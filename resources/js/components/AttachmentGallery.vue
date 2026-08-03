<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useFormatters } from '@/composables/useFormatters';
import { useTranslations } from '@/composables/useTranslations';
import type { Attachment } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { ExternalLink, FileText, LinkIcon, Paperclip, Trash2, Upload } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

// Parent-agnostic: it takes the already-resolved store URL (so a Project can
// reuse it later) and resolves the flat stream/destroy routes by id itself.
const props = defineProps<{ attachments: Attachment[]; storeUrl: string }>();

const { formatBytes } = useFormatters();
const { __ } = useTranslations();
const page = usePage();

const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const uploading = ref(false);
const linkUrl = ref('');
const linkTitle = ref('');

// Inertia surfaces validation failures on the shared `errors` bag.
const errors = computed(() => page.props.errors as Record<string, string>);

const uploadFiles = (files: FileList | File[]) => {
    // Upload sequentially — each is its own request so a rejection (size/type)
    // reports against just that file without dragging the others down.
    Array.from(files).forEach((file) => {
        router.post(
            props.storeUrl,
            { kind: 'file', file },
            {
                forceFormData: true,
                preserveScroll: true,
                onStart: () => (uploading.value = true),
                onFinish: () => (uploading.value = false),
            },
        );
    });
};

const onDrop = (event: DragEvent) => {
    isDragging.value = false;
    if (event.dataTransfer?.files?.length) {
        uploadFiles(event.dataTransfer.files);
    }
};

const onBrowse = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files?.length) {
        uploadFiles(target.files);
    }
    target.value = '';
};

// Paste-from-clipboard, the fast path for screenshots. Only act on image data so
// pasting text elsewhere on the page is never hijacked.
const onPaste = (event: ClipboardEvent) => {
    const files = Array.from(event.clipboardData?.items ?? [])
        .filter((item) => item.kind === 'file' && item.type.startsWith('image/'))
        .map((item) => item.getAsFile())
        .filter((file): file is File => file !== null);

    if (files.length) {
        uploadFiles(files);
    }
};

const addLink = () => {
    if (!linkUrl.value.trim()) {
        return;
    }

    router.post(
        props.storeUrl,
        { kind: 'link', url: linkUrl.value.trim(), title: linkTitle.value.trim() || null },
        {
            preserveScroll: true,
            onSuccess: () => {
                linkUrl.value = '';
                linkTitle.value = '';
            },
        },
    );
};

const remove = (id: number) => {
    router.delete(route('attachments.destroy', id), { preserveScroll: true });
};

onMounted(() => window.addEventListener('paste', onPaste));
onUnmounted(() => window.removeEventListener('paste', onPaste));
</script>

<template>
    <div class="rounded-xl border p-5">
        <div class="flex items-center gap-2">
            <Paperclip class="size-4 text-muted-foreground" />
            <h2 class="text-sm font-medium text-muted-foreground">{{ __('attachments.title') }}</h2>
        </div>

        <!-- Drop zone: drag files in, click to browse, or paste a screenshot. -->
        <button
            type="button"
            class="mt-4 flex w-full flex-col items-center justify-center gap-1 rounded-lg border border-dashed px-4 py-6 text-center transition-colors"
            :class="isDragging ? 'border-primary bg-primary/5' : 'border-input hover:bg-muted/50'"
            @click="fileInput?.click()"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="onDrop"
        >
            <Upload class="size-5 text-muted-foreground" />
            <span class="text-sm text-foreground">{{ __('attachments.dropzone') }}</span>
            <span class="text-xs text-muted-foreground">{{ __('attachments.dropzone_hint') }}</span>
            <span v-if="uploading" class="text-xs text-primary">{{ __('attachments.uploading') }}</span>
        </button>
        <input ref="fileInput" type="file" multiple class="hidden" @change="onBrowse" />
        <p v-if="errors.file" class="mt-2 text-sm text-destructive">{{ errors.file }}</p>

        <!-- Add an external link (Figma, staging, …). -->
        <div class="mt-3 flex flex-col gap-2 sm:flex-row">
            <Input v-model="linkUrl" type="url" :placeholder="__('attachments.link_url_placeholder')" @keyup.enter="addLink" />
            <Input
                v-model="linkTitle"
                type="text"
                class="sm:max-w-[12rem]"
                :placeholder="__('attachments.link_title_placeholder')"
                @keyup.enter="addLink"
            />
            <Button variant="outline" size="sm" class="shrink-0" :disabled="!linkUrl.trim()" @click="addLink">
                <LinkIcon class="size-4" />
                {{ __('attachments.add_link') }}
            </Button>
        </div>
        <p v-if="errors.url" class="mt-2 text-sm text-destructive">{{ errors.url }}</p>

        <!-- The gallery. -->
        <div v-if="attachments.length" class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div v-for="attachment in attachments" :key="attachment.id" class="group relative overflow-hidden rounded-lg border">
                <!-- Image: inline thumbnail linking to the full stream. -->
                <a
                    v-if="attachment.kind === 'file' && attachment.is_image"
                    :href="attachment.stream_url!"
                    target="_blank"
                    rel="noopener"
                    class="block"
                >
                    <img
                        :src="attachment.stream_url!"
                        :alt="attachment.title ?? attachment.original_filename ?? ''"
                        class="h-32 w-full bg-muted object-cover"
                    />
                </a>
                <!-- Non-image file: chip opening the stream (inline or download). -->
                <a
                    v-else-if="attachment.kind === 'file'"
                    :href="attachment.stream_url!"
                    target="_blank"
                    rel="noopener"
                    class="flex h-32 flex-col items-center justify-center gap-2 bg-muted/30 p-3 text-center"
                >
                    <FileText class="size-8 text-muted-foreground" />
                    <span class="line-clamp-2 text-xs text-foreground">{{ attachment.title || attachment.original_filename }}</span>
                </a>
                <!-- Link: chip to the external URL. -->
                <a
                    v-else
                    :href="attachment.url!"
                    target="_blank"
                    rel="noopener"
                    class="flex h-32 flex-col items-center justify-center gap-2 bg-muted/30 p-3 text-center"
                >
                    <ExternalLink class="size-8 text-muted-foreground" />
                    <span class="line-clamp-2 text-xs text-foreground">{{ attachment.title || attachment.url }}</span>
                </a>

                <!-- Caption + size + delete. -->
                <div class="flex items-center justify-between gap-2 border-t px-3 py-2">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-medium text-foreground">
                            {{ attachment.title || attachment.original_filename || attachment.url }}
                        </p>
                        <p v-if="attachment.kind === 'file' && attachment.size_bytes" class="text-xs text-muted-foreground">
                            {{ formatBytes(attachment.size_bytes) }}
                        </p>
                        <p v-else class="text-xs text-muted-foreground">{{ __('attachments.kind_link') }}</p>
                    </div>
                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button variant="ghost" size="icon" class="size-7 shrink-0 text-muted-foreground hover:text-destructive">
                                <Trash2 class="size-4" />
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>{{ __('attachments.delete.title') }}</AlertDialogTitle>
                                <AlertDialogDescription>{{ __('attachments.delete.description') }}</AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>{{ __('common.cancel') }}</AlertDialogCancel>
                                <AlertDialogAction class="bg-destructive text-white hover:bg-destructive/90" @click="remove(attachment.id)">
                                    {{ __('common.delete') }}
                                </AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>
        </div>
        <p v-else class="mt-5 text-sm text-muted-foreground italic">{{ __('attachments.empty') }}</p>
    </div>
</template>
