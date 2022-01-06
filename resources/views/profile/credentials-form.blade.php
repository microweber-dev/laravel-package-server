<x-jet-action-section>
    <x-slot name="title">
        {{ __('Credentials') }}
    </x-slot>

    <x-slot name="description">
        {{ __('These credentials let Private Packagist access your private repositories, update the package data and build zip archives. Once you create one here you can select it when adding packages.') }}

    </x-slot>

    <x-slot name="content">

        <div><h4>{{ __('Add New Credentials') }}</h4></div>
        <div class="w-md-75">
            <div class="form-group">
                <x-jet-label for="credential_type" value="{{ __('Authentication type') }}" />
                <select id="credential_type" wire:model="credentialType" class="form-control">
                  {{--  <option value="http-basic">HTTP Basic (Username/Password)</option>--}}
                    <option value="github-oauth">GitHub API Token</option>
                    <option value="gitlab-token">GitLab API Token</option>
                  {{--  <option value="bitbucket-app-pw">Bitbucket App Password</option>
                    <option value="bitbucket-api">Bitbucket API Key</option>
                    <option value="bearer-token">Bearer Token</option>
                    <option value="ssh-key">SSH Key</option>--}}
                </select>
            </div>
        </div>

        <div class="w-md-75 mt-3">
            <div class="form-group">
                <x-jet-label for="domain" value="{{ __('Domain name for which these credentials should be used') }}" />

                <x-jet-input id="domain"
                             type="text"
                             class="{{ $errors->has('domain') ? 'is-invalid' : '' }}"
                             wire:model.defer="domain" />

                <x-jet-input-error for="domain" />
            </div>
        </div>

        <div class="mt-3">

            @if($credentialType == 'github-oauth')
            GITHUB OAUTH
            @endif

            @if($credentialType == 'gitlab-token')
            GITLAB token
            @endif

        </div>
    </x-slot>
</x-jet-action-section>
