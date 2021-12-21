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

                <x-jet-input-error for="whmcs_url" />
            </div>
        </div>


        <div class="w-md-75 mt-1">
            <div class="form-group">
                <x-jet-label for="whmcs_api_identifier" value="{{ __('WHMCS Api Identifier') }}" />

                <x-jet-input id="whmcs_api_identifier"
                             type="text"
                             class="{{ $errors->has('whmcs_api_identifier') ? 'is-invalid' : '' }}"
                             wire:model.defer="state.whmcs_api_identifier"
                             :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="whmcs_api_identifier" />
            </div>
        </div>

        <div class="w-md-75 mt-1">
            <div class="form-group">
                <x-jet-label for="whmcs_api_secret" value="{{ __('WHMCS Api Secret') }}" />

                <x-jet-input id="whmcs_api_secret"
                             type="text"
                             class="{{ $errors->has('whmcs_api_secret') ? 'is-invalid' : '' }}"
                             wire:model.defer="state.whmcs_api_secret"
                             :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="whmcs_api_secret" />
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
