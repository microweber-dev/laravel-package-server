<x-jet-form-section submit="updateTeamPackageManager">
    <x-slot name="title">
        {{ __('Package Manager') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Configure package manager team information') }}
    </x-slot>

    <x-slot name="form">
        <x-jet-action-message on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>


        <div class="w-md-75">
            <div class="form-group">
                <x-jet-label for="package_manager_templates_demo_domain" value="{{ __('Templates Demo Domain') }}" />

                <x-jet-input id="package_manager_templates_demo_domain"
                             type="text"
                             class="{{ $errors->has('package_manager_templates_demo_domain') ? 'is-invalid' : '' }}"
                             wire:model.defer="settings.package_manager_templates_demo_domain"
                             :disabled="! Gate::check('update', $team)" />
                <small>Example: template.yoursite.com</small>

                <x-jet-input-error for="package_manager_templates_demo_domain" />
            </div>
        </div>



        <div class="w-md-75 mt-4">
            <div class="form-group">
                <x-jet-label for="package_manager_name" value="{{ __('Package Manager Name') }}" />

                <x-jet-input id="package_manager_name"
                             type="text"
                             class="{{ $errors->has('package_manager_name') ? 'is-invalid' : '' }}"
                             wire:model.defer="settings.package_manager_name"
                             :disabled="! Gate::check('update', $team)" />

                <small>Example: microweber/packages</small>

                <x-jet-input-error for="package_manager_name" />
            </div>
        </div>

        <div class="w-md-75 mt-4">
            <div class="form-group">
                <x-jet-label for="package_manager_homepage" value="{{ __('Package Manager Homepage') }}" />

                <x-jet-input id="package_manager_homepage"
                             type="text"
                             class="{{ $errors->has('package_manager_homepage') ? 'is-invalid' : '' }}"
                             wire:model.defer="settings.package_manager_homepage"
                             :disabled="! Gate::check('update', $team)" />
                <small>Example: https://packages.microweberapi.com</small>

                <x-jet-input-error for="package_manager_homepage" />
            </div>
        </div>

        @if(!empty($team->slug))
        <div class="mt-4">
        {{ __('Your package manager json url:') }} <br />
          <a href="{{route('packages.team.packages.json', $team->slug)}}" target="_blank">{{route('packages.team.packages.json', $team->slug)}}</a>
        </div>
        @endif

        @if($team->is_private == 1)
        <div class="mt-4">
        {{ __('Your package manager auth credentials:') }}
        </div>
        <div>
                <b>Username: token</b> <br />
                <b>Token: {{$team->token}}</b>
            <br />
            <button type="button" onclick="confirm('{{ __('Are you sure you want to regenerate the token?') }}') || event.stopImmediatePropagation()" wire:click="regenerateToken" class="btn btn-sm btn-outline-dark">{{ __('Regenerate Token') }}</button>
        </div>
        @endif

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
