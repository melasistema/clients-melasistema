<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import { cn } from '@/lib/utils';
import { type Editor } from '@tiptap/core';
import Placeholder from '@tiptap/extension-placeholder';
import StarterKit from '@tiptap/starter-kit';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { Bold, Code, Heading1, Heading2, Italic, Link as LinkIcon, List, ListOrdered, Quote, Redo, Undo } from 'lucide-vue-next';
import { Markdown, type MarkdownStorage } from 'tiptap-markdown';
import { onBeforeUnmount, watch } from 'vue';

// A WYSIWYG editor whose value is Markdown text. Rich paste (from email, PDF,
// the web) is handled by ProseMirror mapping the HTML clipboard onto the schema
// — anything outside the schema is dropped, so it doubles as a sanitizer. The
// stored value stays portable Markdown, never HTML. Reusable and parent-agnostic
// (a Project description can mount the same component).
const props = withDefaults(defineProps<{ modelValue: string; placeholder?: string }>(), {
    placeholder: '',
});

const emit = defineEmits<{ (e: 'update:modelValue', value: string): void }>();

const { __ } = useTranslations();

// tiptap-markdown augments editor.storage.markdown but doesn't widen the Storage
// type, so read it through the exported MarkdownStorage shape.
const markdownOf = (e: Editor): string => (e.storage as unknown as { markdown: MarkdownStorage }).markdown.getMarkdown();

// @tiptap/vue-3 already defers `new Editor()` to onMounted, so this never runs on
// the server — the editor is created client-side only.
const editor = useEditor({
    // The initial value is Markdown; the Markdown extension parses it.
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            // Links are configured here (StarterKit v3 bundles the Link extension):
            // don't follow on click while editing, and pin a safe protocol allowlist
            // so a pasted `javascript:` URL can never be stored.
            link: {
                openOnClick: false,
                autolink: true,
                protocols: ['http', 'https', 'mailto'],
                HTMLAttributes: { rel: 'noopener nofollow', target: '_blank' },
            },
        }),
        // html:false keeps raw HTML out of both the parsed input and the stored
        // Markdown — the value is always clean Markdown.
        Markdown.configure({ html: false, transformPastedText: true, linkify: true }),
        Placeholder.configure({ placeholder: () => props.placeholder }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm dark:prose-invert max-w-none min-h-32 px-3 py-2 focus:outline-none',
        },
    },
    onUpdate: ({ editor }) => {
        emit('update:modelValue', markdownOf(editor));
    },
});

// Reflect external changes (e.g. a form reset) without fighting the user's cursor:
// only reset when the incoming value differs from what the editor already holds.
watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== markdownOf(editor.value)) {
            editor.value.commands.setContent(value, { emitUpdate: false });
        }
    },
);

// Set or clear a link on the current selection.
const toggleLink = () => {
    if (!editor.value) {
        return;
    }

    if (editor.value.isActive('link')) {
        editor.value.chain().focus().unsetLink().run();
        return;
    }

    const previous = editor.value.getAttributes('link').href as string | undefined;
    const url = window.prompt(__('common.editor.link_prompt'), previous ?? 'https://');

    if (url === null) {
        return; // cancelled
    }

    if (url === '') {
        editor.value.chain().focus().unsetLink().run();
        return;
    }

    editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
};

onBeforeUnmount(() => editor.value?.destroy());

// A toolbar button's classes: compact, ghost-like, highlighted when its mark/node
// is active on the selection.
const buttonClass = (active: boolean) =>
    cn(
        'inline-flex size-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground disabled:pointer-events-none disabled:opacity-40',
        active && 'bg-accent text-accent-foreground',
    );
</script>

<template>
    <div class="rounded-md border shadow-xs focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50">
        <!-- Toolbar: the common formatting actions, plus markdown shortcuts work while typing. -->
        <div v-if="editor" class="flex flex-wrap items-center gap-0.5 border-b p-1">
            <button
                type="button"
                :class="buttonClass(editor.isActive('bold'))"
                :aria-label="__('common.editor.bold')"
                @click="editor.chain().focus().toggleBold().run()"
            >
                <Bold class="size-4" />
            </button>
            <button
                type="button"
                :class="buttonClass(editor.isActive('italic'))"
                :aria-label="__('common.editor.italic')"
                @click="editor.chain().focus().toggleItalic().run()"
            >
                <Italic class="size-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-border"></span>
            <button
                type="button"
                :class="buttonClass(editor.isActive('heading', { level: 1 }))"
                :aria-label="__('common.editor.heading1')"
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
            >
                <Heading1 class="size-4" />
            </button>
            <button
                type="button"
                :class="buttonClass(editor.isActive('heading', { level: 2 }))"
                :aria-label="__('common.editor.heading2')"
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
            >
                <Heading2 class="size-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-border"></span>
            <button
                type="button"
                :class="buttonClass(editor.isActive('bulletList'))"
                :aria-label="__('common.editor.bullet_list')"
                @click="editor.chain().focus().toggleBulletList().run()"
            >
                <List class="size-4" />
            </button>
            <button
                type="button"
                :class="buttonClass(editor.isActive('orderedList'))"
                :aria-label="__('common.editor.ordered_list')"
                @click="editor.chain().focus().toggleOrderedList().run()"
            >
                <ListOrdered class="size-4" />
            </button>
            <button
                type="button"
                :class="buttonClass(editor.isActive('blockquote'))"
                :aria-label="__('common.editor.quote')"
                @click="editor.chain().focus().toggleBlockquote().run()"
            >
                <Quote class="size-4" />
            </button>
            <button
                type="button"
                :class="buttonClass(editor.isActive('code'))"
                :aria-label="__('common.editor.code')"
                @click="editor.chain().focus().toggleCode().run()"
            >
                <Code class="size-4" />
            </button>
            <button type="button" :class="buttonClass(editor.isActive('link'))" :aria-label="__('common.editor.link')" @click="toggleLink">
                <LinkIcon class="size-4" />
            </button>
            <span class="mx-1 h-5 w-px bg-border"></span>
            <button
                type="button"
                :class="buttonClass(false)"
                :aria-label="__('common.editor.undo')"
                :disabled="!editor.can().undo()"
                @click="editor.chain().focus().undo().run()"
            >
                <Undo class="size-4" />
            </button>
            <button
                type="button"
                :class="buttonClass(false)"
                :aria-label="__('common.editor.redo')"
                :disabled="!editor.can().redo()"
                @click="editor.chain().focus().redo().run()"
            >
                <Redo class="size-4" />
            </button>
        </div>

        <EditorContent :editor="editor" />
    </div>
</template>
