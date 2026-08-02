<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { CalendarDays, Clock, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

interface DayRow {
    date: string;
    seconds: number;
    value: number | string;
}

interface ProjectRow {
    project_id: number;
    client_id: number;
    project_name: string;
    client_name: string;
    seconds: number;
    value: number | string;
}

const props = defineProps<{
    period: string;
    periods: string[];
    stats: {
        total_seconds: number;
        total_value: number | string;
        days_worked: number;
    };
    by_day: DayRow[];
    by_project: ProjectRow[];
}>();

const { formatCurrency, formatDuration, formatDay } = useFormatters();
const { __ } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [{ title: __('report.title'), href: '/report' }];

// Widest bar = the busiest day/project in the period, so bars are relative to it.
const maxDaySeconds = computed(() => Math.max(1, ...props.by_day.map((d) => d.seconds)));
const maxProjectSeconds = computed(() => Math.max(1, ...props.by_project.map((p) => p.seconds)));

const barWidth = (seconds: number, max: number): string => `${Math.max(2, Math.round((seconds / max) * 100))}%`;
</script>

<template>
    <Head :title="__('report.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4">
            <!-- Period selector. -->
            <div class="flex flex-wrap items-center gap-2">
                <Link
                    v-for="p in periods"
                    :key="p"
                    :href="route('report', { period: p })"
                    preserve-scroll
                    :class="[
                        'rounded-md px-3 py-1.5 text-sm font-medium transition-colors',
                        p === period
                            ? 'bg-primary text-primary-foreground'
                            : 'bg-muted text-muted-foreground hover:bg-muted/70 hover:text-foreground',
                    ]"
                >
                    {{ __(`report.period.${p}`) }}
                </Link>
            </div>

            <!-- KPI cards. -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">{{ __('report.stats.hours') }}</CardTitle>
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ formatDuration(stats.total_seconds) }}</div>
                        <p class="text-xs text-muted-foreground">{{ __('report.stats.hours_sub', { count: stats.days_worked }) }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">{{ __('report.stats.value') }}</CardTitle>
                        <Wallet class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ formatCurrency(stats.total_value) }}</div>
                        <p class="text-xs text-muted-foreground">{{ __('report.stats.value_sub') }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">{{ __('report.stats.days') }}</CardTitle>
                        <CalendarDays class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold text-foreground">{{ stats.days_worked }}</div>
                        <p class="text-xs text-muted-foreground">{{ __('report.stats.days_sub') }}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Two panels: the day-by-day breakdown, and the per-project split. -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ __('report.by_day.title') }}</CardTitle>
                        <p class="text-sm text-muted-foreground">{{ __('report.by_day.subtitle') }}</p>
                    </CardHeader>
                    <CardContent>
                        <ul v-if="by_day.length > 0" class="flex flex-col gap-3">
                            <li v-for="day in by_day" :key="day.date" class="flex flex-col gap-1.5">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <span class="font-medium text-foreground">{{ formatDay(day.date) }}</span>
                                    <span class="flex shrink-0 items-center gap-3">
                                        <span class="text-muted-foreground">{{ formatDuration(day.seconds) }}</span>
                                        <span class="font-medium whitespace-nowrap text-foreground">{{ formatCurrency(day.value) }}</span>
                                    </span>
                                </div>
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                    <div class="h-full rounded-full bg-primary" :style="{ width: barWidth(day.seconds, maxDaySeconds) }" />
                                </div>
                            </li>
                        </ul>
                        <p v-else class="py-6 text-center text-sm text-muted-foreground">{{ __('report.by_day.empty') }}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ __('report.by_project.title') }}</CardTitle>
                        <p class="text-sm text-muted-foreground">{{ __('report.by_project.subtitle') }}</p>
                    </CardHeader>
                    <CardContent>
                        <ul v-if="by_project.length > 0" class="flex flex-col gap-3">
                            <li v-for="item in by_project" :key="item.project_id" class="flex flex-col gap-1.5">
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <Link
                                        :href="route('clients.projects.tasks.index', [item.client_id, item.project_id])"
                                        class="min-w-0 truncate hover:underline"
                                    >
                                        <span class="font-medium text-foreground">{{ item.project_name }}</span>
                                        <span class="text-muted-foreground"> — {{ item.client_name }}</span>
                                    </Link>
                                    <span class="flex shrink-0 items-center gap-3">
                                        <span class="text-muted-foreground">{{ formatDuration(item.seconds) }}</span>
                                        <span class="font-medium whitespace-nowrap text-foreground">{{ formatCurrency(item.value) }}</span>
                                    </span>
                                </div>
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                    <div class="h-full rounded-full bg-primary" :style="{ width: barWidth(item.seconds, maxProjectSeconds) }" />
                                </div>
                            </li>
                        </ul>
                        <p v-else class="py-6 text-center text-sm text-muted-foreground">{{ __('report.by_project.empty') }}</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
