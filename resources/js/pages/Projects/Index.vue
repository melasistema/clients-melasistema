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
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{ client: Client; projects: Project[] }>();

const { formatCurrency, formatDuration } = useFormatters();
const { __ } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('clients.title'), href: '/clients' },
    { title: __('projects.title'), href: '/clients/' + props.client.id + '/projects' },
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
            return { label: __('projects.billing.fixed'), class: 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300' };
        case 'non_billable':
            return { label: __('projects.billing.non_billable'), class: 'bg-muted text-muted-foreground' };
        default:
            return { label: __('projects.billing.hourly'), class: 'bg-muted text-muted-foreground' };
    }
};

// Nudge predicate: all of a project's tasks are done but the project isn't marked
// complete yet. Tasks ride along in the index payload (eager-loaded).
const readyToComplete = (project: Project) =>
    !project.is_completed && (project.tasks?.length ?? 0) > 0 && (project.tasks ?? []).every((task) => task.is_completed);

const rateLabel = (project: Project) => {
    if (project.billing_mode === 'fixed') {
        return __('projects.rate.fee', { amount: formatCurrency(project.agreed_fee ?? 0) });
    }
    if (project.billing_mode === 'non_billable') {
        return '—';
    }
    return __('projects.rate.hourly', { amount: formatCurrency(project.hourly_rate) });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('projects.title')" />

        <div class="px-4 py-6">
            <div class="flex items-start justify-between gap-4">
                <Heading :title="__('projects.title')" :description="__('projects.index_description', { company: client.company_name })" />
                <Link :href="route('clients.projects.create', client.id)" :class="buttonVariants({ size: 'sm' })">{{ __('projects.add') }}</Link>
            </div>

            <label v-if="completedCount > 0" class="mt-4 flex items-center gap-2 text-sm text-muted-foreground">
                <Checkbox id="show-completed" v-model="showCompleted" />
                {{ __('projects.show_completed', { count: completedCount }) }}
            </label>

            <div class="mt-6 rounded-xl border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ __('projects.table.project') }}</TableHead>
                            <TableHead>{{ __('projects.table.rate') }}</TableHead>
                            <TableHead>{{ __('projects.table.time') }}</TableHead>
                            <TableHead>{{ __('projects.table.earnings') }}</TableHead>
                            <TableHead>{{ __('common.status') }}</TableHead>
                            <TableHead class="text-right">{{ __('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="project in visibleProjects" :key="project.id" :class="project.is_completed ? 'opacity-60' : ''">
                            <TableCell>
                                <div class="font-medium text-foreground">{{ project.name }}</div>
                                <div class="text-muted-foreground">{{ project.description }}</div>
                            </TableCell>
                            <TableCell class="text-foreground">{{ rateLabel(project) }}</TableCell>
                            <TableCell class="text-foreground">{{ formatDuration(project.total_tracked_seconds) }}</TableCell>
                            <TableCell class="text-foreground">
                                <div class="font-medium">{{ formatCurrency(project.total_earnings) }}</div>
                                <div
                                    v-if="project.billing_mode !== 'non_billable' && project.outstanding > 0"
                                    class="text-xs text-amber-600 dark:text-amber-500"
                                >
                                    {{ __('projects.outstanding', { amount: formatCurrency(project.outstanding) }) }}
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
                                        {{ __('projects.badge.completed') }}
                                    </span>
                                    <span
                                        v-if="project.billing_mode !== 'non_billable' && project.is_fully_paid"
                                        class="inline-flex items-center rounded-md bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-950 dark:text-green-300"
                                    >
                                        {{ __('projects.badge.paid') }}
                                    </span>
                                    <span
                                        v-else-if="project.billing_mode !== 'non_billable' && project.amount_paid > 0"
                                        class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                                    >
                                        {{ __('projects.badge.partial') }}
                                    </span>
                                    <span
                                        v-if="readyToComplete(project)"
                                        class="inline-flex items-center rounded-md bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                                    >
                                        {{ __('projects.badge.ready') }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('clients.projects.tasks.index', [client.id, project.id])"
                                        :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                                    >
                                        {{ __('common.tasks') }}
                                    </Link>
                                    <Link
                                        :href="route('clients.projects.edit', [client.id, project.id])"
                                        :class="buttonVariants({ variant: 'outline', size: 'sm' })"
                                    >
                                        {{ __('common.edit') }}
                                    </Link>
                                    <Button v-if="!project.is_completed" variant="secondary" size="sm" @click="completeProject(project.id)">
                                        {{ __('common.complete') }}
                                    </Button>
                                    <Button v-else-if="!project.is_fully_paid" variant="ghost" size="sm" @click="reopenProject(project.id)">
                                        {{ __('common.reopen') }}
                                    </Button>
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="sm">{{ __('common.delete') }}</Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>{{ __('projects.delete.title', { name: project.name }) }}</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    {{ __('projects.delete.description') }}
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>{{ __('common.cancel') }}</AlertDialogCancel>
                                                <AlertDialogAction
                                                    class="bg-destructive text-white hover:bg-destructive/90"
                                                    @click="deleteProject(project.id)"
                                                >
                                                    {{ __('common.delete') }}
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
                                    {{ __('projects.empty') }}
                                    <Link :href="route('clients.projects.create', client.id)" class="text-foreground underline underline-offset-4">
                                        {{ __('projects.empty_cta') }} </Link
                                    >.
                                </template>
                                <template v-else> {{ __('projects.all_completed') }} </template>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
