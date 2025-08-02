<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{ client: any; project: any; task: any }>();

const form = useForm({
    description: props.task.description,
    minutes: props.task.minutes,
});

const submit = () => {
    form.put(route('clients.projects.tasks.update', [props.client.id, props.project.id, props.task.id]));
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: '/clients',
    },
    {
        title: 'Projects',
        href: '/clients/' + props.client.id + '/projects',
    },
    {
        title: 'Tasks',
        href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks',
    },
    {
        title: 'Edit',
        href: '/clients/' + props.client.id + '/projects/' + props.project.id + '/tasks/' + props.task.id + '/edit',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Task for {{ project.name }}
            </h2>
        </template>

        <div class="p-4 sm:p-6 lg:p-8">
            <div class="mx-auto max-w-xl">
                <form @submit.prevent="submit">
                    <div class="space-y-6">
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                            <div class="mt-1">
                                <textarea v-model="form.description" name="description" id="description" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>

                        <div>
                            <label for="minutes" class="block text-sm font-medium text-gray-700">Minutes</label>
                            <div class="mt-1">
                                <input v-model="form.minutes" type="number" name="minutes" id="minutes" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-x-6">
                        <a :href="route('clients.projects.tasks.index', [client.id, project.id])" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
