<x-app-layout>
    <x-slot name="header">
        <div class="font-weight-bold">
            {{ __('Create Team') }}
        </div>
    </x-slot>

    <div>
        @livewire('teams.create-team-form')
    </div>
</x-app-layout>
