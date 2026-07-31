<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button, buttonVariants } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{ client: Client }>();

const { __ } = useTranslations();

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
    { title: __('clients.title'), href: '/clients' },
    { title: __('projects.title'), href: '/clients/' + props.client.id + '/projects' },
    { title: __('common.create'), href: '/clients/' + props.client.id + '/projects/create' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('projects.form.create_title')" />

        <div class="px-4 py-6">
            <Heading
                :title="__('projects.form.create_title')"
                :description="__('projects.form.create_description', { company: client.company_name })"
            />

            <form class="mt-6 max-w-xl space-y-6" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">{{ __('projects.form.name') }}</Label>
                    <Input id="name" v-model="form.name" type="text" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">{{ __('common.description') }}</Label>
                    <Textarea id="description" v-model="form.description" rows="3" />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="hourly_rate">{{ __('projects.form.hourly_rate') }}</Label>
                    <Input id="hourly_rate" v-model="form.hourly_rate" type="number" step="0.01" min="0" />
                    <InputError :message="form.errors.hourly_rate" />
                    <p class="text-xs text-muted-foreground">{{ __('projects.form.hourly_rate_help') }}</p>
                </div>

                <div class="grid gap-2">
                    <Label for="agreed_fee">{{ __('projects.form.agreed_fee') }}</Label>
                    <Input
                        id="agreed_fee"
                        v-model="form.agreed_fee"
                        type="number"
                        step="0.01"
                        min="0"
                        :placeholder="__('projects.form.agreed_fee_placeholder')"
                    />
                    <InputError :message="form.errors.agreed_fee" />
                    <p class="text-xs text-muted-foreground">
                        {{ __('projects.form.agreed_fee_help') }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <Link :href="route('clients.projects.index', client.id)" :class="buttonVariants({ variant: 'ghost' })">{{
                        __('common.cancel')
                    }}</Link>
                    <Button type="submit" :disabled="form.processing">{{ __('common.save') }}</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
