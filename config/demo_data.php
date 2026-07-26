<?php

/*
|--------------------------------------------------------------------------
| Demo / Development Seed Data
|--------------------------------------------------------------------------
|
| Centralized, human-editable sample data used by DemoDataSeeder to give
| local/staging environments realistic clients, projects and tasks to
| develop against. This is the single source of truth — add or tweak
| records here, never inline in the seeder.
|
| The seeder is IDEMPOTENT: records are matched on the natural keys noted
| below and updated in place, so re-running `db:seed` never duplicates
| data. It is also guarded to NEVER run in production (real users' data
| lives there). See DemoDataSeeder.
|
| Billing mode is derived per project (see Project::getBillingModeAttribute):
|   - `agreed_fee` set        => fixed-price (owed = the fee; time is reference)
|   - no fee, `hourly_rate` > 0 => hourly    (owed = tracked time x rate)
|   - no fee, `hourly_rate` = 0 => non-billable (personal work; time only)
| `payments` is the ledger (deposit / milestone / final); `completed_at`
| marks delivered work. Relative date strings like "-15 days" are resolved.
|
| Time is stored on tasks as `total_seconds`; the helper below lets us
| express durations as readable hours.
|
*/

$hours = static fn (float $h): int => (int) round($h * 3600);

return [

    // The demo account everything below is attached to.
    // Matched on `email`; safe to log in with during development.
    'user' => [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ],

    // Each client is matched on `contact_email` (unique in the schema).
    // Projects are matched on (client, `name`); tasks on (project, `description`);
    // payments on (project, `note`).
    'clients' => [
        [
            'company_name' => 'Acme Corporation',
            'contact_name' => 'John Carter',
            'contact_email' => 'john.carter@acme.example',
            'contact_phone' => '+1 415 555 0110',
            'address' => '500 Market Street, San Francisco, CA',
            'vat_number' => 'US123456789',
            'unique_code' => 'ACME-SF',
            'projects' => [
                [
                    // Hourly, in progress: no fee, no payments yet.
                    'name' => 'Corporate Website Redesign',
                    'description' => 'Full rebuild of the marketing site on the new design system.',
                    'hourly_rate' => 85.00,
                    'agreed_fee' => null,
                    'completed_at' => null,
                    'tasks' => [
                        ['description' => 'Discovery & stakeholder interviews', 'total_seconds' => $hours(6)],
                        ['description' => 'Wireframes and design system audit', 'total_seconds' => $hours(9.5)],
                        ['description' => 'Homepage build (Vue + Inertia)', 'total_seconds' => $hours(12)],
                        ['description' => 'Responsive QA and polish', 'total_seconds' => $hours(4.25)],
                    ],
                ],
                [
                    // Hourly, delivered and fully settled with a single payment.
                    'name' => 'Quarterly SEO Retainer',
                    'description' => 'Ongoing technical SEO and content optimization.',
                    'hourly_rate' => 70.00,
                    'agreed_fee' => null,
                    'completed_at' => '-15 days',
                    'tasks' => [
                        ['description' => 'Core Web Vitals fixes', 'total_seconds' => $hours(3)],
                        ['description' => 'Structured data rollout', 'total_seconds' => $hours(2.5)],
                    ],
                    'payments' => [
                        ['amount' => 385.00, 'paid_at' => '-12 days', 'note' => 'Final balance'],
                    ],
                ],
            ],
        ],
        [
            'company_name' => 'Nordic Design Studio',
            'contact_name' => 'Freya Lindqvist',
            'contact_email' => 'freya@nordic.example',
            'contact_phone' => '+46 8 555 0123',
            'address' => 'Sveavägen 12, Stockholm',
            'vat_number' => 'SE556677889901',
            'unique_code' => 'NORDIC-STO',
            'projects' => [
                [
                    // Fixed-price, in progress: a 12,000 fee with a 30% deposit paid.
                    'name' => 'E-commerce Platform',
                    'description' => 'Headless storefront with custom checkout.',
                    'hourly_rate' => 95.00,
                    'agreed_fee' => 12000.00,
                    'completed_at' => null,
                    'tasks' => [
                        ['description' => 'Cart & checkout API integration', 'total_seconds' => $hours(14)],
                        ['description' => 'Payment gateway (Stripe) wiring', 'total_seconds' => $hours(7.75)],
                        ['description' => 'Order confirmation emails', 'total_seconds' => $hours(2)],
                    ],
                    'payments' => [
                        ['amount' => 3600.00, 'paid_at' => '-30 days', 'note' => 'Deposit (30%)'],
                    ],
                ],
            ],
        ],
        [
            'company_name' => 'GreenLeaf Organics',
            'contact_name' => 'Marco Bianchi',
            'contact_email' => 'marco@greenleaf.example',
            'contact_phone' => '+39 06 5550 0199',
            'address' => 'Via Roma 44, Milano',
            'vat_number' => 'IT01234560017',
            'unique_code' => 'GREEN-MI',
            'projects' => [
                [
                    // Fixed-price, delivered and fully paid across two installments.
                    'name' => 'Mobile App MVP',
                    'description' => 'Subscription box companion app — first release.',
                    'hourly_rate' => 65.00,
                    'agreed_fee' => 7000.00,
                    'completed_at' => '-40 days',
                    'tasks' => [
                        ['description' => 'Auth & onboarding flow', 'total_seconds' => $hours(8)],
                        ['description' => 'Subscription management screens', 'total_seconds' => $hours(10.5)],
                        ['description' => 'Push notification setup', 'total_seconds' => $hours(3.25)],
                    ],
                    'payments' => [
                        ['amount' => 3500.00, 'paid_at' => '-70 days', 'note' => 'Deposit (50%)'],
                        ['amount' => 3500.00, 'paid_at' => '-38 days', 'note' => 'Final balance'],
                    ],
                ],
            ],
        ],
        [
            // Personal / non-billable work: a freelancer time-tracking their own
            // projects. Zero rate, no fee, no payments — hours are the whole point.
            'company_name' => 'Myself',
            'contact_name' => 'Personal Projects',
            'contact_email' => 'me@personal.example',
            'contact_phone' => '',
            'address' => '',
            'vat_number' => '',
            'unique_code' => 'SELF',
            'projects' => [
                [
                    'name' => 'Portfolio Website',
                    'description' => 'My own site — nobody pays me, but I still track the time.',
                    'hourly_rate' => 0.00,
                    'agreed_fee' => null,
                    'completed_at' => null,
                    'tasks' => [
                        ['description' => 'Design & content', 'total_seconds' => $hours(5.5)],
                        ['description' => 'Build & deploy', 'total_seconds' => $hours(7)],
                    ],
                ],
            ],
        ],
    ],
];
