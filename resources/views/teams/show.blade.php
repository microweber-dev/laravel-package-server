<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Team Settings') }}
        </h2>
    </x-slot>

    <div>
        @livewire('teams.update-team-name-form', ['team' => $team])

        <x-jet-section-border />
        @livewire('teams.update-team-package-manager-form', ['team' => $team])
        <x-jet-section-border />
        @livewire('teams.update-team-whmcs-form', ['team' => $team])

        <x-jet-section-border />
        @livewire('teams.update-team-package-access-presets', ['team' => $team])

        @livewire('teams.team-member-manager', ['team' => $team])

        @if (Gate::check('delete', $team) && ! $team->personal_team)
            <x-jet-section-border />

            <div>
                @livewire('teams.delete-team-form', ['team' => $team])
            </div>
        @endif
    </div>
</x-app-layout>
