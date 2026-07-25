<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ client: Client }>();

const form = useForm({
    company_name: props.client.company_name,
    contact_name: props.client.contact_name,
    contact_email: props.client.contact_email,
    contact_phone: props.client.contact_phone,
    address: props.client.address,
    vat_number: props.client.vat_number,
    unique_code: props.client.unique_code,
});

const submit = () => {
    form.put(route('clients.update', props.client.id));
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '/clients' },
    { title: 'Edit', href: '/clients/' + props.client.id + '/edit' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit client" />

        <div class="px-4 py-6">
            <Heading title="Edit client" :description="`Update ${client.company_name}'s details.`" />

            <form class="mt-6 max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="company_name">Company name</Label>
                    <Input id="company_name" v-model="form.company_name" type="text" />
                    <InputError :message="form.errors.company_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="contact_name">Contact name</Label>
                    <Input id="contact_name" v-model="form.contact_name" type="text" />
                    <InputError :message="form.errors.contact_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="contact_email">Contact email</Label>
                    <Input id="contact_email" v-model="form.contact_email" type="email" />
                    <InputError :message="form.errors.contact_email" />
                </div>

                <div class="grid gap-2">
                    <Label for="contact_phone">Contact phone</Label>
                    <Input id="contact_phone" v-model="form.contact_phone" type="text" />
                    <InputError :message="form.errors.contact_phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="address">Address</Label>
                    <Textarea id="address" v-model="form.address" rows="3" />
                    <InputError :message="form.errors.address" />
                </div>

                <div class="grid gap-2">
                    <Label for="vat_number">VAT number</Label>
                    <Input id="vat_number" v-model="form.vat_number" type="text" />
                    <InputError :message="form.errors.vat_number" />
                </div>

                <div class="grid gap-2">
                    <Label for="unique_code">Unique code</Label>
                    <Input id="unique_code" v-model="form.unique_code" type="text" />
                    <InputError :message="form.errors.unique_code" />
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('clients.index')" :class="buttonVariants({ variant: 'ghost' })">Cancel</Link>
                    <Button type="submit" :disabled="form.processing">Save</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
