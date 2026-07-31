<?php

// Testi delle pagine di autenticazione (accesso, registrazione, reset password,
// verifica email). Usare da Vue con __('auth.…').
return [
    'login' => [
        'title' => 'Accedi al tuo account',
        'description' => 'Inserisci email e password qui sotto per accedere',
        'head' => 'Accedi',
        'email' => 'Indirizzo email',
        'email_placeholder' => 'email@esempio.com',
        'password' => 'Password',
        'password_placeholder' => 'Password',
        'forgot' => 'Password dimenticata?',
        'remember' => 'Ricordami',
        'submit' => 'Accedi',
        'no_account' => 'Non hai un account?',
        'signup' => 'Registrati',
    ],

    'register' => [
        'title' => 'Crea un account',
        'description' => 'Inserisci i tuoi dati qui sotto per creare il tuo account',
        'head' => 'Registrati',
        'name' => 'Nome',
        'name_placeholder' => 'Nome completo',
        'email' => 'Indirizzo email',
        'email_placeholder' => 'email@esempio.com',
        'password' => 'Password',
        'password_placeholder' => 'Password',
        'confirm' => 'Conferma password',
        'confirm_placeholder' => 'Conferma password',
        'submit' => 'Crea account',
        'have_account' => 'Hai già un account?',
        'login' => 'Accedi',
    ],

    'forgot' => [
        'title' => 'Password dimenticata',
        'description' => 'Inserisci la tua email per ricevere un link di reset della password',
        'head' => 'Password dimenticata',
        'email' => 'Indirizzo email',
        'email_placeholder' => 'email@esempio.com',
        'submit' => 'Invia link di reset password',
        'return' => 'Oppure torna a',
        'login' => 'accedi',
    ],

    'reset' => [
        'title' => 'Reimposta password',
        'description' => 'Inserisci la tua nuova password qui sotto',
        'head' => 'Reimposta password',
        'email' => 'Email',
        'password' => 'Password',
        'password_placeholder' => 'Password',
        'confirm' => 'Conferma password',
        'confirm_placeholder' => 'Conferma password',
        'submit' => 'Reimposta password',
    ],

    'confirm' => [
        'title' => 'Conferma la tua password',
        'description' => 'Questa è un\'area protetta dell\'applicazione. Conferma la tua password prima di continuare.',
        'head' => 'Conferma password',
        'password' => 'Password',
        'submit' => 'Conferma password',
    ],

    'verify' => [
        'title' => 'Verifica email',
        'description' => 'Verifica il tuo indirizzo email cliccando sul link che ti abbiamo appena inviato.',
        'head' => 'Verifica email',
        'sent' => 'Un nuovo link di verifica è stato inviato all\'indirizzo email fornito durante la registrazione.',
        'resend' => 'Invia di nuovo l\'email di verifica',
        'logout' => 'Esci',
    ],
];
