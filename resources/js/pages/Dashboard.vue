<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Clock, TrendingUp, Wallet } from 'lucide-vue-next';

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

defineProps<{
    stats: {
        outstanding: number | string;
        outstanding_projects_count: number;
        received_this_month: number | string;
        received_all_time: number | string;
        tracked_seconds: number;
    };
    awaiting_payment: AwaitingPayment[];
    recent_payments: RecentPayment[];
}>();

const { formatCurrency, formatDuration, formatDay } = useFormatters();
const { __ } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [{ title: __('common.nav.dashboard'), href: '/dashboard' }];
</script>

<template>
    <Head :title="__('dashboard.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
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
