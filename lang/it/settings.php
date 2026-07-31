<?php

// Testi delle pagine impostazioni account (profilo, password, aspetto) e del
// flusso di eliminazione account. Usare da Vue con __('settings.…').
return [
    'title' => 'Impostazioni',
    'description' => 'Gestisci il tuo profilo e le impostazioni dell\'account',

    // Sotto-navigazione delle impostazioni.
    'nav' => [
        'profile' => 'Profilo',
        'password' => 'Password',
        'appearance' => 'Aspetto',
    ],

    'profile' => [
        'title' => 'Impostazioni profilo',
        'info_title' => 'Informazioni profilo',
        'info_description' => 'Aggiorna il tuo nome e indirizzo email',
        'name' => 'Nome',
        'name_placeholder' => 'Nome completo',
        'email' => 'Indirizzo email',
        'email_placeholder' => 'Indirizzo email',
        'unverified' => 'Il tuo indirizzo email non è verificato.',
        'resend' => 'Clicca qui per inviare di nuovo l\'email di verifica.',
        'verification_sent' => 'Un nuovo link di verifica è stato inviato al tuo indirizzo email.',
    ],

    'password' => [
        'title' => 'Impostazioni password',
        'update_title' => 'Aggiorna password',
        'update_description' => 'Assicurati che il tuo account usi una password lunga e casuale per restare sicuro',
        'current' => 'Password attuale',
        'current_placeholder' => 'Password attuale',
        'new' => 'Nuova password',
        'new_placeholder' => 'Nuova password',
        'confirm' => 'Conferma password',
        'confirm_placeholder' => 'Conferma password',
        'save' => 'Salva password',
    ],

    'appearance' => [
        'title' => 'Impostazioni aspetto',
        'description' => 'Aggiorna le impostazioni di aspetto del tuo account',
        'light' => 'Chiaro',
        'dark' => 'Scuro',
        'system' => 'Sistema',
    ],

    'delete' => [
        'title' => 'Elimina account',
        'description' => 'Elimina il tuo account e tutte le sue risorse',
        'warning' => 'Attenzione',
        'warning_body' => 'Procedi con cautela, questa operazione non può essere annullata.',
        'button' => 'Elimina account',
        'confirm_title' => 'Sei sicuro di voler eliminare il tuo account?',
        'confirm_description' => 'Una volta eliminato l\'account, tutte le sue risorse e i suoi dati saranno eliminati definitivamente. Inserisci la tua password per confermare di voler eliminare definitivamente il tuo account.',
        'password' => 'Password',
        'password_placeholder' => 'Password',
    ],
];
