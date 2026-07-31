<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const { __ } = useTranslations();

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
    { title: __('clients.title'), href: '/clients' },
    { title: __('common.create'), href: '/clients/create' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('clients.form.create_title')" />

        <div class="px-4 py-6">
            <Heading :title="__('clients.form.create_title')" :description="__('clients.form.create_description')" />

            <form class="mt-6 max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="company_name">{{ __('clients.form.company_name') }}</Label>
                    <Input id="company_name" v-model="form.company_name" type="text" />
                    <InputError :message="form.errors.company_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="contact_name">{{ __('clients.form.contact_name') }}</Label>
                    <Input id="contact_name" v-model="form.contact_name" type="text" />
                    <InputError :message="form.errors.contact_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="contact_email">{{ __('clients.form.contact_email') }}</Label>
                    <Input id="contact_email" v-model="form.contact_email" type="email" />
                    <InputError :message="form.errors.contact_email" />
                </div>

                <div class="grid gap-2">
                    <Label for="contact_phone">{{ __('clients.form.contact_phone') }}</Label>
                    <Input id="contact_phone" v-model="form.contact_phone" type="text" />
                    <InputError :message="form.errors.contact_phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="address">{{ __('clients.form.address') }}</Label>
                    <Textarea id="address" v-model="form.address" rows="3" />
                    <InputError :message="form.errors.address" />
                </div>

                <div class="grid gap-2">
                    <Label for="vat_number">{{ __('clients.form.vat_number') }}</Label>
                    <Input id="vat_number" v-model="form.vat_number" type="text" />
                    <InputError :message="form.errors.vat_number" />
                </div>

                <div class="grid gap-2">
                    <Label for="unique_code">{{ __('clients.form.unique_code') }}</Label>
                    <Input id="unique_code" v-model="form.unique_code" type="text" />
                    <InputError :message="form.errors.unique_code" />
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('clients.index')" :class="buttonVariants({ variant: 'ghost' })">{{ __('common.cancel') }}</Link>
                    <Button type="submit" :disabled="form.processing">{{ __('common.save') }}</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
