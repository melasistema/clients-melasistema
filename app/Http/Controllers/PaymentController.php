<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    /**
     * Record a payment against a project. Authorization runs in StorePaymentRequest
     * (PaymentPolicy@create against the route's project), so a cross-user write is
     * a 403 before validation.
     */
    public function store(StorePaymentRequest $request, Client $client, Project $project): RedirectResponse
    {
        $project->payments()->create($request->validated());

        return redirect()->back();
    }

    /**
     * Delete a ledger entry. Scoped bindings guarantee the payment belongs to the
     * project belongs to the client; the policy confirms the owner.
     */
    public function destroy(Client $client, Project $project, Payment $payment): RedirectResponse
    {
        $this->authorize('delete', $payment);

        $payment->delete();

        return redirect()->back();
    }
}
