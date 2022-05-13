<div class="row">

    <x-slot name="header">
        <div class="font-weight-bold">
            {{ $this->team->name }} {{ __('Packages') }}
        </div>
        @if(!empty($this->team->slug))
            <div class="mt-2">
                <a href="{{route('packages.team.packages.json', $this->team->slug)}}" target="_blank">{{route('packages.team.packages.json', $this->team->slug)}}</a>
            </div>
        @endif
    </x-slot>

    @if (session()->has('message'))
        <div class="col-md-12">
            <div id="js-alert-message" class="alert alert-success align-items-center" role="alert">
                <b>
                    {{ session('message') }}
                </b>
            </div>
            <script>
                setTimeout(function () {
                    document.getElementById('js-alert-message').style.display = 'none';
                }, 3500);
            </script>
        </div>
    @endif

    <div class="col-md-12 text-right pb-2">
        @if($show_add_team_package_form)
        <div class="">
            @if($add_from_existing)
                <div class="mb-3 has-validation">
                    <label for="inputRepository" class="form-label">Existing repositories</label>
                    <select class="form-control" wire:model="add_existing_repository_id">
                        <option>Select repositories..</option>
                        @foreach($existing_packages_grouped as $group_name=>$existing_packages)
                            <optgroup label="{{$group_name}}">
                                @foreach($existing_packages as $package)
                                <option value="{{$package['id']}}">
                                     {{$package['name']}}
                                </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <div id="repositoryHelp" class="form-text">Select the git repository from existing packages</div>
                    <div class="invalid-feedback">
                        @error('repository_url')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            @else
            <div class="mb-3 has-validation">
                <label for="inputRepository" class="form-label">Repository Url</label>
                <input type="text" wire:model="add_existing_repository_url" class="form-control @error('add_existing_repository_url') is-invalid @enderror" id="inputRepository" aria-describedby="repositoryHelp">
                <div id="repositoryHelp" class="form-text">The url of git repository</div>
                <div class="invalid-feedback">
                    @error('repository_url')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            @endif
            <div class="w-md-75 mb-3">
                <div class="form-group">
                    <div class="form-check">
                        <x-jet-checkbox wire:model="add_from_existing" id="add_from_existing" value="1" />
                        <label class="form-check-label" for="add_from_existing">
                            {{ __('Add from existing') }}
                        </label>
                    </div>

                </div>
            </div>

        </div>
        @endif

        <div>
            @if($show_add_team_package_form)
                <button type="button" wire:click="hideAddTeamPackageForm" class="btn btn-outline-danger btn-sm">Cancel</button>
                <button type="button" wire:click="addTeamPackage" class="btn btn-outline-success btn-sm">Save package</button>
            @else
                <button type="button" wire:click="showAddTeamPackageForm" class="btn btn-outline-dark btn-sm">Add team package</button>
                <button type="button" wire:click="reorderPackagesByNew" class="btn btn-outline-dark btn-sm">Reorder packages by new</button>
            @endif
        </div>

    </div>

    <div class="col-md-12">

        <hr />
        <livewire:team-packages-table theme="bootstrap-5" />

    </div>
</div>
