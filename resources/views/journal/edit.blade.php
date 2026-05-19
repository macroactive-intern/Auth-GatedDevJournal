<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Journal Entry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('journal-entries.update', $entry) }}" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $entry->title)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('title')" />
                        </div>

                        <div>
                            <x-input-label for="body" :value="__('Log')" />
                            <textarea id="body" name="body" rows="14" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('body', $entry->body) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('body')" />
                        </div>

                        <div>
                            <x-input-label :value="__('Tags')" />
                            <div class="mt-2 grid gap-3 sm:grid-cols-5">
                                @for ($index = 0; $index < 5; $index++)
                                    <x-text-input
                                        name="tags[]"
                                        type="text"
                                        class="block w-full"
                                        :value="old('tags.' . $index, $entry->tags->pluck('name')->get($index))"
                                        maxlength="50"
                                        aria-label="{{ __('Tag ') . ($index + 1) }}"
                                    />
                                @endfor
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('tags')" />
                            <x-input-error class="mt-2" :messages="collect($errors->get('tags.*'))->flatten()->all()" />
                        </div>

                        <input type="hidden" name="is_public" value="0">
                        <label for="is_public" class="flex items-center gap-3">
                            <input id="is_public" name="is_public" type="checkbox" value="1" @checked(old('is_public', $entry->is_public)) class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">{{ __('Publish this entry') }}</span>
                        </label>

                        <div class="flex items-center justify-between gap-3">
                            <a href="{{ route('journal-entries.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                {{ __('Back to journal') }}
                            </a>

                            <div class="flex items-center gap-3">
                                <a href="{{ route('journal-entries.show', $entry) }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                                    {{ __('Cancel') }}
                                </a>

                                <x-primary-button>
                                    {{ __('Save changes') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
