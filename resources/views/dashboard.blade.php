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
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-medium">{{ __("You're logged in!") }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Start a new journal entry or continue working through your dev notes.') }}
                        </p>
                    </div>

                    <a href="{{ route('journal-entries.create') }}" class="mt-4 inline-flex items-center rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0">
                        {{ __('Add new entry') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
