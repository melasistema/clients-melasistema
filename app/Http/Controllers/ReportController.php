<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * The time report: how the owner's tracked hours — and their billable value —
     * break down by day and by project over a period. It reads the dated
     * `time_entries` ledger, the only place work is stored with a real date, so
     * unlike the dashboard's all-time hours this can be windowed to a month/year.
     *
     * Each session is valued at the rate snapshotted when it was banked
     * (TimeEntry::valueInCents), so a later rate change never re-prices past work.
     * Values roll up in integer cents (each entry rounded once, then summed) and
     * euros are divided out only at the boundary — the same cents discipline the
     * rest of the app uses. Rows are mapped to plain view-models so the models'
     * hidden relations / appended accessors don't run (like the dashboard/trash).
     */
    public function index(Request $request): Response
    {
        $userId = auth()->id();

        [$period, $from, $to] = $this->resolvePeriod($request->query('period'));

        $entries = TimeEntry::query()
            ->whereHas('task.project.client', fn ($query) => $query->where('user_id', $userId))
            ->when($from, fn ($query) => $query->where('started_at', '>=', $from))
            ->when($to, fn ($query) => $query->where('started_at', '<=', $to))
            ->with('task.project.client')
            ->orderByDesc('started_at')
            ->get();

        $totalSeconds = 0;
        $totalValueCents = 0;
        $byDay = [];
        $byProject = [];

        foreach ($entries as $entry) {
            $valueCents = $entry->valueInCents();
            $totalSeconds += $entry->seconds;
            $totalValueCents += $valueCents;

            // Group by calendar day (entries come newest-first, so the day keys
            // land in newest-first order too — the panel renders them as-is).
            $day = $entry->started_at->toDateString();
            if (! isset($byDay[$day])) {
                $byDay[$day] = ['date' => $day, 'seconds' => 0, 'value' => 0];
            }
            $byDay[$day]['seconds'] += $entry->seconds;
            $byDay[$day]['value'] += $valueCents;

            // Group by project (its parent may be soft-deleted; whereHas already
            // scoped those out, so anything here has a live task/project/client).
            $project = $entry->task->project;
            if (! isset($byProject[$project->id])) {
                $byProject[$project->id] = [
                    'project_id' => $project->id,
                    'client_id' => $project->client->id,
                    'project_name' => $project->name,
                    'client_name' => $project->client->company_name,
                    'seconds' => 0,
                    'value' => 0,
                ];
            }
            $byProject[$project->id]['seconds'] += $entry->seconds;
            $byProject[$project->id]['value'] += $valueCents;
        }

        // Divide cents out to euros at the boundary only, then order projects by
        // hours (the day panel keeps its natural newest-first order).
        $byDay = array_map(fn ($day) => [...$day, 'value' => $day['value'] / 100], array_values($byDay));
        $byProject = array_map(fn ($project) => [...$project, 'value' => $project['value'] / 100], array_values($byProject));
        usort($byProject, fn ($a, $b) => $b['seconds'] <=> $a['seconds']);

        return Inertia::render('Report/Index', [
            'period' => $period,
            'periods' => ['this_month', 'last_month', 'this_year', 'all_time'],
            'stats' => [
                'total_seconds' => $totalSeconds,
                'total_value' => $totalValueCents / 100,
                'days_worked' => count($byDay),
            ],
            'by_day' => $byDay,
            'by_project' => $byProject,
        ]);
    }

    /**
     * Resolve a period key to a [key, from, to] window. Unknown keys fall back to
     * the current month; `all_time` returns null bounds (no date filter).
     *
     * @return array{0: string, 1: Carbon|null, 2: Carbon|null}
     */
    private function resolvePeriod(?string $period): array
    {
        return match ($period) {
            'last_month' => ['last_month', now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => ['this_year', now()->startOfYear(), now()->endOfYear()],
            'all_time' => ['all_time', null, null],
            default => ['this_month', now()->startOfMonth(), now()->endOfMonth()],
        };
    }
}
