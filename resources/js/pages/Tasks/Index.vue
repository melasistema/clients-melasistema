<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{ client: any; project: any; tasks: any[] }>();

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
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
]);

const form = useForm({});

const currentTime = ref(Date.now());

onMounted(() => {
    setInterval(() => {
        currentTime.value = Date.now();
    }, 1000);
});

let timerInterval: number | undefined;

onMounted(() => {
    timerInterval = setInterval(() => {
        currentTime.value = Date.now();
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
    }
});

const deleteTask = (taskId: number) => {
    if (typeof window !== 'undefined' && window.confirm('Are you sure you want to delete this task?')) {
        form.delete(route('clients.projects.tasks.destroy', [props.client.id, props.project.id, taskId]));
    }
};

const startTimer = (taskId: number) => {
    form.post(route('clients.projects.tasks.startTimer', [props.client.id, props.project.id, taskId]));
};

const stopTimer = (taskId: number) => {
    form.post(route('clients.projects.tasks.stopTimer', [props.client.id, props.project.id, taskId]));
};

const formatTime = (timestamp: string) => {
    const startedAt = new Date(timestamp).getTime();
    const now = currentTime.value;
    const diffInSeconds = Math.floor((now - startedAt) / 1000);

    return formatSeconds(diffInSeconds);
};

const formatSeconds = (totalSeconds: number) => {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

const formatEarnings = (totalEarnings: number) => {
    return new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(totalEarnings);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tasks for {{ project.name }}
            </h2>
        </template>

        <div class="p-4 sm:p-6 lg:p-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-gray-900">Tasks</h1>
                    <p class="mt-2 text-sm text-gray-700">A list of all the tasks for {{ project.name }}.</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <a :href="route('clients.projects.tasks.create', [client.id, project.id])" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Add task</a>
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Description</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Total Time</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Timer</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Task Earnings</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="task in tasks" :key="task.id">
                                    <td class="whitespace-nowrap py-5 pl-4 pr-3 text-sm sm:pl-0">
                                        <div class="flex items-center">
                                            <div class="ml-4">
                                                <div class="font-medium text-gray-900">{{ task.description }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <div class="text-gray-900">{{ formatSeconds(task.total_seconds) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <div v-if="task.is_running" class="text-green-600">Running ({{ formatTime(task.timer_started_at) }})</div>
                                        <div v-else class="text-gray-500">Stopped</div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-5 text-sm text-gray-500">
                                        <div class="text-gray-900">{{ formatEarnings(task.this_task_total_entry) }}</div>
                                    </td>
                                    <td class="relative whitespace-nowrap py-5 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <a :href="route('clients.projects.tasks.edit', [client.id, project.id, task.id])" class="text-indigo-600 hover:text-indigo-900">Edit<span class="sr-only">, {{ task.description }}</span></a>
                                        <button v-if="!task.is_running" @click="startTimer(task.id)" class="ml-4 text-green-600 hover:text-green-900">Start Timer<span class="sr-only">, {{ task.description }}</span></button>
                                        <button v-else @click="stopTimer(task.id)" class="ml-4 text-orange-600 hover:text-orange-900">Stop Timer<span class="sr-only">, {{ task.description }}</span></button>
                                        <button @click="deleteTask(task.id)" class="ml-4 text-red-600 hover:text-red-900">Delete<span class="sr-only">, {{ task.description }}</span></button>
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
