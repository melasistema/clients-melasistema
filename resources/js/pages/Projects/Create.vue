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
    name: '',
    description: '',
    hourly_rate: 0,
    agreed_fee: '' as number | string,
});

const submit = () => {
    // An empty fee means "no fee" (hourly / non-billable) — send null, not "".
    form.transform((data) => ({
        ...data,
        agreed_fee: data.agreed_fee === '' || data.agreed_fee === null ? null : data.agreed_fee,
    })).post(route('clients.projects.store', props.client.id));
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Clients', href: '/clients' },
    { title: 'Projects', href: '/clients/' + props.client.id + '/projects' },
    { title: 'Create', href: '/clients/' + props.client.id + '/projects/create' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Create project" />

        <div class="px-4 py-6">
            <Heading title="Create project" :description="`Add a new project for ${client.company_name}.`" />

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
                    <p class="text-xs text-muted-foreground">Set to 0 for personal, non-billable work you still want to time-track.</p>
                </div>

                <div class="grid gap-2">
                    <Label for="agreed_fee">Agreed fee (€) — optional</Label>
                    <Input
                        id="agreed_fee"
                        v-model="form.agreed_fee"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="Leave blank to bill by the hour"
                    />
                    <InputError :message="form.errors.agreed_fee" />
                    <p class="text-xs text-muted-foreground">
                        Set a fee for a fixed-price project — the client owes this amount and tracked time becomes reference only. Leave blank to bill
                        hourly.
                    </p>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('clients.projects.index', client.id)" :class="buttonVariants({ variant: 'ghost' })">Cancel</Link>
                    <Button type="submit" :disabled="form.processing">Save</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
