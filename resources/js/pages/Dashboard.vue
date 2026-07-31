<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Clock, TrendingUp, Wallet } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

interface ActiveTimer {
    client_id: number;
    project_id: number;
    task_id: number;
    task_description: string;
    project_name: string;
    client_name: string;
    timer_started_at: string;
}

interface AwaitingPayment {
    client_id: number;
    project_id: number;
    project_name: string;
    client_name: string;
    outstanding: number | string;
}

interface RecentPayment {
    id: number;
    amount: number | string;
    paid_at: string;
    project_name: string;
    client_name: string;
}

const props = defineProps<{
    stats: {
        outstanding: number | string;
        outstanding_projects_count: number;
        received_this_month: number | string;
        received_all_time: number | string;
        tracked_seconds: number;
    };
    active_timer: ActiveTimer | null;
    awaiting_payment: AwaitingPayment[];
    recent_payments: RecentPayment[];
}>();

const { formatCurrency, formatDuration, formatDay } = useFormatters();
const { __ } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [{ title: __('common.nav.dashboard'), href: '/dashboard' }];

const form = useForm({});

const stopTimer = () => {
    if (!props.active_timer) {
        return;
    }

    form.post(route('clients.projects.tasks.stopTimer', [props.active_timer.client_id, props.active_timer.project_id, props.active_timer.task_id]), {
        preserveScroll: true,
    });
};

// Live-ticking elapsed time for the running timer, driven off a 1s clock.
const currentTime = ref(Date.now());
let timerInterval: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    timerInterval = setInterval(() => {
        currentTime.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});

const elapsed = (timestamp: string) => {
    const startedAt = new Date(timestamp).getTime();

    return formatDuration(Math.max(Math.floor((currentTime.value - startedAt) / 1000), 0));
};
</script>

<template>
    <Head :title="__('dashboard.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <!-- Active timer bar: only present while a task is running. -->
            <div
                v-if="active_timer"
                class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 dark:border-green-900 dark:bg-green-950/50"
            >
                <div class="flex items-center gap-3">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-500 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-green-600"></span>
                    </span>
                    <div class="text-sm">
                        <span class="text-xs tracking-wide text-green-700 uppercase dark:text-green-400">{{
                            __('dashboard.active_timer.label')
                        }}</span>
                        <div class="font-medium text-foreground">
                            {{ active_timer.task_description }}
                            <span class="text-muted-foreground">· {{ active_timer.project_name }} — {{ active_timer.client_name }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-mono text-lg font-semibold text-green-700 tabular-nums dark:text-green-400">{{
                        elapsed(active_timer.timer_started_at)
                    }}</span>
                    <Button size="sm" :disabled="form.processing" @click="stopTimer">{{ __('dashboard.active_timer.stop') }}</Button>
                </div>
            </div>

            <!-- KPI cards. -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">{{ __('dashboard.stats.outstanding') }}</CardTitle>
                        <Wallet class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ formatCurrency(stats.outstanding) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ __('dashboard.stats.outstanding_sub', { count: stats.outstanding_projects_count }) }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">{{ __('dashboard.stats.received') }}</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ formatCurrency(stats.received_this_month) }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ __('dashboard.stats.received_sub', { amount: formatCurrency(stats.received_all_time) }) }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">{{ __('dashboard.stats.hours') }}</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ formatDuration(stats.tracked_seconds) }}</div>
                        <p class="text-xs text-muted-foreground">{{ __('dashboard.stats.hours_sub') }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Two panels: what to chase, and what came in. -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ __('dashboard.awaiting.title') }}</CardTitle>
                        <p class="text-sm text-muted-foreground">{{ __('dashboard.awaiting.subtitle') }}</p>
                    </CardHeader>
                    <CardContent>
                        <ul v-if="awaiting_payment.length > 0" class="divide-y divide-border">
                            <li v-for="item in awaiting_payment" :key="item.project_id" class="flex items-center justify-between gap-3 py-2.5">
                                <Link
                                    :href="route('clients.projects.tasks.index', [item.client_id, item.project_id])"
                                    class="min-w-0 truncate text-sm hover:underline"
                                >
                                    <span class="font-medium text-foreground">{{ item.project_name }}</span>
                                    <span class="text-muted-foreground"> — {{ item.client_name }}</span>
                                </Link>
                                <span class="font-medium whitespace-nowrap text-foreground">{{ formatCurrency(item.outstanding) }}</span>
                            </li>
                        </ul>
                        <p v-else class="py-6 text-center text-sm text-muted-foreground">{{ __('dashboard.awaiting.empty') }}</p>
                        <Link href="/clients" class="mt-3 inline-block text-sm text-foreground underline underline-offset-4">
                            {{ __('dashboard.awaiting.view_all') }}
                        </Link>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ __('dashboard.recent_payments.title') }}</CardTitle>
                        <p class="text-sm text-muted-foreground">{{ __('dashboard.recent_payments.subtitle') }}</p>
                    </CardHeader>
                    <CardContent>
                        <ul v-if="recent_payments.length > 0" class="divide-y divide-border">
                            <li v-for="payment in recent_payments" :key="payment.id" class="flex items-center justify-between gap-3 py-2.5">
                                <div class="min-w-0 truncate text-sm">
                                    <span class="font-medium text-foreground">{{ payment.project_name }}</span>
                                    <span class="text-muted-foreground"> — {{ payment.client_name }}</span>
                                </div>
                                <div class="flex shrink-0 items-center gap-3 text-sm">
                                    <span class="text-muted-foreground">{{ formatDay(payment.paid_at) }}</span>
                                    <span class="font-medium whitespace-nowrap text-foreground">{{ formatCurrency(payment.amount) }}</span>
                                </div>
                            </li>
                        </ul>
                        <p v-else class="py-6 text-center text-sm text-muted-foreground">{{ __('dashboard.recent_payments.empty') }}</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
