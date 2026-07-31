<?php

// Strings for the account settings pages (profile, password, appearance) and
// the delete-account flow. Reference from Vue with __('settings.…').
return [
    'title' => 'Settings',
    'description' => 'Manage your profile and account settings',

    // Settings sub-navigation.
    'nav' => [
        'profile' => 'Profile',
        'password' => 'Password',
        'appearance' => 'Appearance',
    ],

    'profile' => [
        'title' => 'Profile settings',
        'info_title' => 'Profile information',
        'info_description' => 'Update your name and email address',
        'name' => 'Name',
        'name_placeholder' => 'Full name',
        'email' => 'Email address',
        'email_placeholder' => 'Email address',
        'unverified' => 'Your email address is unverified.',
        'resend' => 'Click here to resend the verification email.',
        'verification_sent' => 'A new verification link has been sent to your email address.',
    ],

    'password' => [
        'title' => 'Password settings',
        'update_title' => 'Update password',
        'update_description' => 'Ensure your account is using a long, random password to stay secure',
        'current' => 'Current password',
        'current_placeholder' => 'Current password',
        'new' => 'New password',
        'new_placeholder' => 'New password',
        'confirm' => 'Confirm password',
        'confirm_placeholder' => 'Confirm password',
        'save' => 'Save password',
    ],

    'appearance' => [
        'title' => 'Appearance settings',
        'description' => "Update your account's appearance settings",
        'light' => 'Light',
        'dark' => 'Dark',
        'system' => 'System',
    ],

    'delete' => [
        'title' => 'Delete account',
        'description' => 'Delete your account and all of its resources',
        'warning' => 'Warning',
        'warning_body' => 'Please proceed with caution, this cannot be undone.',
        'button' => 'Delete account',
        'confirm_title' => 'Are you sure you want to delete your account?',
        'confirm_description' => 'Once your account is deleted, all of its resources and data will also be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
        'password' => 'Password',
        'password_placeholder' => 'Password',
    ],
];
