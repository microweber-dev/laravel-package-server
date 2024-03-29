<x-jet-action-section>
    <x-slot name="title">
        {{ __('Credentials') }}
    </x-slot>

    <x-slot name="description">
        {{ __('These credentials let Microweber Packages access your private repositories, update the package data and build zip archives. Once you create one here you can select it when adding packages.') }}

    </x-slot>

    <x-slot name="content">

        @if($showCredentialsForm)
            <button wire:click="hideCredentialsForm()" class="btn btn-dark btn-sm">Close credentials form</button>
        @else
            <button wire:click="showCredentialsForm()" class="btn btn-dark btn-sm">Add New Credentials</button>
        @endif

        @if($showCredentialsForm)
            <div>

                @if ($credentialEdit)
                    <div class="mt-3"><h4>{{ __('Edit credential') }}</h4></div>
                @else
                 <div class="mt-3"><h4>{{ __('Add New Credentials') }}</h4></div>
                @endif

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
                    @if ($credentialEdit)
                        <button type="button" wire:click="save({{$credentialId}})" class="btn btn-outline-dark">Edit</button>
                    @else
                        <button type="button" wire:click="save" class="btn btn-outline-dark">Create</button>
                    @endif
                </div>
                @endif
            </div>

            <div class="p-3">
                <table class="table table-borderless bg-white">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th scope="col">Domain</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($credentials as $credential)
                        <tr  @if($confirmingDeleteId === $credential->id) class="table-danger" @endif>
                            <td>{{$credential->description}}</td>
                            <td>{{$credential->domain}}</td>
                            <td>{{$credential->updated_at}}</td>
                            <td>
                                <button type="button" class="btn btn-outline-dark btn-sm" wire:click="edit({{ $credential->id }})">Edit</button>

                                @if($confirmingDeleteId === $credential->id)
                                    <button wire:click="delete({{ $credential->id }})"
                                            class="btn btn-outline-dark btn-sm">Sure?</button>
                                @else
                                    <button wire:click="confirmDelete({{ $credential->id }})"
                                            class="btn btn-outline-danger btn-sm">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </x-slot>
</x-jet-action-section>
