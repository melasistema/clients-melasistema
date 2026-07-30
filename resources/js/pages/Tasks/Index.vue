<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button, buttonVariants } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project, type Task } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{ client: Client; project: Project; tasks: Task[] }>();

const { formatCurrency, formatDuration } = useFormatters();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '/clients' },
    { title: 'Projects', href: '/clients/' + props.client.id + '/projects' },
    { title: 'Tasks', href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks' },
];

const form = useForm({});

// Completed tasks are hidden by default; a toggle brings them back (muted).
const showCompleted = ref(false);
const completedCount = computed(() => props.tasks.filter((task) => task.is_completed).length);
const visibleTasks = computed(() => (showCompleted.value ? props.tasks : props.tasks.filter((task) => !task.is_completed)));

// Gentle nudge: once every task is done and the project isn't yet marked
// complete, suggest completing it — but never force it (no auto-cascade).
const showCompletionNudge = computed(() => props.tasks.length > 0 && props.tasks.every((task) => task.is_completed) && !props.project.is_completed);

const completeProject = () => {
    form.post(route('clients.projects.complete', [props.client.id, props.project.id]), { preserveScroll: true });
};

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

const deleteTask = (taskId: number) => {
    form.delete(route('clients.projects.tasks.destroy', [props.client.id, props.project.id, taskId]), {
        preserveScroll: true,
    });
};

const startTimer = (taskId: number) => {
    form.post(route('clients.projects.tasks.startTimer', [props.client.id, props.project.id, taskId]), {
        preserveScroll: true,
    });
};

const stopTimer = (taskId: number) => {
    form.post(route('clients.projects.tasks.stopTimer', [props.client.id, props.project.id, taskId]), {
        preserveScroll: true,
    });
};

const completeTask = (taskId: number) => {
    form.post(route('clients.projects.tasks.complete', [props.client.id, props.project.id, taskId]), {
        preserveScroll: true,
    });
};

const reopenTask = (taskId: number) => {
    form.post(route('clients.projects.tasks.reopen', [props.client.id, props.project.id, taskId]), {
        preserveScroll: true,
    });
};

// The live elapsed time of a running timer, ticking off `currentTime`.
const formatTime = (timestamp: string) => {
    const startedAt = new Date(timestamp).getTime();
    const diffInSeconds = Math.floor((currentTime.value - startedAt) / 1000);

    return formatDuration(Math.max(diffInSeconds, 0));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Tasks" />

        <div class="px-4 py-6">
            <div class="flex items-start justify-between gap-4">
                <Heading title="Tasks" :description="`Tasks for ${project.name}, time tracked and earnings.`" />
                <Link :href="route('clients.projects.tasks.create', [client.id, project.id])" :class="buttonVariants({ size: 'sm' })">
                    Add task
                </Link>
            </div>

            <label v-if="completedCount > 0" class="mt-4 flex items-center gap-2 text-sm text-muted-foreground">
                <Checkbox id="show-completed" v-model="showCompleted" />
                Show completed ({{ completedCount }})
            </label>

            <div
                v-if="showCompletionNudge"
                class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm dark:border-amber-900 dark:bg-amber-950/50"
            >
                <span class="text-amber-800 dark:text-amber-200">All tasks are complete — mark this project as done?</span>
                <Button size="sm" @click="completeProject">Mark project complete</Button>
            </div>

            <div class="mt-6 rounded-xl border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Description</TableHead>
                            <TableHead>Total time</TableHead>
                            <TableHead>Timer</TableHead>
                            <TableHead>Task earnings</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="task in visibleTasks" :key="task.id" :class="task.is_completed ? 'opacity-60' : ''">
                            <TableCell class="font-medium text-foreground">
                                <div class="flex items-center gap-2">
                                    <span :class="task.is_completed ? 'line-through' : ''">{{ task.description }}</span>
                                    <span
                                        v-if="task.is_completed"
                                        class="inline-flex items-center rounded-md bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-300"
                                    >
                                        Done
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="text-foreground">{{ formatDuration(task.total_seconds) }}</TableCell>
                            <TableCell>
                                <span v-if="task.is_completed" class="text-muted-foreground">—</span>
                                <span v-else-if="task.is_running && task.timer_started_at" class="font-medium text-green-600 dark:text-green-500">
                                    Running ({{ formatTime(task.timer_started_at) }})
                                </span>
                                <span v-else class="text-muted-foreground">Stopped</span>
                            </TableCell>
                            <TableCell class="font-medium text-foreground">{{ formatCurrency(task.this_task_total_entry) }}</TableCell>
                            <TableCell>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('clients.projects.tasks.edit', [client.id, project.id, task.id])"
                                        :class="buttonVariants({ variant: 'outline', size: 'sm' })"
                                    >
                                        Edit
                                    </Link>
                                    <template v-if="!task.is_completed">
                                        <Button v-if="!task.is_running" variant="secondary" size="sm" @click="startTimer(task.id)">Start</Button>
                                        <Button v-else variant="default" size="sm" @click="stopTimer(task.id)">Stop</Button>
                                        <Button variant="ghost" size="sm" @click="completeTask(task.id)">Complete</Button>
                                    </template>
                                    <Button v-else variant="ghost" size="sm" @click="reopenTask(task.id)">Reopen</Button>
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="sm">Delete</Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Delete this task?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This moves “{{ task.description }}” and its tracked time to the Trash. You can restore it from
                                                    there, or delete it permanently later.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction
                                                    class="bg-destructive text-white hover:bg-destructive/90"
                                                    @click="deleteTask(task.id)"
                                                >
                                                    Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="visibleTasks.length === 0">
                            <TableCell colspan="5" class="py-10 text-center text-muted-foreground">
                                <template v-if="tasks.length === 0">
                                    No tasks yet.
                                    <Link
                                        :href="route('clients.projects.tasks.create', [client.id, project.id])"
                                        class="text-foreground underline underline-offset-4"
                                    >
                                        Add the first task </Link
                                    >.
                                </template>
                                <template v-else> All tasks are completed. Tick “Show completed” to see them. </template>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
