<?php

// Strings for the authentication pages (login, register, password reset,
// email verification). Reference from Vue with __('auth.…').
return [
    'login' => [
        'title' => 'Log in to your account',
        'description' => 'Enter your email and password below to log in',
        'head' => 'Log in',
        'email' => 'Email address',
        'email_placeholder' => 'email@example.com',
        'password' => 'Password',
        'password_placeholder' => 'Password',
        'forgot' => 'Forgot password?',
        'remember' => 'Remember me',
        'submit' => 'Log in',
        'no_account' => "Don't have an account?",
        'signup' => 'Sign up',
    ],

    'register' => [
        'title' => 'Create an account',
        'description' => 'Enter your details below to create your account',
        'head' => 'Register',
        'name' => 'Name',
        'name_placeholder' => 'Full name',
        'email' => 'Email address',
        'email_placeholder' => 'email@example.com',
        'password' => 'Password',
        'password_placeholder' => 'Password',
        'confirm' => 'Confirm password',
        'confirm_placeholder' => 'Confirm password',
        'submit' => 'Create account',
        'have_account' => 'Already have an account?',
        'login' => 'Log in',
    ],

    'forgot' => [
        'title' => 'Forgot password',
        'description' => 'Enter your email to receive a password reset link',
        'head' => 'Forgot password',
        'email' => 'Email address',
        'email_placeholder' => 'email@example.com',
        'submit' => 'Email password reset link',
        'return' => 'Or, return to',
        'login' => 'log in',
    ],

    'reset' => [
        'title' => 'Reset password',
        'description' => 'Please enter your new password below',
        'head' => 'Reset password',
        'email' => 'Email',
        'password' => 'Password',
        'password_placeholder' => 'Password',
        'confirm' => 'Confirm Password',
        'confirm_placeholder' => 'Confirm password',
        'submit' => 'Reset password',
    ],

    'confirm' => [
        'title' => 'Confirm your password',
        'description' => 'This is a secure area of the application. Please confirm your password before continuing.',
        'head' => 'Confirm password',
        'password' => 'Password',
        'submit' => 'Confirm Password',
    ],

    'verify' => [
        'title' => 'Verify email',
        'description' => 'Please verify your email address by clicking on the link we just emailed to you.',
        'head' => 'Email verification',
        'sent' => 'A new verification link has been sent to the email address you provided during registration.',
        'resend' => 'Resend verification email',
        'logout' => 'Log out',
    ],
];
