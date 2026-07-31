<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import type { AppPageProps } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { Pause, Play, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// The persistent timer bar, shown in the app header on every page. It has two
// states, both driven by props shared on every request (HandleInertiaRequests):
//   • running  — the `activeTimer`: a live-ticking clock + Stop.
//   • stopped  — the `lastTimer` (last stopped task, from a cookie): a static
//                total + Resume + a close button, so you never lose track of what
//                you were working on until you dismiss it.
// The task label links to its project's task list in both states. Renders nothing
// when neither is present. This global shape is what a NativePHP menu-bar timer
// will reuse.
const page = usePage<AppPageProps>();
const activeTimer = computed(() => page.props.activeTimer);
const lastTimer = computed(() => page.props.lastTimer);

// The stopped bar is a fallback: only when nothing is actively running.
const showStopped = computed(() => !activeTimer.value && lastTimer.value !== null);

const { formatDuration } = useFormatters();
const { __ } = useTranslations();

const form = useForm({});

// The project's task list — the closest thing to a task detail page (there are no
// task show pages), so clicking the label lands you where the task lives.
const taskHref = (timer: { client_id: number; project_id: number }) => route('clients.projects.tasks.index', [timer.client_id, timer.project_id]);

const stopTimer = () => {
    const timer = activeTimer.value;

    if (!timer) {
        return;
    }

    form.post(route('clients.projects.tasks.stopTimer', [timer.client_id, timer.project_id, timer.task_id]), {
        preserveScroll: true,
    });
};

const resumeTimer = () => {
    const timer = lastTimer.value;

    if (!timer) {
        return;
    }

    form.post(route('clients.projects.tasks.startTimer', [timer.client_id, timer.project_id, timer.task_id]), {
        preserveScroll: true,
    });
};

const dismiss = () => {
    form.post(route('timer.dismiss'), { preserveScroll: true });
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

const elapsed = computed(() => {
    if (!activeTimer.value) {
        return '';
    }

    const startedAt = new Date(activeTimer.value.timer_started_at).getTime();

    return formatDuration(Math.max(Math.floor((currentTime.value - startedAt) / 1000), 0));
});
</script>

<template>
    <!-- Running: live clock, clickable label, Stop. -->
    <div
        v-if="activeTimer"
        class="flex items-center gap-2 rounded-full border border-green-200 bg-green-50 py-1 pr-1 pl-2.5 dark:border-green-900 dark:bg-green-950/50"
    >
        <span class="relative flex h-2 w-2 shrink-0">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-500 opacity-75"></span>
            <span class="relative inline-flex h-2 w-2 rounded-full bg-green-600"></span>
        </span>

        <span class="font-mono text-sm font-semibold text-green-700 tabular-nums dark:text-green-400">{{ elapsed }}</span>

        <Link :href="taskHref(activeTimer)" class="hidden max-w-[16rem] min-w-0 truncate text-xs text-muted-foreground hover:underline sm:inline">
            <span class="text-foreground">{{ activeTimer.task_description }}</span>
            · {{ activeTimer.project_name }}
        </Link>

        <Button
            size="sm"
            variant="ghost"
            class="h-7 px-2 text-xs text-green-800 hover:bg-green-100 dark:text-green-300 dark:hover:bg-green-900"
            :disabled="form.processing"
            :title="__('common.timer.stop')"
            :aria-label="__('common.timer.stop')"
            @click="stopTimer"
        >
            {{ __('common.timer.stop') }}
        </Button>
    </div>

    <!-- Stopped: last worked-on task, resumable, dismissible. -->
    <div
        v-else-if="showStopped && lastTimer"
        class="flex items-center gap-2 rounded-full border border-border bg-muted/50 py-1 pr-1 pl-2.5 text-muted-foreground"
    >
        <Pause class="h-3 w-3 shrink-0" />

        <span class="font-mono text-sm font-semibold tabular-nums">{{ formatDuration(lastTimer.total_seconds) }}</span>

        <Link :href="taskHref(lastTimer)" class="hidden max-w-[16rem] min-w-0 truncate text-xs hover:underline sm:inline">
            <span class="text-muted-foreground">{{ __('common.timer.last') }}:</span>
            <span class="text-foreground">{{ lastTimer.task_description }}</span>
            · {{ lastTimer.project_name }}
        </Link>

        <Button
            size="sm"
            variant="ghost"
            class="h-7 gap-1 px-2 text-xs"
            :disabled="form.processing"
            :title="__('common.timer.resume')"
            :aria-label="__('common.timer.resume')"
            @click="resumeTimer"
        >
            <Play class="h-3 w-3" />
            {{ __('common.timer.resume') }}
        </Button>

        <Button
            size="icon"
            variant="ghost"
            class="h-7 w-7 shrink-0"
            :disabled="form.processing"
            :title="__('common.timer.dismiss')"
            :aria-label="__('common.timer.dismiss')"
            @click="dismiss"
        >
            <X class="h-3.5 w-3.5" />
        </Button>
    </div>
</template>
