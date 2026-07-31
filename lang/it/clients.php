<?php

// Testi delle pagine Clienti. I token :placeholder vengono sostituiti dal
// secondo argomento di __(): __('clients.delete.title', { company }).
return [
    'title' => 'Clienti',
    'description' => 'I tuoi clienti, i loro contatti e i guadagni totali.',
    'add' => 'Aggiungi cliente',
    'projects' => 'Progetti',
    'table' => [
        'company' => 'Azienda',
        'contact' => 'Contatto',
        'vat' => 'P. IVA',
        'earnings' => 'Guadagni totali',
    ],
    'delete' => [
        'title' => 'Eliminare :company?',
        'description' => 'Questo sposta il cliente, con tutti i suoi progetti e attività, nel Cestino. Puoi ripristinarlo da lì o eliminarlo definitivamente in seguito.',
    ],
    'empty' => 'Nessun cliente ancora.',
    'empty_cta' => 'Aggiungi il tuo primo cliente',
    'form' => [
        'create_title' => 'Crea cliente',
        'create_description' => 'Aggiungi un nuovo cliente al tuo account.',
        'edit_title' => 'Modifica cliente',
        'edit_description' => 'Aggiorna i dati di :name.',
        'company_name' => 'Nome azienda',
        'contact_name' => 'Nome contatto',
        'contact_email' => 'Email contatto',
        'contact_phone' => 'Telefono contatto',
        'address' => 'Indirizzo',
        'vat_number' => 'Partita IVA',
        'unique_code' => 'Codice univoco',
    ],
];
