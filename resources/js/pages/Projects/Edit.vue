<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ client: Client; project: Project }>();

const form = useForm({
    name: props.project.name,
    description: props.project.description,
    hourly_rate: props.project.hourly_rate,
    paid_at: props.project.paid_at,
});

const submit = () => {
    form.put(route('clients.projects.update', [props.client.id, props.project.id]));
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '/clients' },
    { title: 'Projects', href: '/clients/' + props.client.id + '/projects' },
    { title: 'Edit', href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/edit' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit project" />

        <div class="px-4 py-6">
            <Heading title="Edit project" :description="`Update ${project.name}.`" />

            <form class="mt-6 max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">Project name</Label>
                    <Input id="name" v-model="form.name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" v-model="form.description" rows="3" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="hourly_rate">Hourly rate (€)</Label>
                    <Input id="hourly_rate" v-model="form.hourly_rate" type="number" step="0.01" min="0" />
                    <InputError :message="form.errors.hourly_rate" />
                </div>

                <div class="grid gap-2">
                    <Label for="paid_at">Paid at</Label>
                    <Input id="paid_at" v-model="form.paid_at" type="datetime-local" />
                    <InputError :message="form.errors.paid_at" />
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('clients.projects.index', client.id)" :class="buttonVariants({ variant: 'ghost' })">Cancel</Link>
                    <Button type="submit" :disabled="form.processing">Save</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
