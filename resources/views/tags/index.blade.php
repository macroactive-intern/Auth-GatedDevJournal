<x-guest-layout>
    <div class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-900">{{ __('Tags') }}</h1>

        <div class="flex flex-wrap gap-3">
            @forelse ($tags as $tag)
                <a href="{{ route('tags.show', $tag) }}" class="inline-flex items-center rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <span>{{ $tag->name }}</span>
                    <span class="ms-2 text-gray-500">{{ $tag->journal_entries_count }}</span>
                </a>
            @empty
                <p class="text-sm text-gray-600">{{ __('No tags have been created yet.') }}</p>
            @endforelse
        </div>
    </div>
</x-guest-layout>
