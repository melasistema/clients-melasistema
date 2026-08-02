<?php

// Stringhe per la pagina Report (ore e valore fatturabile per giorno e progetto).
return [
    'title' => 'Report',
    'subtitle' => 'Come le ore tracciate si suddividono per giorno e progetto.',

    'period' => [
        'this_month' => 'Questo mese',
        'last_month' => 'Mese scorso',
        'this_year' => 'Quest\'anno',
        'all_time' => 'Sempre',
    ],

    'stats' => [
        'hours' => 'Ore tracciate',
        'hours_sub' => 'su :count giorni',
        'value' => 'Valore fatturabile',
        'value_sub' => 'alla tariffa del momento',
        'days' => 'Giorni lavorati',
        'days_sub' => 'con tempo tracciato',
    ],

    'by_day' => [
        'title' => 'Dettaglio giornaliero',
        'subtitle' => 'Prima i più recenti',
        'empty' => 'Nessun tempo tracciato in questo periodo.',
    ],

    'by_project' => [
        'title' => 'Per progetto',
        'subtitle' => 'Prima quelli con più ore',
        'empty' => 'Nessun tempo tracciato in questo periodo.',
    ],
];
