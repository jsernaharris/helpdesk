@extends('layouts.portal')
@section('title', 'Knowledge Base')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Search -->
    <div class="mb-8">
        <form method="GET" class="flex gap-3">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search the knowledge base..." class="block w-full rounded-md border-gray-300 text-sm px-4 py-3 border focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit" class="rounded-md bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-500">Search</button>
        </form>
    </div>

    @if($searchResults)
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Search Results for "{{ $query }}"</h2>
        <div class="space-y-3">
            @forelse($searchResults as $article)
            <a href="{{ route('portal.kb.show', [$article->category, $article]) }}" class="block bg-white shadow rounded-lg p-4 hover:bg-gray-50">
                <h3 class="text-sm font-semibold text-indigo-600">{{ $article->title }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $article->excerpt ?? Str::limit(strip_tags($article->content), 150) }}</p>
            </a>
            @empty
            <p class="text-sm text-gray-500">No results found.</p>
            @endforelse
        </div>
    </div>
    @endif

    @if($featuredArticles->count())
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Featured Articles</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($featuredArticles as $article)
            <a href="{{ route('portal.kb.show', [$article->category, $article]) }}" class="bg-white shadow rounded-lg p-4 hover:bg-gray-50">
                <h3 class="text-sm font-semibold text-indigo-600">{{ $article->title }}</h3>
                <p class="text-xs text-gray-500 mt-1">{{ $article->excerpt ?? Str::limit(strip_tags($article->content), 100) }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Categories -->
    <h2 class="text-lg font-semibold text-gray-900 mb-4">Browse by Category</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($categories as $category)
        <a href="{{ route('portal.kb.category', $category) }}" class="bg-white shadow rounded-lg p-5 hover:bg-gray-50">
            <h3 class="text-base font-semibold text-gray-900">{{ $category->name }}</h3>
            @if($category->description)
            <p class="text-sm text-gray-500 mt-1">{{ $category->description }}</p>
            @endif
            <p class="text-xs text-indigo-600 mt-2">{{ $category->articles_count ?? $category->articles->count() }} articles</p>
        </a>
        @endforeach
    </div>
</div>

<!-- KB AI Chatbot -->
<div x-data="kbChat()" class="fixed bottom-6 right-6 z-50">
    <button x-show="!open" @click="open = true" class="rounded-full bg-indigo-600 text-white px-5 py-3 shadow-lg hover:bg-indigo-500 text-sm font-semibold">
        Ask AI
    </button>
    <div x-show="open" x-cloak class="bg-white rounded-lg shadow-2xl w-96 max-w-[95vw] flex flex-col" style="height: 520px;">
        <div class="flex items-center justify-between px-4 py-3 border-b">
            <h3 class="text-sm font-semibold text-gray-900">Knowledge Base Assistant</h3>
            <button @click="open = false" class="text-gray-400 hover:text-gray-600 text-xl leading-none">&times;</button>
        </div>
        <div x-ref="log" class="flex-1 overflow-y-auto p-4 space-y-3 text-sm">
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'text-right' : ''">
                    <div :class="m.role === 'user' ? 'bg-indigo-50 text-indigo-900' : 'bg-gray-100 text-gray-800'" class="inline-block rounded-lg px-3 py-2 max-w-[85%] text-left whitespace-pre-wrap" x-text="m.content"></div>
                    <template x-if="m.role === 'assistant' && m.citations && m.citations.length">
                        <div class="mt-1 text-xs text-gray-500">
                            Sources:
                            <template x-for="(c, idx) in m.citations" :key="c.id">
                                <span><a :href="`/portal/kb/${c.category_slug || ''}/${c.slug}`" class="text-indigo-600 hover:underline" x-text="`[${idx+1}] ${c.title}`"></a><span x-show="idx < m.citations.length - 1">, </span></span>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
            <p x-show="loading" class="text-xs text-gray-400">Thinking…</p>
            <p x-show="error" x-text="error" class="text-xs text-red-600"></p>
        </div>
        <form @submit.prevent="ask()" class="border-t p-3 flex gap-2">
            <input x-model="question" type="text" required minlength="3" maxlength="500" placeholder="Ask a question…" class="flex-1 rounded-md border-gray-300 text-sm px-3 py-2 border">
            <button type="submit" :disabled="loading" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-60">Send</button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function kbChat() {
        return {
            open: false,
            question: '',
            messages: [],
            loading: false,
            error: '',
            async ask() {
                if (!this.question.trim()) return;
                const q = this.question.trim();
                this.messages.push({ role: 'user', content: q });
                this.question = '';
                this.error = '';
                this.loading = true;
                this.$nextTick(() => this.scroll());
                try {
                    const r = await fetch('{{ route('portal.kb.chat') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ question: q }),
                    });
                    const j = await r.json();
                    if (!r.ok) throw new Error(j.error || 'Request failed');
                    this.messages.push({ role: 'assistant', content: j.answer, citations: j.citations });
                } catch (e) {
                    this.error = e.message;
                } finally {
                    this.loading = false;
                    this.$nextTick(() => this.scroll());
                }
            },
            scroll() {
                const el = this.$refs.log;
                if (el) el.scrollTop = el.scrollHeight;
            },
        };
    }
</script>
<style>[x-cloak] { display: none !important; }</style>
@endpush
@endsection
