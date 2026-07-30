<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Money display (currency + locale)
    |--------------------------------------------------------------------------
    |
    | Every euro figure in the app is formatted on the frontend with
    | `Intl.NumberFormat`. This tool ships defaulting to euros formatted the
    | German way (1.234,56 €) because that is what the author uses — but it is
    | meant to be cloned and self-hosted by one freelancer, so a US freelancer
    | can switch to dollars, or a UK one to pounds, without editing any Vue.
    |
    | `currency` is an ISO 4217 code (EUR, USD, GBP, …) and drives the symbol.
    | `locale` is a BCP 47 tag (de-DE, en-US, en-GB, …) and drives grouping,
    | decimal separators and symbol placement. Set MONEY_CURRENCY / MONEY_LOCALE
    | in .env; both are shared to the frontend by HandleInertiaRequests.
    |
    */

    'currency' => env('MONEY_CURRENCY', 'EUR'),

    'locale' => env('MONEY_LOCALE', 'it-IT'),

];
