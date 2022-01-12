<div class="row">

    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('My Packages') }}
        </h2>
    </x-slot>

    @if ($check_background_job)
        <div wire:poll="backgroundJobStatus">
            <div class="col-md-12">
                <div class="alert alert-success align-items-center" role="alert">
                    <b>
                        Background job is running.
                    </b>
            </div>
        </div>
        </div>
    @endif

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
        <div class="card mb-4">
            <div class="card-body row">
                <div class="col-md-6">
                    <div>
                        <input wire:model="keyword" type="text" class="form-control" placeholder="Search packages...">
                        <small>Microweber Packages is the official microweber cms composer repository. </small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <div class="form-check">
                            <x-jet-checkbox wire:model="shared_packages" id="shared_packages" value="1" />
                            <label class="form-check-label" for="shared_packages">
                                {{ __('Shared packages') }}
                            </label>
                        </div>

                    </div>
                </div>
                <div class="col-md-2 justify-content-center">
                    <a href="{{route('my-packages.add')}}" class="btn btn-outline-dark">
                     Add Package
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <table class="table table-bordered bg-white">
            <thead>
            <tr>
                <th scope="col">Details</th>
                <th scope="col">Repository Url</th>
                <th scope="col">Status</th>
                <th scope="col">Last Update</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($packages as $package)
                <tr>
                    <td>
                        @if(!empty($package->screenshot))
                          <div style="max-height:100px;text-align:center;overflow: hidden;">
                              <img src="{{$package->screenshot()}}" style="width:130px;" />
                          </div>
                        @endif
                    </td>
                    <td>
                        <div><b> {{$package->description}}</b></div>
                        <div> {{$package->repository_url}}</div>

                        @if($package->version > 0)
                           <div> <span class="badge bg-success">v{{$package->version}}</span></div>
                        @endif

                        @if($package->clone_status != \App\Models\Package::CLONE_STATUS_SUCCESS)
                            <div style="max-height: 100px;max-width: 700px;overflow: scroll" class="text-danger">
                                {{$package->clone_log}}
                            </div>
                        @endif
                        <div>Added by: {{$package->owner->name}}</div>
                    </td>
                    <td>
                        @if($package->clone_status == \App\Models\Package::CLONE_STATUS_SUCCESS)
                        <span class="badge bg-success">{{$package->clone_status}}</span>
                        @else
                            <span class="badge bg-danger">{{$package->clone_status}}</span>
                        @endif
                    </td>
                    <td>{{$package->updated_at}}</td>
                    <td>
                        <a class="btn btn-outline-dark btn-sm" href="{{route('my-packages.show', $package->id)}}">View</a>
                        <button type="button" class="btn btn-outline-dark btn-sm" wire:click="update({{ $package->id }})" wire:loading.attr="disabled">Update</button>
                        <a href="{{route('my-packages.edit', $package->id)}}" class="btn btn-outline-dark btn-sm">Edit</a>
                        <button type="button" class="btn btn-outline-dark btn-sm" wire:click="delete({{ $package->id }})" wire:loading.attr="disabled">Delete</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div>
            {{ $packages->links() }}
        </div>

    </div>
</div>
