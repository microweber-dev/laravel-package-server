<x-jet-form-section submit="updateTeamWhmcs">
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

        <div>
            @if (!isset($this->connection_status['success']))
                <div class="alert alert-danger">
                    @if (isset($this->connection_status['message']))
                        {{$this->connection_status['message']}}
                    @else
                        Something went wrong. Can\'t connect to the WHMCS.
                    @endif
                </div>
            @else
                <div class="alert alert-success">
                    Connection with WHMCS is successfully.
                </div>
            @endif
        </div>

        <div class="w-md-75">
            <div class="form-group">
                <x-jet-label for="whmcs_url" value="{{ __('WHMCS URL') }}" />

                <x-jet-input id="whmcs_url"
                             type="text"
                             class="{{ $errors->has('whmcs_url') ? 'is-invalid' : '' }}"
                             wire:model.defer="settings.whmcs_url"
                             :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="whmcs_url" />
            </div>
        </div>

         <div class="w-md-75 mt-4">
            <div class="form-group">
                <x-jet-label for="whmcs_auth_type" value="{{ __('WHMSC Auth Type') }}" />
                <select id="whmcs_auth_type" name="whmcs_auth_type" wire:model="settings.whmcs_auth_type" class="form-control">
                    <option value="api">API</option>
                    <option value="password">Username &amp; Password</option>
                </select>
            </div>
        </div>

        @if(isset($this->settings['whmcs_auth_type']) && $this->settings['whmcs_auth_type'] == 'password')

            <div class="w-md-75 mt-4">
                <div class="form-group">
                    <x-jet-label for="whmcs_username" value="{{ __('WHMSC Username') }}" />

                    <x-jet-input id="whmcs_username"
                                 type="text"
                                 class="{{ $errors->has('whmcs_username') ? 'is-invalid' : '' }}"
                                 wire:model.defer="settings.whmcs_username"
                                 :disabled="! Gate::check('update', $team)" />

                    <x-jet-input-error for="whmcs_username" />
                </div>
            </div>

            <div class="w-md-75 mt-3">
                <div class="form-group">
                    <x-jet-label for="whmcs_password" value="{{ __('WHMSC Password') }}" />

                    <x-jet-input id="whmcs_password"
                                 type="text"
                                 class="{{ $errors->has('whmcs_password') ? 'is-invalid' : '' }}"
                                 wire:model.defer="settings.whmcs_password"
                                 :disabled="! Gate::check('update', $team)" />

                    <x-jet-input-error for="whmcs_password" />
                </div>
            </div>


        @else
        <div class="w-md-75 mt-4">
            <div class="form-group">
                <x-jet-label for="whmcs_api_identifier" value="{{ __('WHMCS Api Identifier') }}" />

                <x-jet-input id="whmcs_api_identifier"
                             type="text"
                             class="{{ $errors->has('whmcs_api_identifier') ? 'is-invalid' : '' }}"
                             wire:model.defer="settings.whmcs_api_identifier"
                             :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="whmcs_api_identifier" />
            </div>
        </div>

        <div class="w-md-75 mt-2">
            <div class="form-group">
                <x-jet-label for="whmcs_api_secret" value="{{ __('WHMCS Api Secret') }}" />

                <x-jet-input id="whmcs_api_secret"
                             type="text"
                             class="{{ $errors->has('whmcs_api_secret') ? 'is-invalid' : '' }}"
                             wire:model.defer="settings.whmcs_api_secret"
                             :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="whmcs_api_secret" />
            </div>
        </div>
        @endif


    </x-slot>

    @if (Gate::check('update', $team))
        <x-slot name="actions">
			<div class="d-flex align-items-baseline">

                <button type="button" wire:click="getConnectionStatus" class="btn btn-outline-dark">{{ __('Test Api') }}</button>
                &nbsp; &nbsp; &nbsp; 

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
