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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project, type Task } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

const props = defineProps<{ client: Client; project: Project; tasks: Task[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '/clients' },
    { title: 'Projects', href: '/clients/' + props.client.id + '/projects' },
    { title: 'Tasks', href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks' },
];

const form = useForm({});

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

const formatSeconds = (totalSeconds: number) => {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

const formatTime = (timestamp: string) => {
    const startedAt = new Date(timestamp).getTime();
    const diffInSeconds = Math.floor((currentTime.value - startedAt) / 1000);

    return formatSeconds(Math.max(diffInSeconds, 0));
};

const formatEarnings = (value: number) => new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(value);
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
                        <TableRow v-for="task in tasks" :key="task.id">
                            <TableCell class="font-medium text-foreground">{{ task.description }}</TableCell>
                            <TableCell class="text-foreground">{{ formatSeconds(task.total_seconds) }}</TableCell>
                            <TableCell>
                                <span v-if="task.is_running && task.timer_started_at" class="font-medium text-green-600 dark:text-green-500">
                                    Running ({{ formatTime(task.timer_started_at) }})
                                </span>
                                <span v-else class="text-muted-foreground">Stopped</span>
                            </TableCell>
                            <TableCell class="font-medium text-foreground">{{ formatEarnings(task.this_task_total_entry) }}</TableCell>
                            <TableCell>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('clients.projects.tasks.edit', [client.id, project.id, task.id])"
                                        :class="buttonVariants({ variant: 'outline', size: 'sm' })"
                                    >
                                        Edit
                                    </Link>
                                    <Button v-if="!task.is_running" variant="secondary" size="sm" @click="startTimer(task.id)"> Start </Button>
                                    <Button v-else variant="default" size="sm" @click="stopTimer(task.id)">Stop</Button>
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
                        <TableRow v-if="tasks.length === 0">
                            <TableCell colspan="5" class="py-10 text-center text-muted-foreground">
                                No tasks yet.
                                <Link
                                    :href="route('clients.projects.tasks.create', [client.id, project.id])"
                                    class="text-foreground underline underline-offset-4"
                                >
                                    Add the first task </Link
                                >.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
