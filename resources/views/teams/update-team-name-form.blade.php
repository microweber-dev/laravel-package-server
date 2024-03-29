<x-jet-form-section submit="updateTeamName">
    <x-slot name="title">
        {{ __('Team Name') }}
    </x-slot>

    <x-slot name="description">
        {{ __('The team\'s name and owner information.') }}
    </x-slot>

    <x-slot name="form">
        <x-jet-action-message on="saved">
            {{ __('Saved.') }}
        </x-jet-action-message>

        <!-- Team Owner Information -->
        <div class="mb-4">
            <x-jet-label value="{{ __('Team Owner') }}" />

            <div class="d-flex mt-2">
                <img class="rounded-circle me-2" width="48" src="{{ $team->owner->profile_photo_url }}">
                <div>
                    <div>{{ $team->owner->name }}</div>
                    <div class="text-muted">{{ $team->owner->email }}</div>
                </div>
            </div>
        </div>

        <!-- Team Name -->
        <div class="w-md-75">
            <div class="form-group">
                <x-jet-label for="name" value="{{ __('Team Name') }}" />

                <x-jet-input id="name"
                             type="text"
                             class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                             wire:model.defer="state.name"
                             :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="name" />
            </div>
        </div>

        <!-- Team Slug -->
        <div class="w-md-75 mt-3">
            <div class="form-group">
                <x-jet-label for="slug" value="{{ __('Team Slug') }}" />

                <x-jet-input id="slug"
                             type="text"
                             class="{{ $errors->has('slug') ? 'is-invalid' : '' }}"
                             wire:model.defer="state.slug"
                             :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="slug" />
            </div>
        </div>

        <!-- Team Domain -->
        <div class="w-md-75 mt-3">
            <div class="form-group">
                <x-jet-label for="domain" value="{{ __('Team Domain') }}" />

                <x-jet-input id="domain"
                             type="text"
                             class="{{ $errors->has('domain') ? 'is-invalid' : '' }}"
                             wire:model.defer="state.domain"
                             :disabled="! Gate::check('update', $team)" />

                <x-jet-input-error for="domain" />
            </div>
        </div>

        <!-- Team Private -->
        <div class="w-md-75 mt-3">
            <div class="form-group">
                <x-jet-label for="slug" value="{{ __('Team Private') }}" />

                <div class="form-check">
                    <x-jet-checkbox wire:model.defer="state.is_private" id="is_private" value="1" />
                    <label class="form-check-label" for="is_private">
                        {{ __('Yes') }}
                    </label>
                </div>

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
