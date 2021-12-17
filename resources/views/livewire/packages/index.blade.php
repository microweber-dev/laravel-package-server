<div class="row">


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
                <div class="col-md-4"></div>
                <div class="col-md-2 justify-content-center">
                    <button wire:click="create()" class="btn btn-outline-dark">
                     Add Package
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if($is_modal_open)
        @include('livewire.packages.create')
    @endif

    <div class="col-md-12">
        <table class="table table-bordered bg-white">
            <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Repository Url</th>
                <th scope="col">Status</th>
                <th scope="col">Last Update</th>
                <th scope="col">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($packages as $package)
                <tr>
                    <td>{{$package->name}}</td>
                    <td>{{$package->repository_url}}</td>

                    <td>
                        @if($package->clone_status == \App\Models\Package::CLONE_STATUS_SUCCESS)
                        <span class="badge bg-success">{{$package->clone_status}}</span>
                        @else
                            <span class="badge bg-danger">{{$package->clone_status}}</span>
                        @endif
                    </td>
                    <td>{{$package->updated_at}}</td>
                    <td>
                        <a class="btn btn-outline-dark" href="{{$package->id}}">View</a>
                        <button type="button" class="btn btn-outline-dark" wire:click="update({{ $package->id }})" wire:loading.attr="disabled">Update</button>
                        <button type="button" class="btn btn-outline-dark" wire:click="delete({{ $package->id }})" wire:loading.attr="disabled">Delete</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $packages->links() }}

    </div>
</div>
