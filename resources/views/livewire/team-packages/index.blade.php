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

                        @if($teamPackage->package->version > 0)
                            <div> <span class="badge bg-success">v{{$teamPackage->package->version}}</span></div>
                        @endif

                        @if($teamPackage->package->clone_status != \App\Models\Package::CLONE_STATUS_SUCCESS)
                            <div style="max-height: 100px;max-width: 700px;overflow: scroll" class="text-danger">
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
                        <a class="btn btn-outline-dark" href="{{route('my-packages.show', $teamPackage->package->id)}}">View</a>
                        <a href="{{route('team-packages.edit', $teamPackage->id)}}" class="btn btn-outline-dark">Edit</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $teamPackages->links() }}

    </div>
</div>
