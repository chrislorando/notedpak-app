<script setup lang="ts">
import type { DateValue } from '@internationalized/date';
import { DateFormatter, getLocalTimeZone } from '@internationalized/date';
import { CalendarIcon } from 'lucide-vue-next';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const df = new DateFormatter('en-US', {
    dateStyle: 'long',
});

interface Props {
    title?: string;
    modelValue?: DateValue;
    class?: HTMLAttributes['class'];
    placeholder?: string;
}

interface Emits {
    (e: 'update:modelValue', value: DateValue | undefined): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

const value = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button variant="outline" :class="cn('w-[280px] justify-start text-left font-normal', !value && 'text-muted-foreground', props.class)">
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ value ? title + ' ' + df.format(value.toDate(getLocalTimeZone())) : (placeholder ?? 'Pick a date') }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <Calendar v-model="value" initial-focus />
        </PopoverContent>
    </Popover>
</template>
