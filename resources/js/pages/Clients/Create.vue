<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

const form = useForm({
    company_name: '',
    contact_name: '',
    contact_email: '',
    contact_phone: '',
    address: '',
    vat_number: '',
    unique_code: '',
});

const submit = () => {
    form.post(route('clients.store'));
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Clients',
        href: '/clients',
    },
    {
        title: 'Create',
        href: '/clients/create',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Client
            </h2>
        </template>

        <div class="p-4 sm:p-6 lg:p-8">
            <div class="mx-auto max-w-xl">
                <form @submit.prevent="submit">
                    <div class="space-y-6">
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
                            <div class="mt-1">
                                <input v-model="form.company_name" type="text" name="company_name" id="company_name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>

                        <div>
                            <label for="contact_name" class="block text-sm font-medium text-gray-700">Contact Name</label>
                            <div class="mt-1">
                                <input v-model="form.contact_name" type="text" name="contact_name" id="contact_name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>

                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                            <div class="mt-1">
                                <input v-model="form.contact_email" type="email" name="contact_email" id="contact_email" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>

                        <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                            <div class="mt-1">
                                <input v-model="form.contact_phone" type="text" name="contact_phone" id="contact_phone" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                            <div class="mt-1">
                                <textarea v-model="form.address" name="address" id="address" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>
                        </div>

                        <div>
                            <label for="vat_number" class="block text-sm font-medium text-gray-700">VAT Number</label>
                            <div class="mt-1">
                                <input v-model="form.vat_number" type="text" name="vat_number" id="vat_number" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>

                        <div>
                            <label for="unique_code" class="block text-sm font-medium text-gray-700">Unique Code</label>
                            <div class="mt-1">
                                <input v-model="form.unique_code" type="text" name="unique_code" id="unique_code" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-x-6">
                        <a :href="route('clients.index')" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
