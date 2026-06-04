@props(['name' => 'content', 'value' => '', 'required' => true, 'uploadUrl' => null])

<div x-data="markdownEditor()" class="border border-gray-300 rounded-md overflow-hidden">
    <!-- Toolbar -->
    <div class="flex items-center gap-1 px-2 py-1.5 bg-gray-50 border-b border-gray-300 flex-wrap">
        <button type="button" @click="wrap('**', '**')" title="Bold" class="p-1.5 rounded hover:bg-gray-200 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"/><path d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"/></svg>
        </button>
        <button type="button" @click="wrap('*', '*')" title="Italic" class="p-1.5 rounded hover:bg-gray-200 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 4h4m-2 0v16m-4 0h8"/><line x1="15" y1="4" x2="9" y2="20" stroke-width="2"/></svg>
        </button>
        <button type="button" @click="wrap('~~', '~~')" title="Strikethrough" class="p-1.5 rounded hover:bg-gray-200 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M16 4H9a4 4 0 000 8h6a4 4 0 010 8H8M3 12h18"/></svg>
        </button>
        <div class="w-px h-5 bg-gray-300 mx-1"></div>
        <button type="button" @click="prefix('# ')" title="Heading 1" class="p-1.5 rounded hover:bg-gray-200 text-gray-600 text-xs font-bold">H1</button>
        <button type="button" @click="prefix('## ')" title="Heading 2" class="p-1.5 rounded hover:bg-gray-200 text-gray-600 text-xs font-bold">H2</button>
        <button type="button" @click="prefix('### ')" title="Heading 3" class="p-1.5 rounded hover:bg-gray-200 text-gray-600 text-xs font-bold">H3</button>
        <div class="w-px h-5 bg-gray-300 mx-1"></div>
        <button type="button" @click="prefix('- ')" title="Bullet List" class="p-1.5 rounded hover:bg-gray-200 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
        </button>
        <button type="button" @click="prefix('1. ')" title="Numbered List" class="p-1.5 rounded hover:bg-gray-200 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M10 6h11M10 12h11M10 18h11M3 6h1M3 12h1M3 18h1"/></svg>
        </button>
        <button type="button" @click="prefix('> ')" title="Blockquote" class="p-1.5 rounded hover:bg-gray-200 text-gray-600">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4.583 17.321C3.553 16.227 3 15 3 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311C9.591 11.689 11 13.166 11 15c0 1.933-1.567 3.5-3.5 3.5-1.207 0-2.185-.476-2.917-1.179zM15.583 17.321C14.553 16.227 14 15 14 13.011c0-3.5 2.457-6.637 6.03-8.188l.893 1.378c-3.335 1.804-3.987 4.145-4.247 5.621.537-.278 1.24-.375 1.929-.311C20.591 11.689 22 13.166 22 15c0 1.933-1.567 3.5-3.5 3.5-1.207 0-2.185-.476-2.917-1.179z"/></svg>
        </button>
        <div class="w-px h-5 bg-gray-300 mx-1"></div>
        <button type="button" @click="wrap('`', '`')" title="Inline Code" class="p-1.5 rounded hover:bg-gray-200 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/></svg>
        </button>
        <button type="button" @click="insertCodeBlock()" title="Code Block" class="p-1.5 rounded hover:bg-gray-200 text-gray-600 text-xs font-mono">{}</button>
        <button type="button" @click="insertLink()" title="Link" class="p-1.5 rounded hover:bg-gray-200 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m9.86-2.54a4.5 4.5 0 00-1.242-7.244l-4.5-4.5a4.5 4.5 0 00-6.364 6.364L4.34 8.22"/></svg>
        </button>
        <button type="button" @click="insertTable()" title="Table" class="p-1.5 rounded hover:bg-gray-200 text-gray-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M10.875 12c-.621 0-1.125.504-1.125 1.125M12 12c.621 0 1.125.504 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m0 0v1.5"/></svg>
        </button>
        <button type="button" @click="prefix('---')" title="Horizontal Rule" class="p-1.5 rounded hover:bg-gray-200 text-gray-600 text-xs">HR</button>
        <div class="w-px h-5 bg-gray-300 mx-1"></div>
        @if($uploadUrl)
        <label title="Upload Image" class="p-1.5 rounded hover:bg-gray-200 text-gray-600 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/></svg>
            <input type="file" accept="image/*" @change="uploadImage($event)" class="hidden">
        </label>
        @endif
        <div class="flex-1"></div>
        <button type="button" @click="showPreview = !showPreview" :class="showPreview ? 'bg-indigo-100 text-indigo-700' : 'text-gray-600'" class="p-1.5 rounded hover:bg-gray-200 text-xs font-medium">Preview</button>
    </div>

    <!-- Editor + Preview -->
    <div class="flex" :class="showPreview ? 'divide-x divide-gray-300' : ''">
        <div :class="showPreview ? 'w-1/2' : 'w-full'">
            <textarea
                x-ref="editor"
                name="{{ $name }}"
                @if($required) required @endif
                @input="updatePreview()"
                @dragover.prevent="dragOver = true"
                @dragleave="dragOver = false"
                @drop.prevent="handleDrop($event)"
                :class="dragOver ? 'border-2 border-dashed border-indigo-400 bg-indigo-50' : ''"
                class="block w-full border-0 resize-y text-sm px-4 py-3 font-mono focus:ring-0 min-h-[400px]"
                placeholder="Write your article in Markdown...

# Heading 1
## Heading 2

**Bold**, *italic*, ~~strikethrough~~

- Bullet list
1. Numbered list

> Blockquote

`inline code`

```
code block
```

[Link text](url)
![Image alt](url)

| Column 1 | Column 2 |
|----------|----------|
| Cell 1   | Cell 2   |"
            >{{ $value }}</textarea>
        </div>

        <div x-show="showPreview" x-cloak class="w-1/2 overflow-y-auto bg-white min-h-[400px]">
            <div class="px-6 py-4 prose prose-sm max-w-none" x-html="previewHtml">
                <p class="text-gray-400 italic">Preview will appear here...</p>
            </div>
        </div>
    </div>

    <!-- Upload progress -->
    <div x-show="uploading" x-cloak class="px-3 py-2 bg-blue-50 border-t border-gray-300 text-xs text-blue-700">
        Uploading image...
    </div>
</div>

@once
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>
<script>
function markdownEditor() {
    return {
        showPreview: false,
        previewHtml: '',
        uploading: false,
        dragOver: false,
        uploadUrl: @json($uploadUrl),

        updatePreview() {
            if (this.showPreview) {
                const raw = this.$refs.editor.value;
                this.previewHtml = DOMPurify.sanitize(marked.parse(raw));
            }
        },

        wrap(before, after) {
            const el = this.$refs.editor;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const selected = el.value.substring(start, end) || 'text';
            el.value = el.value.substring(0, start) + before + selected + after + el.value.substring(end);
            el.selectionStart = start + before.length;
            el.selectionEnd = start + before.length + selected.length;
            el.focus();
            el.dispatchEvent(new Event('input'));
        },

        prefix(pre) {
            const el = this.$refs.editor;
            const start = el.selectionStart;
            const lineStart = el.value.lastIndexOf('\n', start - 1) + 1;
            el.value = el.value.substring(0, lineStart) + pre + el.value.substring(lineStart);
            el.selectionStart = el.selectionEnd = start + pre.length;
            el.focus();
            el.dispatchEvent(new Event('input'));
        },

        insertLink() {
            const url = prompt('Enter URL:');
            if (url) {
                const el = this.$refs.editor;
                const start = el.selectionStart;
                const end = el.selectionEnd;
                const selected = el.value.substring(start, end) || 'link text';
                const md = `[${selected}](${url})`;
                el.value = el.value.substring(0, start) + md + el.value.substring(end);
                el.focus();
                el.dispatchEvent(new Event('input'));
            }
        },

        insertTable() {
            const el = this.$refs.editor;
            const pos = el.selectionStart;
            const table = '\n| Column 1 | Column 2 | Column 3 |\n|----------|----------|----------|\n| Cell 1   | Cell 2   | Cell 3   |\n';
            el.value = el.value.substring(0, pos) + table + el.value.substring(pos);
            el.focus();
            el.dispatchEvent(new Event('input'));
        },

        insertCodeBlock() {
            const el = this.$refs.editor;
            const start = el.selectionStart;
            const end = el.selectionEnd;
            const selected = el.value.substring(start, end) || 'code here';
            const md = '\n```\n' + selected + '\n```\n';
            el.value = el.value.substring(0, start) + md + el.value.substring(end);
            el.focus();
            el.dispatchEvent(new Event('input'));
        },

        async uploadImage(event) {
            const file = event.target.files[0];
            if (!file || !this.uploadUrl) return;
            await this.doUpload(file);
            event.target.value = '';
        },

        async handleDrop(event) {
            this.dragOver = false;
            const file = event.dataTransfer.files[0];
            if (!file || !file.type.startsWith('image/') || !this.uploadUrl) return;
            await this.doUpload(file);
        },

        async doUpload(file) {
            this.uploading = true;
            try {
                const form = new FormData();
                form.append('image', file);
                const resp = await fetch(this.uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: form,
                });
                if (!resp.ok) throw new Error('Upload failed');
                const data = await resp.json();
                const el = this.$refs.editor;
                const pos = el.selectionStart;
                const md = `![${file.name}](${data.url})`;
                el.value = el.value.substring(0, pos) + md + el.value.substring(pos);
                el.focus();
                el.dispatchEvent(new Event('input'));
            } catch (e) {
                alert('Image upload failed: ' + e.message);
            } finally {
                this.uploading = false;
            }
        },

        init() {
            this.$watch('showPreview', (val) => { if (val) this.updatePreview(); });
        }
    }
}
</script>
@endpush
@endonce
