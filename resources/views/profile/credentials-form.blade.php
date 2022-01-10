<x-jet-action-section>
    <x-slot name="title">
        {{ __('Credentials') }}
    </x-slot>

    <x-slot name="description">
        {{ __('These credentials let Microweber Packages access your private repositories, update the package data and build zip archives. Once you create one here you can select it when adding packages.') }}

    </x-slot>

    <x-slot name="content">

        <button onclick="Livewire.emit('openModal', 'hello-world')">Open Modal</button>

        <div><h4>{{ __('Add New Credentials') }}</h4></div>

        <div class="w-md-75">
            <div class="form-group">
                <x-jet-label for="description" value="{{ __('Description') }}"/>

                <x-jet-input id="description"
                             type="text"
                             class="{{ $errors->has('description') ? 'is-invalid' : '' }}"
                             wire:model.defer="description"/>

                <x-jet-input-error for="description"/>
            </div>
        </div>

        <div class="w-md-75 mt-3">
            <div class="form-group">
                <x-jet-label for="authentication_type" value="{{ __('Authentication type') }}"/>
                <select id="authentication_type" wire:model="authenticationType" class="form-control">
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
                <x-jet-label for="domain" value="{{ __('Domain name for which these credentials should be used') }}"/>

                <x-jet-input id="domain"
                             type="text"
                             class="{{ $errors->has('domain') ? 'is-invalid' : '' }}"
                             wire:model.defer="domain"
                />

                <x-jet-input-error for="domain"/>
            </div>
        </div>


        @if($authenticationType == 'github-oauth' || $authenticationType == 'gitlab-token')
            <div class="w-md-75 mt-3">
                <div class="form-group">
                    <x-jet-label for="accessToken" value="{{ __('Access Token') }}"/>

                    <x-jet-input id="accessToken"
                                 type="text"
                                 class="{{ $errors->has('accessToken') ? 'is-invalid' : '' }}"
                                 wire:model.defer="authenticationData.accessToken"/>

                    <x-jet-input-error for="accessToken"/>
                </div>
            </div>
        @endif


        @if($authenticationType == 'github-oauth')
            <div class="w-md-75 mt-3">
                <p class="alert alert-primary mt-3">Head to
                    <a target="_blank"
                       href="https://github.com/settings/tokens/new?scopes=repo,read:user,admin:repo_hook,admin:org_hook&amp;description=Private+Microweber+Access
">https://<span data-github-domain="">github.com</span>/settings/tokens/new?scopes=repo,read:user,admin:repo_hook,admin:org_hook&amp;description=Private+Microweber+Access
                    </a> to create an API token.
                </p>
            </div>
        @endif

        @if($authenticationType == 'gitlab-token')
            <div class="w-md-75 mt-3">
                <p class="alert alert-primary mt-3">Head to <a target="_blank"
                                                               href="https://gitlab.com/-/profile/personal_access_tokens">https://<span
                            data-gitlab-domain="">gitlab.com</span>/-/profile/personal_access_tokens</a> and select the
                    scopes <code>api</code> and <code>read_user</code>.</p>
            </div>
        @endif


        <div class="d-flex align-items-baseline">
            <button type="button" wire:click="create" class="btn btn-outline-dark">Create</button>
        </div>

        </div>
    </x-slot>
</x-jet-action-section>
