<?php

// Shared UI strings reused across pages. Page-specific copy lives in the
// per-page file (e.g. lang/en/clients.php). Reference from Vue with
// __('common.edit') via the useTranslations() composable.
return [
    'add' => 'Add',
    'create' => 'Create',
    'edit' => 'Edit',
    'open' => 'Open',
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

    // Rich-text (Markdown) editor toolbar — used by MarkdownEditor.vue, reusable
    // across task/project descriptions. Labels are the buttons' aria-labels.
    'editor' => [
        'bold' => 'Bold',
        'italic' => 'Italic',
        'heading1' => 'Heading 1',
        'heading2' => 'Heading 2',
        'bullet_list' => 'Bulleted list',
        'ordered_list' => 'Numbered list',
        'quote' => 'Quote',
        'code' => 'Inline code',
        'link' => 'Link',
        'undo' => 'Undo',
        'redo' => 'Redo',
        'link_prompt' => 'Enter a URL (leave empty to remove the link):',
    ],

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
