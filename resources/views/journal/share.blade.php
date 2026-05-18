<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Share Journal Entry') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold">{{ $entry->title }}</h3>
                    <p class="mt-4 text-sm text-gray-600">{{ __('This signed link is valid for 7 days.') }}</p>
                    <p class="mt-4 break-all rounded-md bg-gray-100 p-3 text-sm">{{ $shareUrl }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
