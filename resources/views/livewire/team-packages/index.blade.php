<div class="row">

    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Team Packages') }}
        </h2>
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
            @endif
        </div>

    </div>

    <div class="col-md-12">
        <table class="table table-bordered bg-white">
            <thead>
            <tr>
                <th scope="col">Screenshot</th>
                <th scope="col">Details</th>
                <th scope="col">Is visible</th>
                <th scope="col">Is paid</th>
                <th scope="col">Owner</th>
                <th scope="col">Last Update</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($teamPackages as $teamPackage)
                <tr>
                    <td>
                        @if(!empty($teamPackage->package->screenshot))
                            <div style="max-height:100px;text-align:center;overflow: hidden;">
                                <img src="{{$teamPackage->package->screenshot()}}" style="width:130px;" />
                            </div>
                        @endif
                    </td>
                    <td>
                        <div><b> {{$teamPackage->package->description}}</b></div>
                        <div> {{$teamPackage->package->repository_url}}</div>
                        @if($teamPackage->package)
                        <div> <pre>{{$teamPackage->package->name}}</pre></div>
                        @endif
                        @if($teamPackage->package->version > 0)
                            <div> <span class="badge bg-success">v{{$teamPackage->package->version}}</span></div>
                        @endif

                        @if($teamPackage->package->clone_status != \App\Models\Package::CLONE_STATUS_SUCCESS)
                            <div style="max-height: 100px;max-width: 700px;overflow: hidden" class="text-danger">
                                {{$teamPackage->package->clone_log}}
                            </div>
                        @endif
                    </td>

                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input" wire:model="is_visible.{{$teamPackage->id}}" value="1" type="checkbox" id="flexSwitchCheckIsVisible">
                            <label class="form-check-label" for="flexSwitchCheckIsVisible">
                                @if($teamPackage->is_visible == 1)
                                    Yes
                                @else
                                    No
                                @endif
                            </label>
                        </div>
                    </td>

                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input" wire:model="is_paid.{{$teamPackage->id}}" value="1" type="checkbox" id="flexSwitchCheckIsPaid">
                            <label class="form-check-label" for="flexSwitchCheckIsPaid">
                                @if($teamPackage->is_paid == 1)
                                    Yes
                                @else
                                    No
                                @endif
                            </label>
                        </div>
                    </td>
                    <td>
                        {{$teamPackage->package->owner->name}}
                    </td>

                     <td>{{$teamPackage->updated_at}}</td>
                    <td>
                        <a class="btn btn-outline-dark btn-sm" href="{{route('my-packages.show', $teamPackage->package->id)}}">View</a>
                        <button type="button" class="btn btn-outline-dark btn-sm" wire:click="packageUpdate({{ $teamPackage->package->id }})" wire:loading.attr="disabled">Update</button>
                        <a href="{{route('team-packages.edit', $teamPackage->id)}}" class="btn btn-outline-dark btn-sm">Edit</a>

                        @if($confirming_delete_id === $teamPackage->id)
                            <button wire:click="delete({{ $teamPackage->id }})" class="btn btn-outline-danger btn-sm">Sure?</button>
                        @else
                            <button wire:click="confirmDelete({{ $teamPackage->id }})" class="btn btn-outline-dark btn-sm">Delete</button>
                        @endif

                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $teamPackages->links() }}

    </div>
</div>
