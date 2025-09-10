<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { PanelLeft, PanelLeftClose, PanelLeftOpen } from 'lucide-vue-next'
import { useSidebar } from './utils'

const props = defineProps<{
  class?: HTMLAttributes['class'],
  color?: string | null;
}>()

const { toggleSidebar, open } = useSidebar();

</script>

<template>
  <Button
    data-sidebar="trigger"
    data-slot="sidebar-trigger"
    variant="ghost"
    size="icon"
    :class="cn('h-7 w-7', props.class)"
    @click="toggleSidebar"
    :style="{ color: props?.color }"
  >
    <PanelLeftClose v-if="open" />
    <PanelLeftOpen v-if="!open" />
    <span class="sr-only">Toggle Sidebar</span>
  </Button>
</template>
