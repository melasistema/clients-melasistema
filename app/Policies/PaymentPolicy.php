<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\Project;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Recording a payment is authorized against the parent project, passed
     * explicitly since there is no payment instance yet. Ownership walks the
     * chain payment -> project -> client -> user, like the other policies.
     */
    public function create(User $user, Project $project): bool
    {
        return $project->client->user_id === $user->id;
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $payment->project->client->user_id === $user->id;
    }
}
