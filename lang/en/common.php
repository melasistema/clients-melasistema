<?php

// Shared UI strings reused across pages. Page-specific copy lives in the
// per-page file (e.g. lang/en/clients.php). Reference from Vue with
// __('common.edit') via the useTranslations() composable.
return [
    'add' => 'Add',
    'create' => 'Create',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'cancel' => 'Cancel',
    'save' => 'Save',
    'actions' => 'Actions',
    'complete' => 'Complete',
    'reopen' => 'Reopen',
    'restore' => 'Restore',
    'tasks' => 'Tasks',
    'status' => 'Status',
    'description' => 'Description',
    'deleted' => 'Deleted',
    'saved' => 'Saved.',

    // App chrome: the persistent running-timer bar in the header (LiveTimer.vue),
    // shown on every page while a task's timer runs.
    'timer' => [
        'running' => 'Currently running',
        'stop' => 'Stop timer',
        'last' => 'Last',
        'resume' => 'Resume',
        'dismiss' => 'Dismiss',
    ],

    // App chrome: sidebar navigation, footer links and the user menu.
    'nav' => [
        'dashboard' => 'Dashboard',
        'clients' => 'Clients',
        'report' => 'Report',
        'trash' => 'Trash',
        'github' => 'GitHub Repo',
        'documentation' => 'Documentation',
        'settings' => 'Settings',
        'logout' => 'Log out',
    ],
];
