<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project, type Task } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ client: Client; project: Project; task: Task }>();

const form = useForm({
    description: props.task.description,
    total_seconds: props.task.total_seconds,
});

const submit = () => {
    form.put(route('clients.projects.tasks.update', [props.client.id, props.project.id, props.task.id]));
};

const formattedTime = computed({
    get: () => {
        const totalSeconds = form.total_seconds;
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    },
    set: (value: string) => {
        const parts = value.split(':').map(Number);
        let totalSeconds = 0;
        if (parts.length === 3) {
            totalSeconds = parts[0] * 3600 + parts[1] * 60 + parts[2];
        } else if (parts.length === 2) {
            totalSeconds = parts[0] * 60 + parts[1];
        } else if (parts.length === 1) {
            totalSeconds = parts[0];
        }
        form.total_seconds = totalSeconds;
    },
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '/clients' },
    { title: 'Projects', href: '/clients/' + props.client.id + '/projects' },
    { title: 'Tasks', href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks' },
    {
        title: 'Edit',
        href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks/' + props.task.id + '/edit',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit task" />

        <div class="px-4 py-6">
            <Heading title="Edit task" :description="`Update this task for ${project.name}.`" />

            <form class="mt-6 max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" rows="3" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="total_seconds">Time (HH:MM:SS)</Label>
                    <Input id="total_seconds" v-model="formattedTime" type="text" placeholder="00:00:00" />
                    <InputError :message="form.errors.total_seconds" />
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('clients.projects.tasks.index', [client.id, project.id])" :class="buttonVariants({ variant: 'ghost' })">
                        Cancel
                    </Link>
                    <Button type="submit" :disabled="form.processing">Save</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
