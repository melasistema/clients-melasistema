<?php

// Testi della pagina Cestino / recupero.
return [
    'title' => 'Cestino',
    'description' => 'Gli elementi eliminati restano qui. Ripristinali oppure elimina definitivamente per rimuoverli del tutto.',
    'empty' => 'Il cestino è vuoto.',
    'sections' => [
        'clients' => 'Clienti',
        'projects' => 'Progetti',
        'tasks' => 'Attività',
    ],
    'table' => [
        'company' => 'Azienda',
        'contact' => 'Contatto',
        'project' => 'Progetto',
        'client' => 'Cliente',
        'task' => 'Attività',
    ],
    'delete_forever' => 'Elimina definitivamente',
    'purge' => [
        'client_title' => 'Eliminare definitivamente :name?',
        'client_description' => 'Questo cancella il cliente e tutti i suoi progetti e attività per sempre. Non può essere annullato.',
        'project_title' => 'Eliminare definitivamente :name?',
        'project_description' => 'Questo cancella il progetto e tutte le sue attività per sempre. Non può essere annullato.',
        'task_title' => 'Eliminare definitivamente questa attività?',
        'task_description' => 'Questo cancella “:title” e il suo tempo tracciato per sempre. Non può essere annullato.',
    ],
];
