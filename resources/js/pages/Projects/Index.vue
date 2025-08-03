<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const formatSeconds = (totalSeconds: number) => {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

const props = defineProps<{ client: any; projects: any[] }>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
    {
        title: 'Clients',
        href: '/clients',
    },
    {
        title: 'Projects',
        href: '/clients/' + (props.client ? props.client.id : '') + '/projects',
    },
]);

const deleteProject = (projectId: number) => {
    if (typeof window !== 'undefined' && window.confirm('Are you sure you want to delete this project?')) {
        form.delete(route('clients.projects.destroy', [props.client.id, projectId]));
    }
};

const form = useForm({});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Projects for {{ client.company_name }}
            </h2>
        </template>

        <div class="p-4 sm:p-6 lg:p-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900">Projects</h1>
                    <p class="mt-2 text-sm text-gray-700">A list of all the projects for {{ client.company_name }}.</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <a :href="route('clients.projects.create', client.id)" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add project</a>
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Project Name</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Hourly Rate</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Time</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Earnings</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Paid At</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="project in projects" :key="project.id">
                                    <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                        <div class="flex items-center">
                                            <div class="ml-4">
                                                <div class="font-medium text-gray-900">{{ project.name }}</div>
                                                <div class="mt-1 text-gray-500">{{ project.description }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <div class="text-gray-900">{{ project.hourly_rate }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <div class="text-gray-900">{{ formatSeconds(project.total_seconds) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <div class="text-gray-900">€{{ project.total_earnings.toFixed(2) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <div class="text-gray-900">{{ project.paid_at }}</div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <a :href="route('clients.projects.tasks.index', [client.id, project.id])" class="text-indigo-600 hover:text-indigo-900">View Tasks<span class="sr-only">, {{ project.name }}</span></a>
                                        <a :href="route('clients.projects.edit', [client.id, project.id])" class="ml-4 text-indigo-600 hover:text-indigo-900">Edit<span class="sr-only">, {{ project.name }}</span></a>
                                        <button @click="deleteProject(project.id)" class="ml-4 text-red-600 hover:text-red-900">Delete<span class="sr-only">, {{ project.name }}</span></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
