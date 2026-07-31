<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ client: Client; project: Project }>();

const { formatCurrency } = useFormatters();
const { __ } = useTranslations();

const form = useForm({
    name: props.project.name,
    description: props.project.description,
    hourly_rate: props.project.hourly_rate,
    agreed_fee: (props.project.agreed_fee ?? '') as number | string,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        agreed_fee: data.agreed_fee === '' || data.agreed_fee === null ? null : data.agreed_fee,
    })).put(route('clients.projects.update', [props.client.id, props.project.id]));
};

// The payment ledger only makes sense when something is owed.
const showPayments = computed(() => props.project.billing_mode !== 'non_billable');
const feeAmount = computed(() => Number(props.project.agreed_fee ?? 0));

const paymentForm = useForm({
    amount: '' as number | string,
    paid_at: new Date().toISOString().slice(0, 10),
    note: '' as string | null,
});

const addPayment = () => {
    paymentForm.post(route('clients.projects.payments.store', [props.client.id, props.project.id]), {
        preserveScroll: true,
        onSuccess: () => paymentForm.reset('amount', 'note'),
    });
};

const deleteForm = useForm({});

const removePayment = (paymentId: number) => {
    deleteForm.delete(route('clients.projects.payments.destroy', [props.client.id, props.project.id, paymentId]), {
        preserveScroll: true,
    });
};

// Quick-fill helpers for fixed-price projects: a fraction of the fee, or whatever
// is still outstanding.
const fillPercent = (fraction: number, note: string) => {
    paymentForm.amount = (feeAmount.value * fraction).toFixed(2);
    paymentForm.note = note;
};

const fillRemaining = () => {
    paymentForm.amount = Math.max(props.project.outstanding, 0).toFixed(2);
    paymentForm.note = __('projects.payments.final_note');
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('clients.title'), href: '/clients' },
    { title: __('projects.title'), href: '/clients/' + props.client.id + '/projects' },
    { title: __('common.edit'), href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/edit' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('projects.form.edit_title')" />

        <div class="px-4 py-6">
            <Heading :title="__('projects.form.edit_title')" :description="__('projects.form.edit_description', { name: project.name })" />

            <form class="mt-6 max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">{{ __('projects.form.name') }}</Label>
                    <Input id="name" v-model="form.name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">{{ __('common.description') }}</Label>
                    <Textarea id="description" v-model="form.description" rows="3" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="hourly_rate">{{ __('projects.form.hourly_rate') }}</Label>
                    <Input id="hourly_rate" v-model="form.hourly_rate" type="number" step="0.01" min="0" />
                    <InputError :message="form.errors.hourly_rate" />
                    <p class="text-xs text-muted-foreground">{{ __('projects.form.hourly_rate_help') }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="agreed_fee">{{ __('projects.form.agreed_fee') }}</Label>
                    <Input
                        id="agreed_fee"
                        v-model="form.agreed_fee"
                        type="number"
                        step="0.01"
                        min="0"
                        :placeholder="__('projects.form.agreed_fee_placeholder')"
                    />
                    <InputError :message="form.errors.agreed_fee" />
                    <p class="text-xs text-muted-foreground">
                        {{ __('projects.form.agreed_fee_help') }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('clients.projects.index', client.id)" :class="buttonVariants({ variant: 'ghost' })">{{
                        __('common.cancel')
                    }}</Link>
                    <Button type="submit" :disabled="form.processing">{{ __('common.save') }}</Button>
                </div>
            </form>

            <!-- Payment ledger: deposit, milestones, final balance. Hidden for
                 non-billable projects (nothing is owed). -->
            <section v-if="showPayments" class="mt-12 max-w-xl">
                <h2 class="text-lg font-medium text-foreground">{{ __('projects.payments.title') }}</h2>
                <p class="text-sm text-muted-foreground">{{ __('projects.payments.subtitle') }}</p>

                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div class="rounded-xl border p-4">
                        <div class="text-xs text-muted-foreground">{{ __('projects.payments.owed') }}</div>
                        <div class="mt-1 font-medium text-foreground">{{ formatCurrency(project.total_earnings) }}</div>
                    </div>
                    <div class="rounded-xl border p-4">
                        <div class="text-xs text-muted-foreground">{{ __('projects.payments.paid') }}</div>
                        <div class="mt-1 font-medium text-foreground">{{ formatCurrency(project.amount_paid) }}</div>
                    </div>
                    <div class="rounded-xl border p-4">
                        <div class="text-xs text-muted-foreground">{{ __('projects.payments.outstanding') }}</div>
                        <div
                            class="mt-1 font-medium"
                            :class="project.outstanding > 0 ? 'text-amber-600 dark:text-amber-500' : 'text-green-600 dark:text-green-500'"
                        >
                            {{ formatCurrency(project.outstanding) }}
                        </div>
                    </div>
                </div>

                <ul class="mt-4 divide-y rounded-xl border">
                    <li v-for="payment in project.payments ?? []" :key="payment.id" class="flex items-center justify-between gap-4 p-3">
                        <div>
                            <div class="font-medium text-foreground">{{ formatCurrency(payment.amount) }}</div>
                            <div class="text-sm text-muted-foreground">
                                {{ payment.paid_at }}<span v-if="payment.note"> · {{ payment.note }}</span>
                            </div>
                        </div>
                        <Button variant="ghost" size="sm" @click="removePayment(payment.id)">{{ __('projects.payments.remove') }}</Button>
                    </li>
                    <li v-if="(project.payments ?? []).length === 0" class="p-4 text-center text-sm text-muted-foreground">
                        {{ __('projects.payments.empty') }}
                    </li>
                </ul>

                <form class="mt-4 space-y-4 rounded-xl border p-4" @submit.prevent="addPayment">
                    <div v-if="project.billing_mode === 'fixed'" class="flex flex-wrap gap-2">
                        <Button type="button" variant="outline" size="sm" @click="fillPercent(0.3, __('projects.payments.deposit_30_note'))">
                            {{ __('projects.payments.deposit_30') }}
                        </Button>
                        <Button type="button" variant="outline" size="sm" @click="fillPercent(0.5, __('projects.payments.deposit_50_note'))">
                            {{ __('projects.payments.deposit_50') }}
                        </Button>
                        <Button type="button" variant="outline" size="sm" @click="fillRemaining">{{ __('projects.payments.remaining') }}</Button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="payment_amount">{{ __('projects.payments.amount') }}</Label>
                            <Input id="payment_amount" v-model="paymentForm.amount" type="number" step="0.01" min="0.01" />
                            <InputError :message="paymentForm.errors.amount" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="payment_date">{{ __('projects.payments.date') }}</Label>
                            <Input id="payment_date" v-model="paymentForm.paid_at" type="date" />
                            <InputError :message="paymentForm.errors.paid_at" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="payment_note">{{ __('projects.payments.note') }}</Label>
                            <Input id="payment_note" v-model="paymentForm.note" type="text" :placeholder="__('projects.payments.note_placeholder')" />
                            <InputError :message="paymentForm.errors.note" />
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <Button type="submit" size="sm" :disabled="paymentForm.processing">{{ __('projects.payments.add') }}</Button>
                    </div>
                </form>
            </section>
        </div>
    </AppLayout>
</template>
