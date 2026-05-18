<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>

            <a href="{{ route('journal-entries.create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                {{ __('Add new entry') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto space-y-6 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium">{{ __("You're logged in!") }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Read public dev notes from the team or add a new journal entry of your own.') }}
                        </p>
                    </div>

                    <a href="{{ route('journal-entries.create') }}" class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0">
                        {{ __('Add new entry') }}
                    </a>
                </div>
            </div>

            <section class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium">{{ __('Public feed') }}</h3>

                        <a href="{{ route('journal.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            {{ __('My journal') }}
                        </a>
                    </div>

                    <div class="mt-6 space-y-5">
                        @forelse ($entries as $entry)
                            <article class="border-b border-gray-100 pb-5 last:border-b-0 last:pb-0">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h4 class="font-semibold text-gray-900">
                                            <a href="{{ route('journal-entries.show', $entry) }}" class="hover:underline">
                                                {{ $entry->title }}
                                            </a>
                                        </h4>

                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ __('By :author', ['author' => $entry->user->name]) }}
                                            <span class="mx-1">&middot;</span>
                                            {{ optional($entry->published_at)->format('M j, Y g:i A') ?? $entry->created_at->format('M j, Y g:i A') }}
                                        </p>
                                    </div>

                                    @can('edit', $entry)
                                        <a href="{{ route('journal-entries.edit', $entry) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                            {{ __('Edit') }}
                                        </a>
                                    @else
                                        <a href="{{ route('journal-entries.feedback.create', $entry) }}" class="inline-flex items-center rounded-md border border-indigo-600 px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            {{ __('Give feedback') }}
                                            <span class="ml-2 text-xs text-indigo-500">({{ $entry->feedback_count }})</span>
                                        </a>
                                    @endcan
                                </div>

                                <p class="mt-3 text-sm leading-6 text-gray-600">
                                    {{ str($entry->body)->words(35) }}
                                </p>

                                @if ($entry->tags->isNotEmpty())
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($entry->tags as $tag)
                                            <a href="{{ route('tags.show', $tag) }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">
                                                #{{ $tag->name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="rounded-md border border-dashed border-gray-300 p-6 text-center">
                                <p class="text-sm text-gray-600">{{ __('No public entries yet.') }}</p>
                                <a href="{{ route('journal-entries.create') }}" class="mt-3 inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                    {{ __('Write your first entry') }}
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
