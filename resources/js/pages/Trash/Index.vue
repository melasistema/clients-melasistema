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
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

interface TrashedClient {
    id: number;
    company_name: string;
    contact_email: string;
    deleted_at: string;
}
interface TrashedProject {
    id: number;
    name: string;
    client_name: string;
    deleted_at: string;
}
interface TrashedTask {
    id: number;
    description: string;
    project_name: string;
    client_name: string;
    deleted_at: string;
}

const props = defineProps<{
    clients: TrashedClient[];
    projects: TrashedProject[];
    tasks: TrashedTask[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Trash', href: '/trash' }];

const form = useForm({});

const restore = (name: string, id: number) => {
    form.put(route(name, id), { preserveScroll: true });
};

const purge = (name: string, id: number) => {
    form.delete(route(name, id), { preserveScroll: true });
};

const isEmpty = () => props.clients.length === 0 && props.projects.length === 0 && props.tasks.length === 0;

const formatDate = (value: string) =>
    value ? new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '';
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Trash" />

        <div class="px-4 py-6">
            <Heading title="Trash" description="Deleted items are kept here. Restore them, or delete permanently to purge for good." />

            <div v-if="isEmpty()" class="mt-6 rounded-xl border py-16 text-center text-muted-foreground">Trash is empty.</div>

            <!-- Clients -->
            <section v-if="clients.length" class="mt-8">
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">Clients</h2>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Company</TableHead>
                                <TableHead>Contact</TableHead>
                                <TableHead>Deleted</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="client in clients" :key="client.id">
                                <TableCell class="font-medium text-foreground">{{ client.company_name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ client.contact_email }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ formatDate(client.deleted_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="restore('clients.restore', client.id)">Restore</Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm">Delete forever</Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Permanently delete {{ client.company_name }}?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This erases the client and all of its projects and tasks for good. This cannot be undone.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction
                                                        class="bg-destructive text-white hover:bg-destructive/90"
                                                        @click="purge('clients.forceDelete', client.id)"
                                                    >
                                                        Delete forever
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </section>

            <!-- Projects -->
            <section v-if="projects.length" class="mt-8">
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">Projects</h2>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Project</TableHead>
                                <TableHead>Client</TableHead>
                                <TableHead>Deleted</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="project in projects" :key="project.id">
                                <TableCell class="font-medium text-foreground">{{ project.name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ project.client_name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ formatDate(project.deleted_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="restore('projects.restore', project.id)">Restore</Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm">Delete forever</Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Permanently delete {{ project.name }}?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This erases the project and all of its tasks for good. This cannot be undone.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction
                                                        class="bg-destructive text-white hover:bg-destructive/90"
                                                        @click="purge('projects.forceDelete', project.id)"
                                                    >
                                                        Delete forever
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </section>

            <!-- Tasks -->
            <section v-if="tasks.length" class="mt-8">
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">Tasks</h2>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Task</TableHead>
                                <TableHead>Project</TableHead>
                                <TableHead>Client</TableHead>
                                <TableHead>Deleted</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="task in tasks" :key="task.id">
                                <TableCell class="font-medium text-foreground">{{ task.description }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ task.project_name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ task.client_name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ formatDate(task.deleted_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="restore('tasks.restore', task.id)">Restore</Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm">Delete forever</Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Permanently delete this task?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This erases “{{ task.description }}” and its tracked time for good. This cannot be undone.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction
                                                        class="bg-destructive text-white hover:bg-destructive/90"
                                                        @click="purge('tasks.forceDelete', task.id)"
                                                    >
                                                        Delete forever
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
