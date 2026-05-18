<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Give Feedback') }}
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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
