<x-guest-layout>
    <div class="space-y-6">
        <h1 class="text-xl font-semibold text-gray-900">
            {{ __('Entries tagged :tag', ['tag' => $tag->name]) }}
        </h1>

        <div class="space-y-4">
            @forelse ($entries as $entry)
                <article class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                    <h2 class="text-lg font-semibold">
                        <a href="{{ route('journal-entries.show', $entry) }}" class="hover:underline">
                            {{ $entry->title }}
                        </a>
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        {{ __('By :author', ['author' => $entry->user->name]) }}
                    </p>
                    <p class="mt-4 text-sm text-gray-700">
                        {{ str($entry->body)->words(40) }}
                    </p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach ($entry->tags as $entryTag)
                            <a href="{{ route('tags.show', $entryTag) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                                #{{ $entryTag->name }}
                            </a>
                        @endforeach
                    </div>
                </article>
            @empty
                <p class="text-sm text-gray-600">{{ __('No public entries use this tag yet.') }}</p>
            @endforelse
        </div>
    </div>
</x-guest-layout>
