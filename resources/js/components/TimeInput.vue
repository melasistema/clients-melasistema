<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/composables/useTranslations';
import { computed } from 'vue';

/**
 * Segmented time entry: three number steppers (H / M / S) that read and write a
 * single total-seconds value. Built from the shadcn `Input` primitive — native
 * number inputs give spinners + arrow-key increment for free, so there's no
 * fiddly "HH:MM:SS" string to hand-edit and no third-party picker.
 *
 * Minutes and seconds are clamped to 0–59; hours are open-ended.
 */
const model = defineModel<number>({ required: true });

const { __ } = useTranslations();

const clamp = (value: number, max: number): number => {
    if (Number.isNaN(value) || value < 0) return 0;
    if (value > max) return max;
    return Math.floor(value);
};

const hours = computed({
    get: () => Math.floor(model.value / 3600),
    set: (value: number) => {
        const h = Number.isNaN(value) || value < 0 ? 0 : Math.floor(value);
        model.value = h * 3600 + minutes.value * 60 + seconds.value;
    },
});

const minutes = computed({
    get: () => Math.floor((model.value % 3600) / 60),
    set: (value: number) => {
        model.value = hours.value * 3600 + clamp(value, 59) * 60 + seconds.value;
    },
});

const seconds = computed({
    get: () => model.value % 60,
    set: (value: number) => {
        model.value = hours.value * 3600 + minutes.value * 60 + clamp(value, 59);
    },
});
</script>

<template>
    <div class="grid max-w-xs grid-cols-3 gap-3">
        <div class="grid gap-1.5">
            <Input v-model.number="hours" type="number" min="0" inputmode="numeric" class="text-center tabular-nums" />
            <Label class="text-center text-xs font-normal text-muted-foreground">{{ __('tasks.form.hours') }}</Label>
        </div>
        <div class="grid gap-1.5">
            <Input v-model.number="minutes" type="number" min="0" max="59" inputmode="numeric" class="text-center tabular-nums" />
            <Label class="text-center text-xs font-normal text-muted-foreground">{{ __('tasks.form.minutes') }}</Label>
        </div>
        <div class="grid gap-1.5">
            <Input v-model.number="seconds" type="number" min="0" max="59" inputmode="numeric" class="text-center tabular-nums" />
            <Label class="text-center text-xs font-normal text-muted-foreground">{{ __('tasks.form.seconds') }}</Label>
        </div>
    </div>
</template>
