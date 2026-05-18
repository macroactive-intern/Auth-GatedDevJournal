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
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
