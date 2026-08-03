<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import TimeInput from '@/components/TimeInput.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client, type Project, type Task } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ client: Client; project: Project; task: Task }>();

const { __ } = useTranslations();

const form = useForm({
    title: props.task.title,
    description: props.task.description ?? '',
    total_seconds: props.task.total_seconds,
});

const submit = () => {
    form.put(route('clients.projects.tasks.update', [props.client.id, props.project.id, props.task.id]));
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: __('clients.title'), href: '/clients' },
    { title: __('projects.title'), href: '/clients/' + props.client.id + '/projects' },
    { title: __('tasks.title'), href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks' },
    {
        title: __('common.edit'),
        href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks/' + props.task.id + '/edit',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('tasks.form.edit_title')" />

        <div class="px-4 py-6">
            <Heading :title="__('tasks.form.edit_title')" :description="__('tasks.form.edit_description', { project: project.name })" />

            <form class="mt-6 max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="title">{{ __('tasks.form.name') }}</Label>
                    <Input id="title" v-model="form.title" type="text" :placeholder="__('tasks.form.name_placeholder')" />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">{{ __('tasks.form.description') }}</Label>
                    <Textarea id="description" v-model="form.description" rows="5" />
                    <p class="text-xs text-muted-foreground">{{ __('tasks.form.description_hint') }}</p>
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label>{{ __('tasks.form.time') }}</Label>
                    <TimeInput v-model="form.total_seconds" />
                    <InputError :message="form.errors.total_seconds" />
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('clients.projects.tasks.index', [client.id, project.id])" :class="buttonVariants({ variant: 'ghost' })">
                        {{ __('common.cancel') }}
                    </Link>
                    <Button type="submit" :disabled="form.processing">{{ __('common.save') }}</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
