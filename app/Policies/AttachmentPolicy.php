<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AttachmentPolicy
{
    /**
     * Viewing (streaming) an attachment is an O(1) check on the denormalized
     * owner — deliberately not a walk up the morph chain, since the streaming
     * route is hit once per image in a gallery.
     */
    public function view(User $user, Attachment $attachment): bool
    {
        return $attachment->user_id === $user->id;
    }

    /**
     * Creating is authorized against the parent (a Task or Project), passed
     * explicitly since there is no attachment instance yet — mirrors the way
     * TaskPolicy@create and PaymentPolicy@create take their parent.
     */
    public function create(User $user, Model $parent): bool
    {
        return $this->ownerId($parent) === $user->id;
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $attachment->user_id === $user->id;
    }

    /**
     * Resolve the owning user id from an attachable parent by walking its chain
     * up to the client. Returns null for an unsupported parent type (deny).
     */
    private function ownerId(Model $parent): ?int
    {
        return match (true) {
            $parent instanceof Task => $parent->project->client->user_id,
            $parent instanceof Project => $parent->client->user_id,
            default => null,
        };
    }
}
