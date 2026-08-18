<script setup lang="ts">
import AttachmentGallery from '@/components/AttachmentGallery.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import TimeInput from '@/components/TimeInput.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project, type Task } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, defineAsyncComponent, onMounted, onUnmounted, ref } from 'vue';

// The Markdown editor pulls in ProseMirror (~100KB+); lazy-load it so that weight
// only ships on this page, not app-wide.
const MarkdownEditor = defineAsyncComponent(() => import('@/components/MarkdownEditor.vue'));

const props = defineProps<{ client: Client; project: Project; task: Task }>();

const { formatCurrency, formatDuration } = useFormatters();
const { __ } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('clients.title'), href: '/clients' },
    { title: __('projects.title'), href: '/clients/' + props.client.id + '/projects' },
    { title: __('tasks.title'), href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks' },
    { title: props.task.title, href: route('clients.projects.tasks.show', [props.client.id, props.project.id, props.task.id]) },
];

// This page is both the task's detail view and its editor — the title,
// description and tracked time are edited inline (there is no separate Edit page).
const form = useForm({
    title: props.task.title,
    description: props.task.description ?? '',
    total_seconds: props.task.total_seconds,
});

const save = () => {
    form.put(route('clients.projects.tasks.update', [props.client.id, props.project.id, props.task.id]), {
        preserveScroll: true,
    });
};

// Timer / completion actions post on their own, independent of the edit form.
const act = (name: string) => {
    router.post(route(name, [props.client.id, props.project.id, props.task.id]), {}, { preserveScroll: true });
};

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
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="task.title" />

        <div class="px-4 py-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <Heading :title="task.title" :description="`${project.name} · ${client.company_name}`" />
                <Link
                    :href="route('clients.projects.tasks.index', [client.id, project.id])"
                    :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                >
                    {{ __('tasks.show.back') }}
                </Link>
            </div>

            <!-- Editable task fields: this page is the task's editor too. -->
            <form class="mt-6 rounded-xl border p-5" @submit.prevent="save">
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="grid grow gap-2">
                        <Label for="title">{{ __('tasks.form.name') }}</Label>
                        <Input id="title" v-model="form.title" type="text" :placeholder="__('tasks.form.name_placeholder')" />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div class="grid gap-2 sm:w-56">
                        <Label>{{ __('tasks.form.time') }}</Label>
                        <TimeInput v-model="form.total_seconds" />
                        <InputError :message="form.errors.total_seconds" />
                    </div>
                </div>

                <div class="mt-4 grid gap-2">
                    <Label>{{ __('tasks.form.description') }}</Label>
                    <MarkdownEditor v-model="form.description" :placeholder="__('tasks.form.description_placeholder')" />
                    <p class="text-xs text-muted-foreground">{{ __('tasks.form.description_hint') }}</p>
                    <InputError :message="form.errors.description" />
                </div>

                <div class="mt-4 flex justify-end">
                    <Button type="submit" :disabled="form.processing">{{ __('common.save') }}</Button>
                </div>
            </form>

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
                    <Button v-if="!task.is_running" variant="secondary" size="sm" @click="act('clients.projects.tasks.startTimer')">
                        {{ __('tasks.start') }}
                    </Button>
                    <Button v-else variant="default" size="sm" @click="act('clients.projects.tasks.stopTimer')">
                        {{ __('tasks.stop') }}
                    </Button>
                    <Button variant="ghost" size="sm" @click="act('clients.projects.tasks.complete')">{{ __('common.complete') }}</Button>
                </template>
                <Button v-else variant="ghost" size="sm" @click="act('clients.projects.tasks.reopen')">{{ __('common.reopen') }}</Button>
            </div>

            <!-- Files & links attached to this task. -->
            <div class="mt-6">
                <AttachmentGallery
                    :attachments="task.attachments ?? []"
                    :store-url="route('clients.projects.tasks.attachments.store', [client.id, project.id, task.id])"
                />
            </div>
        </div>
    </AppLayout>
</template>
