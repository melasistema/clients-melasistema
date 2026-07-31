<?php

// Traduzioni condivise riusate in più pagine. Il testo specifico di una pagina
// vive nel file dedicato (es. lang/it/clients.php). Usare da Vue con
// __('common.edit') tramite il composable useTranslations().
return [
    'add' => 'Aggiungi',
    'create' => 'Crea',
    'edit' => 'Modifica',
    'delete' => 'Elimina',
    'cancel' => 'Annulla',
    'save' => 'Salva',
    'actions' => 'Azioni',
    'complete' => 'Completa',
    'reopen' => 'Riapri',
    'restore' => 'Ripristina',
    'tasks' => 'Attività',
    'status' => 'Stato',
    'description' => 'Descrizione',
    'deleted' => 'Eliminato',
    'saved' => 'Salvato.',

    // Struttura dell'app: la barra del timer in esecuzione nell'header (LiveTimer.vue),
    // mostrata su ogni pagina mentre il timer di un'attività è in corso.
    'timer' => [
        'running' => 'In esecuzione',
        'stop' => 'Ferma timer',
        'last' => 'Ultimo',
        'resume' => 'Riprendi',
        'dismiss' => 'Chiudi',
    ],

    // Struttura dell'app: navigazione della sidebar, link a piè di pagina e menu utente.
    'nav' => [
        'dashboard' => 'Dashboard',
        'clients' => 'Clienti',
        'trash' => 'Cestino',
        'github' => 'Repository GitHub',
        'documentation' => 'Documentazione',
        'settings' => 'Impostazioni',
        'logout' => 'Esci',
    ],
];
