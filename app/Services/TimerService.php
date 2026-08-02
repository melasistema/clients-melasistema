<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;

/**
 * The single home for task-timer domain logic — starting, switching, and banking
 * sessions — extracted from TaskController so the controller stays thin (HTTP +
 * cookies) and the single-running-timer invariant lives in one testable place.
 *
 * Invariant: a freelancer works one task at a time, so at most one of the owner's
 * tasks is ever `is_running`. Every path that ends a session (stop, switch,
 * complete) routes through {@see bank()} so exactly one dated `time_entries` row
 * is written per session and `tasks.total_seconds` always equals the sum of
 * entries accrued since the ledger shipped.
 */
class TimerService
{
    /**
     * Start (or switch to) a task's timer under the single-running-timer invariant:
     * inside one transaction, bank any *other* running task of the owner (so two
     * timers can never accrue the same wall-clock minute), then mark this one
     * running. The owner id is passed in rather than walked off the task so the
     * "stop the others" query never triggers a lazy load.
     */
    public function start(Task $task, int $ownerId): void
    {
        DB::transaction(function () use ($task, $ownerId) {
            $this->stopRunningTimers($ownerId, exceptTaskId: $task->id);

            $task->update([
                'is_running' => true,
                'timer_started_at' => now(),
            ]);
        });
    }

    /**
     * Stop a task's timer, banking the elapsed seconds. Returns the new
     * `total_seconds` (the caller writes it into the "last stopped" cookie).
     */
    public function stop(Task $task): int
    {
        return $this->bank($task);
    }

    /**
     * Stop every running timer owned by the given user (optionally excluding one
     * task), banking each one's elapsed seconds. Backs the single-timer invariant.
     * Eager-loads `project` so bank()'s rate snapshot doesn't N+1 across the loop.
     */
    public function stopRunningTimers(int $userId, ?int $exceptTaskId = null): void
    {
        $running = Task::with('project')
            ->whereHas('project.client', fn ($query) => $query->where('user_id', $userId))
            ->where('is_running', true)
            ->when($exceptTaskId, fn ($query) => $query->where('id', '!=', $exceptTaskId))
            ->get();

        foreach ($running as $task) {
            $this->bank($task);
        }
    }

    /**
     * Bank a running task's elapsed time: clear the running flag, add the seconds to
     * the cached `total_seconds`, and record the session as one dated `time_entries`
     * row with the project's rate snapshotted. The single place a session is ever
     * banked (stop / switch / complete all route through here), so exactly one entry
     * is written per session and `total_seconds` always equals the sum of entries
     * accrued since the feature shipped. Returns the new `total_seconds`.
     *
     * A zero-second session (start then immediate stop) is banked but writes no
     * entry — it is noise, and adding zero keeps the running total correct.
     */
    public function bank(Task $task): int
    {
        // Carbon 3's diffInSeconds returns a float (fractional seconds); cast so
        // total_seconds stays a whole-second integer (the DB column is, and the
        // last_timer cookie is read back raw).
        $startedAt = $task->timer_started_at;
        $endedAt = now();
        $secondsToAdd = (int) $startedAt->diffInSeconds($endedAt);
        $newTotalSeconds = $task->total_seconds + $secondsToAdd;

        $task->update([
            'is_running' => false,
            'timer_started_at' => null,
            'total_seconds' => $newTotalSeconds,
        ]);

        if ($secondsToAdd > 0) {
            $task->timeEntries()->create([
                'started_at' => $startedAt,
                'ended_at' => $endedAt,
                'seconds' => $secondsToAdd,
                'hourly_rate' => $task->project->hourly_rate,
            ]);
        }

        return $newTotalSeconds;
    }
}
