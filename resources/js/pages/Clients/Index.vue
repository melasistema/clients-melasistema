<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button, buttonVariants } from '@/components/ui/button';
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Client } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{ clients: Client[] }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Clients', href: '/clients' }];

const form = useForm({});

const deleteClient = (clientId: number) => {
    form.delete(route('clients.destroy', clientId), { preserveScroll: true });
};

const formatEarnings = (value: number) =>
    new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(value);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Clients" />

        <div class="px-4 py-6">
            <div class="flex items-start justify-between gap-4">
                <Heading title="Clients" description="Your clients, their contact details and total earnings." />
                <Link :href="route('clients.create')" :class="buttonVariants({ size: 'sm' })">Add client</Link>
            </div>

            <div class="mt-6 rounded-xl border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Company</TableHead>
                            <TableHead>Contact</TableHead>
                            <TableHead>VAT</TableHead>
                            <TableHead>Total earnings</TableHead>
                            <TableHead class="text-right">Actions</TableHead>
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
                                {{ formatEarnings(client.total_earnings) }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('clients.projects.index', client.id)"
                                        :class="buttonVariants({ variant: 'ghost', size: 'sm' })"
                                    >
                                        Projects
                                    </Link>
                                    <Link
                                        :href="route('clients.edit', client.id)"
                                        :class="buttonVariants({ variant: 'outline', size: 'sm' })"
                                    >
                                        Edit
                                    </Link>
                                    <AlertDialog>
                                        <AlertDialogTrigger as-child>
                                            <Button variant="destructive" size="sm">Delete</Button>
                                        </AlertDialogTrigger>
                                        <AlertDialogContent>
                                            <AlertDialogHeader>
                                                <AlertDialogTitle>Delete {{ client.company_name }}?</AlertDialogTitle>
                                                <AlertDialogDescription>
                                                    This permanently removes the client and all of its projects and
                                                    tasks. This action cannot be undone.
                                                </AlertDialogDescription>
                                            </AlertDialogHeader>
                                            <AlertDialogFooter>
                                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                <AlertDialogAction
                                                    class="bg-destructive text-white hover:bg-destructive/90"
                                                    @click="deleteClient(client.id)"
                                                >
                                                    Delete
                                                </AlertDialogAction>
                                            </AlertDialogFooter>
                                        </AlertDialogContent>
                                    </AlertDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="clients.length === 0">
                            <TableCell colspan="5" class="py-10 text-center text-muted-foreground">
                                No clients yet.
                                <Link :href="route('clients.create')" class="text-foreground underline underline-offset-4">
                                    Add your first client
                                </Link>.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
