<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button, buttonVariants } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ clients: Client[] }>();

const { formatCurrency } = useFormatters();
const { __ } = useTranslations();

const breadcrumbs: BreadcrumbItem[] = [{ title: __('clients.title'), href: '/clients' }];

const form = useForm({});

const deleteClient = (clientId: number) => {
    form.delete(route('clients.destroy', clientId), { preserveScroll: true });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('clients.title')" />

        <div class="px-4 py-6">
            <div class="flex items-start justify-between gap-4">
                <Heading :title="__('clients.title')" :description="__('clients.description')" />
                <Link :href="route('clients.create')" :class="buttonVariants({ size: 'sm' })">{{ __('clients.add') }}</Link>
            </div>

            <div class="mt-6 rounded-xl border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>{{ __('clients.table.company') }}</TableHead>
                            <TableHead>{{ __('clients.table.contact') }}</TableHead>
                            <TableHead>{{ __('clients.table.vat') }}</TableHead>
                            <TableHead>{{ __('clients.table.earnings') }}</TableHead>
                            <TableHead class="text-right">{{ __('common.actions') }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="client in clients" :key="client.id">
                            <TableCell>
                                <div class="font-medium text-foreground">{{ client.company_name }}</div>
                                <div class="text-muted-foreground">{{ client.address }}</div>
                            </TableCell>
                            <TableCell>
                                <div class="text-foreground">{{ client.contact_name }}</div>
                                <div class="text-muted-foreground">{{ client.contact_email }}</div>
                            </TableCell>
                            <TableCell>
                                <div class="text-foreground">{{ client.vat_number }}</div>
                                <div class="text-muted-foreground">{{ client.unique_code }}</div>
                            </TableCell>
                            <TableCell class="font-medium text-foreground">
                                {{ formatCurrency(client.total_earnings) }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('clients.projects.index', client.id)"
                                        :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                                    >
                                        {{ __('clients.projects') }}
                                    </Link>
                                    <Link :href="route('clients.edit', client.id)" :class="buttonVariants({ variant: 'outline', size: 'sm' })">
                                        {{ __('common.edit') }}
                                    </Link>
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="sm">{{ __('common.delete') }}</Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>{{
                                                    __('clients.delete.title', { company: client.company_name })
                                                }}</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    {{ __('clients.delete.description') }}
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>{{ __('common.cancel') }}</AlertDialogCancel>
                                                <AlertDialogAction
                                                    class="bg-destructive text-white hover:bg-destructive/90"
                                                    @click="deleteClient(client.id)"
                                                >
                                                    {{ __('common.delete') }}
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="clients.length === 0">
                            <TableCell colspan="5" class="py-10 text-center text-muted-foreground">
                                {{ __('clients.empty') }}
                                <Link :href="route('clients.create')" class="text-foreground underline underline-offset-4">
                                    {{ __('clients.empty_cta') }} </Link
                                >.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
