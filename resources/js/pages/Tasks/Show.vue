<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project, type Task } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{ client: Client; project: Project; task: Task }>();

const { formatCurrency, formatDuration } = useFormatters();
const { __ } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('clients.title'), href: '/clients' },
    { title: __('projects.title'), href: '/clients/' + props.client.id + '/projects' },
    { title: __('tasks.title'), href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks' },
    { title: props.task.title, href: route('clients.projects.tasks.show', [props.client.id, props.project.id, props.task.id]) },
];

const form = useForm({});

// Live-ticking clock for a running timer (mirrors Tasks/Index).
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

const runningElapsed = computed(() => {
    if (!props.task.is_running || !props.task.timer_started_at) {
        return '';
    }

    const startedAt = new Date(props.task.timer_started_at).getTime();

    return formatDuration(Math.max(Math.floor((currentTime.value - startedAt) / 1000), 0));
});

const post = (name: string) => {
    form.post(route(name, [props.client.id, props.project.id, props.task.id]), { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="task.title" />

        <div class="px-4 py-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <Heading :title="task.title" :description="`${project.name} · ${client.company_name}`" />
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('clients.projects.tasks.index', [client.id, project.id])"
                        :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                    >
                        {{ __('tasks.show.back') }}
                    </Link>
                    <Link
                        :href="route('clients.projects.tasks.edit', [client.id, project.id, task.id])"
                        :class="buttonVariants({ variant: 'outline', size: 'sm' })"
                    >
                        {{ __('common.edit') }}
                    </Link>
                </div>
            </div>

            <!-- KPI row: total time, live timer state, task earnings. -->
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border p-4">
                    <div class="text-sm text-muted-foreground">{{ __('tasks.table.time') }}</div>
                    <div class="mt-1 font-mono text-2xl font-semibold tabular-nums">{{ formatDuration(task.total_seconds) }}</div>
                </div>
                <div class="rounded-xl border p-4">
                    <div class="text-sm text-muted-foreground">{{ __('tasks.table.timer') }}</div>
                    <div class="mt-1 text-2xl font-semibold">
                        <span v-if="task.is_completed" class="text-muted-foreground">—</span>
                        <span v-else-if="task.is_running" class="font-mono text-green-600 tabular-nums dark:text-green-500">{{
                            runningElapsed
                        }}</span>
                        <span v-else class="text-muted-foreground">{{ __('tasks.timer.stopped') }}</span>
                    </div>
                </div>
                <div class="rounded-xl border p-4">
                    <div class="text-sm text-muted-foreground">{{ __('tasks.table.earnings') }}</div>
                    <div class="mt-1 text-2xl font-semibold">{{ formatCurrency(task.this_task_total_entry) }}</div>
                </div>
            </div>

            <!-- Timer / completion actions. -->
            <div class="mt-4 flex flex-wrap items-center gap-2">
                <template v-if="!task.is_completed">
                    <Button v-if="!task.is_running" variant="secondary" size="sm" @click="post('clients.projects.tasks.startTimer')">
                        {{ __('tasks.start') }}
                    </Button>
                    <Button v-else variant="default" size="sm" @click="post('clients.projects.tasks.stopTimer')">
                        {{ __('tasks.stop') }}
                    </Button>
                    <Button variant="ghost" size="sm" @click="post('clients.projects.tasks.complete')">{{ __('common.complete') }}</Button>
                </template>
                <Button v-else variant="ghost" size="sm" @click="post('clients.projects.tasks.reopen')">{{ __('common.reopen') }}</Button>
            </div>

            <!-- Full description body. -->
            <div class="mt-6 rounded-xl border p-5">
                <h2 class="text-sm font-medium text-muted-foreground">{{ __('tasks.show.details') }}</h2>
                <p v-if="task.description" class="mt-2 whitespace-pre-wrap text-foreground">{{ task.description }}</p>
                <p v-else class="mt-2 text-sm text-muted-foreground italic">{{ __('tasks.show.no_description') }}</p>
            </div>
        </div>
    </AppLayout>
</template>
