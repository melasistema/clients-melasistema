import { usePage } from '@inertiajs/vue3';

/**
 * Shared display formatters. Currency + locale come from the backend
 * (config/money.php, shared as `money` by HandleInertiaRequests), so every
 * page formats money identically and self-hosters switch currency in one place
 * rather than editing each Vue page.
 *
 * Call this in `<script setup>` — it reads the current page's shared props.
 */
export function useFormatters() {
    const page = usePage();

    // Built once per component: the money config is fixed for the session.
    const currency = new Intl.NumberFormat(page.props.money.locale, {
        style: 'currency',
        currency: page.props.money.currency,
    });

    // Money values arrive as either numbers (appended accessors) or decimal
    // strings (decimal:2 casts like a payment's `amount`); coerce either.
    const formatCurrency = (value: number | string): string => currency.format(Number(value));

    // Seconds → HH:MM:SS (zero-padded). Used for tracked time everywhere.
    const formatDuration = (totalSeconds: number): string => {
        const hours = Math.floor(totalSeconds / 3600);
        const minutes = Math.floor((totalSeconds % 3600) / 60);
        const seconds = totalSeconds % 60;

        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    };

    return { formatCurrency, formatDuration };
}
