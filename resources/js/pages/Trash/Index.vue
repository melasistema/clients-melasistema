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
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
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

const { formatDate } = useFormatters();
const { __ } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [{ title: __('trash.title'), href: '/trash' }];

const form = useForm({});

const restore = (name: string, id: number) => {
    form.put(route(name, id), { preserveScroll: true });
};

const purge = (name: string, id: number) => {
    form.delete(route(name, id), { preserveScroll: true });
};

const isEmpty = () => props.clients.length === 0 && props.projects.length === 0 && props.tasks.length === 0;
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('trash.title')" />

        <div class="px-4 py-6">
            <Heading :title="__('trash.title')" :description="__('trash.description')" />

            <div v-if="isEmpty()" class="mt-6 rounded-xl border py-16 text-center text-muted-foreground">{{ __('trash.empty') }}</div>

            <!-- Clients -->
            <section v-if="clients.length" class="mt-8">
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">{{ __('trash.sections.clients') }}</h2>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ __('trash.table.company') }}</TableHead>
                                <TableHead>{{ __('trash.table.contact') }}</TableHead>
                                <TableHead>{{ __('common.deleted') }}</TableHead>
                                <TableHead class="text-right">{{ __('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="client in clients" :key="client.id">
                                <TableCell class="font-medium text-foreground">{{ client.company_name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ client.contact_email }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ formatDate(client.deleted_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="restore('clients.restore', client.id)">{{
                                            __('common.restore')
                                        }}</Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm">{{ __('trash.delete_forever') }}</Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>{{
                                                        __('trash.purge.client_title', { name: client.company_name })
                                                    }}</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        {{ __('trash.purge.client_description') }}
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>{{ __('common.cancel') }}</AlertDialogCancel>
                                                    <AlertDialogAction
                                                        class="bg-destructive text-white hover:bg-destructive/90"
                                                        @click="purge('clients.forceDelete', client.id)"
                                                    >
                                                        {{ __('trash.delete_forever') }}
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
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">{{ __('trash.sections.projects') }}</h2>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ __('trash.table.project') }}</TableHead>
                                <TableHead>{{ __('trash.table.client') }}</TableHead>
                                <TableHead>{{ __('common.deleted') }}</TableHead>
                                <TableHead class="text-right">{{ __('common.actions') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="project in projects" :key="project.id">
                                <TableCell class="font-medium text-foreground">{{ project.name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ project.client_name }}</TableCell>
                                <TableCell class="text-muted-foreground">{{ formatDate(project.deleted_at) }}</TableCell>
                                <TableCell>
                                    <div class="flex items-center justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="restore('projects.restore', project.id)">{{
                                            __('common.restore')
                                        }}</Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm">{{ __('trash.delete_forever') }}</Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>{{ __('trash.purge.project_title', { name: project.name }) }}</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        {{ __('trash.purge.project_description') }}
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>{{ __('common.cancel') }}</AlertDialogCancel>
                                                    <AlertDialogAction
                                                        class="bg-destructive text-white hover:bg-destructive/90"
                                                        @click="purge('projects.forceDelete', project.id)"
                                                    >
                                                        {{ __('trash.delete_forever') }}
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
                <h2 class="mb-2 text-sm font-medium text-muted-foreground">{{ __('trash.sections.tasks') }}</h2>
                <div class="rounded-xl border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ __('trash.table.task') }}</TableHead>
                                <TableHead>{{ __('trash.table.project') }}</TableHead>
                                <TableHead>{{ __('trash.table.client') }}</TableHead>
                                <TableHead>{{ __('common.deleted') }}</TableHead>
                                <TableHead class="text-right">{{ __('common.actions') }}</TableHead>
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
                                        <Button variant="outline" size="sm" @click="restore('tasks.restore', task.id)">{{
                                            __('common.restore')
                                        }}</Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm">{{ __('trash.delete_forever') }}</Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>{{ __('trash.purge.task_title') }}</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        {{ __('trash.purge.task_description', { description: task.description }) }}
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>{{ __('common.cancel') }}</AlertDialogCancel>
                                                    <AlertDialogAction
                                                        class="bg-destructive text-white hover:bg-destructive/90"
                                                        @click="purge('tasks.forceDelete', task.id)"
                                                    >
                                                        {{ __('trash.delete_forever') }}
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
