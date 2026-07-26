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
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{ client: Client; projects: Project[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '/clients' },
    { title: 'Projects', href: '/clients/' + props.client.id + '/projects' },
];

const form = useForm({});

// Completed projects are hidden by default; a toggle brings them back (muted).
const showCompleted = ref(false);
const completedCount = computed(() => props.projects.filter((project) => project.is_completed).length);
const visibleProjects = computed(() => (showCompleted.value ? props.projects : props.projects.filter((project) => !project.is_completed)));

const deleteProject = (projectId: number) => {
    form.delete(route('clients.projects.destroy', [props.client.id, projectId]), { preserveScroll: true });
};

const completeProject = (projectId: number) => {
    form.post(route('clients.projects.complete', [props.client.id, projectId]), { preserveScroll: true });
};

const reopenProject = (projectId: number) => {
    form.post(route('clients.projects.reopen', [props.client.id, projectId]), { preserveScroll: true });
};

const modeBadge = (project: Project) => {
    switch (project.billing_mode) {
        case 'fixed':
            return { label: 'Fixed', class: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' };
        case 'non_billable':
            return { label: 'Non-billable', class: 'bg-muted text-muted-foreground' };
        default:
            return { label: 'Hourly', class: 'bg-muted text-muted-foreground' };
    }
};

// Nudge predicate: all of a project's tasks are done but the project isn't marked
// complete yet. Tasks ride along in the index payload (eager-loaded).
const readyToComplete = (project: Project) =>
    !project.is_completed && (project.tasks?.length ?? 0) > 0 && (project.tasks ?? []).every((task) => task.is_completed);

const rateLabel = (project: Project) => {
    if (project.billing_mode === 'fixed') {
        return `${formatEarnings(project.agreed_fee ?? 0)} fee`;
    }
    if (project.billing_mode === 'non_billable') {
        return '—';
    }
    return `${formatEarnings(project.hourly_rate)}/h`;
};

const formatSeconds = (totalSeconds: number) => {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

const formatEarnings = (value: number | string) => new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(Number(value));
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Projects" />

        <div class="px-4 py-6">
            <div class="flex items-start justify-between gap-4">
                <Heading title="Projects" :description="`Projects for ${client.company_name}, their rates and earnings.`" />
                <Link :href="route('clients.projects.create', client.id)" :class="buttonVariants({ size: 'sm' })">Add project</Link>
            </div>

            <label v-if="completedCount > 0" class="mt-4 flex items-center gap-2 text-sm text-muted-foreground">
                <Checkbox id="show-completed" v-model="showCompleted" />
                Show completed ({{ completedCount }})
            </label>

            <div class="mt-6 rounded-xl border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Project</TableHead>
                            <TableHead>Rate / fee</TableHead>
                            <TableHead>Total time</TableHead>
                            <TableHead>Earnings</TableHead>
                            <TableHead>Status</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="project in visibleProjects" :key="project.id" :class="project.is_completed ? 'opacity-60' : ''">
                            <TableCell>
                                <div class="font-medium text-foreground">{{ project.name }}</div>
                                <div class="text-muted-foreground">{{ project.description }}</div>
                            </TableCell>
                            <TableCell class="text-foreground">{{ rateLabel(project) }}</TableCell>
                            <TableCell class="text-foreground">{{ formatSeconds(project.total_tracked_seconds) }}</TableCell>
                            <TableCell class="text-foreground">
                                <div class="font-medium">{{ formatEarnings(project.total_earnings) }}</div>
                                <div
                                    v-if="project.billing_mode !== 'non_billable' && project.outstanding > 0"
                                    class="text-xs text-amber-600 dark:text-amber-500"
                                >
                                    {{ formatEarnings(project.outstanding) }} outstanding
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium"
                                        :class="modeBadge(project).class"
                                    >
                                        {{ modeBadge(project).label }}
                                    </span>
                                    <span
                                        v-if="project.is_completed"
                                        class="inline-flex items-center rounded-md bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-300"
                                    >
                                        Completed
                                    </span>
                                    <span
                                        v-if="project.billing_mode !== 'non_billable' && project.is_fully_paid"
                                        class="inline-flex items-center rounded-md bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-300"
                                    >
                                        Paid
                                    </span>
                                    <span
                                        v-else-if="project.billing_mode !== 'non_billable' && project.amount_paid > 0"
                                        class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                                    >
                                        Partial
                                    </span>
                                    <span
                                        v-if="readyToComplete(project)"
                                        class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                                    >
                                        Ready to complete
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('clients.projects.tasks.index', [client.id, project.id])"
                                        :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                                    >
                                        Tasks
                                    </Link>
                                    <Link
                                        :href="route('clients.projects.edit', [client.id, project.id])"
                                        :class="buttonVariants({ variant: 'outline', size: 'sm' })"
                                    >
                                        Edit
                                    </Link>
                                    <Button v-if="!project.is_completed" variant="secondary" size="sm" @click="completeProject(project.id)">
                                        Complete
                                    </Button>
                                    <Button v-else-if="!project.is_fully_paid" variant="ghost" size="sm" @click="reopenProject(project.id)">
                                        Reopen
                                    </Button>
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="sm">Delete</Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Delete {{ project.name }}?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This moves the project, with all of its tasks, to the Trash. You can restore it from there, or
                                                    delete it permanently later.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction
                                                    class="bg-destructive text-white hover:bg-destructive/90"
                                                    @click="deleteProject(project.id)"
                                                >
                                                    Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="visibleProjects.length === 0">
                            <TableCell colspan="6" class="py-10 text-center text-muted-foreground">
                                <template v-if="projects.length === 0">
                                    No projects yet.
                                    <Link :href="route('clients.projects.create', client.id)" class="text-foreground underline underline-offset-4">
                                        Add the first project </Link
                                    >.
                                </template>
                                <template v-else> All projects are completed. Tick “Show completed” to see them. </template>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
