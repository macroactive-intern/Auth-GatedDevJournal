<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ Auth::user()->is($entry->user) ? __('Entry Feedback') : __('Give Feedback') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="border-b border-gray-100 pb-6">
                        <h3 class="text-lg font-medium">{{ $entry->title }}</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ __('By :author', ['author' => $entry->user->name]) }}
                        </p>
                        <p class="mt-3 text-sm leading-6 text-gray-600">
                            {{ str($entry->body)->words(45) }}
                        </p>
                    </div>

                    @if (Auth::user()->isNot($entry->user))
                        <form method="POST" action="{{ route('journal-entries.feedback.store', $entry) }}" class="mt-6 space-y-6">
                            @csrf

                            <div>
                                <x-input-label for="body" :value="__('Feedback')" />
                                <textarea id="body" name="body" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('body') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('body')" />
                            </div>

                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                    {{ __('Cancel') }}
                                </a>

                                <x-primary-button>
                                    {{ __('Send feedback') }}
                                </x-primary-button>
                            </div>
                        </form>
                    @else
                        <div class="mt-6 rounded-md border border-gray-200 p-4">
                            <p class="text-sm text-gray-600">
                                {{ __('This is your entry. Feedback from other users appears below.') }}
                            </p>
                        </div>
                    @endif

                    <section class="mt-8 border-t border-gray-100 pt-6">
                        <h3 class="text-lg font-medium">{{ __('Feedback') }}</h3>

                        <div class="mt-4 space-y-4">
                            @forelse ($entry->feedback as $feedback)
                                <article class="rounded-md border border-gray-200 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $feedback->user->name }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $feedback->created_at->format('M j, Y g:i A') }}
                                        </p>
                                    </div>

                                    <p class="mt-3 text-sm leading-6 text-gray-600">
                                        {{ $feedback->body }}
                                    </p>
                                </article>
                            @empty
                                <p class="text-sm text-gray-600">{{ __('No feedback has been added yet.') }}</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
