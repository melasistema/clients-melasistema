<?php

// Testi delle pagine Attività (elenco con timer, creazione, modifica).
return [
    'title' => 'Attività',
    'index_description' => 'Attività di :project, tempo tracciato e guadagni.',
    'add' => 'Aggiungi attività',
    'show_completed' => 'Mostra completate (:count)',
    'nudge' => 'Tutte le attività sono complete — segnare il progetto come concluso?',
    'mark_complete' => 'Segna progetto come completato',
    'table' => [
        'title' => 'Attività',
        'time' => 'Tempo totale',
        'timer' => 'Timer',
        'earnings' => 'Guadagni attività',
    ],
    'badge_done' => 'Fatto',
    'timer' => [
        'running' => 'In corso (:time)',
        'stopped' => 'Fermo',
    ],
    'start' => 'Avvia',
    'stop' => 'Ferma',
    'empty' => 'Nessuna attività ancora.',
    'empty_cta' => 'Aggiungi la prima attività',
    'all_completed' => 'Tutte le attività sono completate. Seleziona “Mostra completate” per vederle.',
    'delete' => [
        'title' => 'Eliminare questa attività?',
        'description' => 'Questo sposta “:title” e il suo tempo tracciato nel Cestino. Puoi ripristinarla da lì o eliminarla definitivamente in seguito.',
    ],
    'show' => [
        'details' => 'Dettagli',
        'no_description' => 'Nessuna descrizione. Modifica l’attività per aggiungerne una.',
        'back' => 'Torna alle attività',
    ],
    'form' => [
        'create_title' => 'Crea attività',
        'create_description' => 'Aggiungi una nuova attività per :project.',
        'edit_title' => 'Modifica attività',
        'edit_description' => 'Aggiorna questa attività per :project.',
        'name' => 'Titolo',
        'name_placeholder' => 'Titolo breve e leggibile',
        'description' => 'Descrizione',
        'description_placeholder' => 'Scrivi i dettagli… incolla da un’email o un PDF per mantenere la formattazione.',
        'description_hint' => 'Facoltativa. Testo formattato — usa la barra o il Markdown; incollando la formattazione viene mantenuta.',
        'time' => 'Tempo tracciato',
        'hours' => 'Ore',
        'minutes' => 'Minuti',
        'seconds' => 'Secondi',
        'total' => 'Totale',
    ],
];
