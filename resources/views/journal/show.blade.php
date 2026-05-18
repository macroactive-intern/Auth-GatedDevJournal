<x-guest-layout>
    <div class="space-y-4">
        <h1 class="text-xl font-semibold text-gray-900">{{ $entry->title }}</h1>

        <p class="text-sm text-gray-600">
            {{ __('By :author', ['author' => $entry->user->name]) }}
        </p>

        <p class="text-sm text-gray-700">{{ $entry->body }}</p>

        <div class="flex flex-wrap gap-2">
            @foreach ($entry->tags as $tag)
                <a href="{{ route('tags.show', $tag) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                    #{{ $tag->name }}
                </a>
            @endforeach
        </div>
    </div>
</x-guest-layout>
