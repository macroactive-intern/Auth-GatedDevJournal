<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dev Journal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="text-lg font-medium">
                        {{ __('Welcome back, :name.', ['name' => Auth::user()->name]) }}
                    </p>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('This authenticated journal area is only available after login.') }}
                    </p>

                    @isset($entries)
                        <div class="mt-6 space-y-4">
                            @forelse ($entries as $entry)
                                <article>
                                    <h3 class="font-semibold">
                                        <a href="{{ route('journal-entries.show', $entry) }}" class="hover:underline">
                                            {{ $entry->title }}
                                        </a>
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600">{{ str($entry->body)->words(20) }}</p>
                                </article>
                            @empty
                                <p class="text-sm text-gray-600">{{ __('No journal entries found.') }}</p>
                            @endforelse
                        </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
