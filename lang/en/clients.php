<?php

// Strings for the Clients pages. :placeholder tokens are filled from the
// second argument of __(), Laravel-style: __('clients.delete.title', { company }).
return [
    'title' => 'Clients',
    'description' => 'Your clients, their contact details and total earnings.',
    'add' => 'Add client',
    'projects' => 'Projects',
    'table' => [
        'company' => 'Company',
        'contact' => 'Contact',
        'vat' => 'VAT',
        'earnings' => 'Total earnings',
    ],
    'delete' => [
        'title' => 'Delete :company?',
        'description' => 'This moves the client, with all of its projects and tasks, to the Trash. You can restore it from there, or delete it permanently later.',
    ],
    'empty' => 'No clients yet.',
    'empty_cta' => 'Add your first client',
    'form' => [
        'create_title' => 'Create client',
        'create_description' => 'Add a new client to your account.',
        'edit_title' => 'Edit client',
        'edit_description' => "Update :name's details.",
        'company_name' => 'Company name',
        'contact_name' => 'Contact name',
        'contact_email' => 'Contact email',
        'contact_phone' => 'Contact phone',
        'address' => 'Address',
        'vat_number' => 'VAT number',
        'unique_code' => 'Unique code',
    ],
];
