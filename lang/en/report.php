<?php

// Strings for the time Report page (hours + billable value by day and project).
return [
    'title' => 'Report',
    'subtitle' => 'How your tracked hours break down by day and project.',

    'period' => [
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_year' => 'This year',
        'all_time' => 'All time',
    ],

    'stats' => [
        'hours' => 'Hours tracked',
        'hours_sub' => 'across :count days',
        'value' => 'Billable value',
        'value_sub' => 'at the rate when tracked',
        'days' => 'Days worked',
        'days_sub' => 'with tracked time',
    ],

    'by_day' => [
        'title' => 'Daily breakdown',
        'subtitle' => 'Newest first',
        'empty' => 'No time tracked in this period.',
    ],

    'by_project' => [
        'title' => 'By project',
        'subtitle' => 'Most hours first',
        'empty' => 'No time tracked in this period.',
    ],
];
