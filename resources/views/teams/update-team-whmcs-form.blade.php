<x-jet-form-section submit="updateTeamName">
    <x-slot name="title">
        {{ __('WHMCS Settings') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Configure WHMCS api details to get information.') }}
    </x-slot>

    <x-slot name="form">
        <x-jet-action-message on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <div class="w-md-75">
            <div class="form-group">
                <x-jet-label for="whmcs_url" value="{{ __('WHMCS URL') }}" />

                <x-jet-input id="whmcs_url"
                             type="text"
                             class="{{ $errors->has('whmcs_url') ? 'is-invalid' : '' }}"
                             wire:model.defer="state.whmcs_url"
                             :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="name" />
            </div>
        </div>


    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
			<div class="d-flex align-items-baseline">
				<x-jet-button>
                    <div wire:loading class="spinner-border spinner-border-sm" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>

					{{ __('Save') }}
				</x-jet-button>
			</div>
        </x-slot>
    @endif
</x-jet-form-section>
