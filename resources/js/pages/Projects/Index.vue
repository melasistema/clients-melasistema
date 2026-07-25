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
import { type BreadcrumbItem, type Client, type Project } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ client: Client; projects: Project[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '/clients' },
    { title: 'Projects', href: '/clients/' + props.client.id + '/projects' },
];

const form = useForm({});

const deleteProject = (projectId: number) => {
    form.delete(route('clients.projects.destroy', [props.client.id, projectId]), { preserveScroll: true });
};

const formatSeconds = (totalSeconds: number) => {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

const totalSecondsFor = (project: Project) =>
    (project.tasks ?? []).reduce((total, task) => total + task.total_seconds, 0);

const formatEarnings = (value: number) =>
    new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(value);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Projects" />

        <div class="px-4 py-6">
            <div class="flex items-start justify-between gap-4">
                <Heading title="Projects" :description="`Projects for ${client.company_name}, their rates and earnings.`" />
                <Link :href="route('clients.projects.create', client.id)" :class="buttonVariants({ size: 'sm' })">Add project</Link>
            </div>

            <div class="mt-6 rounded-xl border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Project</TableHead>
                            <TableHead>Hourly rate</TableHead>
                            <TableHead>Total time</TableHead>
                            <TableHead>Total earnings</TableHead>
                            <TableHead>Paid</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="project in projects" :key="project.id">
                            <TableCell>
                                <div class="font-medium text-foreground">{{ project.name }}</div>
                                <div class="text-muted-foreground">{{ project.description }}</div>
                            </TableCell>
                            <TableCell class="text-foreground">{{ formatEarnings(project.hourly_rate) }}</TableCell>
                            <TableCell class="text-foreground">{{ formatSeconds(totalSecondsFor(project)) }}</TableCell>
                            <TableCell class="font-medium text-foreground">{{ formatEarnings(project.total_earnings) }}</TableCell>
                            <TableCell class="text-muted-foreground">{{ project.paid_at ?? '—' }}</TableCell>
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
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="sm">Delete</Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Delete {{ project.name }}?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This permanently removes the project and all of its tasks. This
                                                    action cannot be undone.
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
                        <TableRow v-if="projects.length === 0">
                            <TableCell colspan="6" class="py-10 text-center text-muted-foreground">
                                No projects yet.
                                <Link
                                    :href="route('clients.projects.create', client.id)"
                                    class="text-foreground underline underline-offset-4"
                                >
                                    Add the first project
                                </Link>.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
