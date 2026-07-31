import { usePage } from '@inertiajs/vue3';

type Replacements = Record<string, string | number>;

/**
 * Frontend counterpart to Laravel's `__()`. The active locale's messages are
 * loaded from lang/{locale}/*.php and shared as the `translations` prop by
 * HandleInertiaRequests (deep-merged over the fallback locale, so a missing
 * key falls back to the fallback language rather than showing the raw key).
 *
 * Keys are dot-namespaced by file: __('common.edit') reads lang/{locale}/common.php.
 * :placeholder tokens are filled from the replacements argument, Laravel-style:
 *   __('clients.delete.title', { company: 'Acme' })  // "Delete Acme?"
 *
 * Call this in `<script setup>`; it reads the current page's shared props.
 */
export function useTranslations() {
    const page = usePage();

    const __ = (key: string, replacements: Replacements = {}): string => {
        const messages = page.props.translations as Record<string, unknown>;

        // Walk the dot-path into the nested messages tree.
        const value = key.split('.').reduce<unknown>((node, part) => {
            if (node && typeof node === 'object' && part in (node as Record<string, unknown>)) {
                return (node as Record<string, unknown>)[part];
            }
            return undefined;
        }, messages);

        // Unknown key → return the key itself, exactly like Laravel's __().
        if (typeof value !== 'string') {
            return key;
        }

        return Object.entries(replacements).reduce((line, [token, replacement]) => line.replaceAll(`:${token}`, String(replacement)), value);
    };

    return { __ };
}
