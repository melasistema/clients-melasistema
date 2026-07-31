<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * The owner's at-a-glance overview. Every figure rolls up the same accessors
     * the rest of the app trusts (owed / paid / outstanding, in integer cents) so
     * the dashboard never re-derives money a different way than the clients index.
     *
     * One eager-loaded pass over clients -> projects -> {tasks, payments} feeds all
     * the money/time rollups (same 4-query shape as the clients index). Everything
     * is mapped to plain view-models so the models' hidden relations / appended
     * accessors don't run. (The running-timer bar is app chrome now, shared on every
     * request by HandleInertiaRequests, so it isn't a dashboard prop.)
     *
     * Note the deliberate asymmetry: money can be windowed ("received this month")
     * because payments carry a `paid_at`; tracked time cannot — a task only holds an
     * all-time `total_seconds`, there is no dated time-entry log — so hours are
     * reported all-time.
     */
    public function index(): Response
    {
        $user = auth()->user();

        $clients = $user->clients()->with(['projects.tasks', 'projects.payments'])->get();

        $startOfMonth = now()->startOfMonth();

        $outstandingCents = 0;
        $paidCents = 0;
        $receivedThisMonthCents = 0;
        $trackedSeconds = 0;
        $outstandingProjectsCount = 0;
        $awaiting = collect();
        $paymentsFeed = collect();

        foreach ($clients as $client) {
            foreach ($client->projects as $project) {
                $paidCents += $project->amountPaidInCents();
                $trackedSeconds += $project->total_tracked_seconds;

                $projectOutstandingCents = $project->outstandingInCents();

                if ($projectOutstandingCents > 0) {
                    // Floor per project: an overpaid project must not net down what
                    // another client still owes.
                    $outstandingCents += $projectOutstandingCents;
                    $outstandingProjectsCount++;

                    // "Awaiting payment" is the actionable subset: work that is
                    // delivered (completed) but not yet fully paid.
                    if ($project->is_completed) {
                        $awaiting->push([
                            'client_id' => $client->id,
                            'project_id' => $project->id,
                            'project_name' => $project->name,
                            'client_name' => $client->company_name,
                            'outstanding' => $projectOutstandingCents / 100,
                        ]);
                    }
                }

                foreach ($project->payments as $payment) {
                    if ($payment->paid_at->gte($startOfMonth)) {
                        $receivedThisMonthCents += $payment->amountInCents();
                    }

                    $paymentsFeed->push([
                        'id' => $payment->id,
                        'amount' => $payment->amountInCents() / 100,
                        'paid_at' => $payment->paid_at,
                        'project_name' => $project->name,
                        'client_name' => $client->company_name,
                    ]);
                }
            }
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'outstanding' => $outstandingCents / 100,
                'outstanding_projects_count' => $outstandingProjectsCount,
                'received_this_month' => $receivedThisMonthCents / 100,
                'received_all_time' => $paidCents / 100,
                'tracked_seconds' => $trackedSeconds,
            ],
            'awaiting_payment' => $awaiting->sortByDesc('outstanding')->values(),
            'recent_payments' => $paymentsFeed->sortByDesc('paid_at')->take(5)->values(),
        ]);
    }
}
