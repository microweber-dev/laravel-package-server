<div class="row">

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
                    @if ($package->is_cloned == 1)
                        <td>{{$package->name}}</td>
                        <td>{{$package->repository_url}}</td>
                        <td>
                            <span class="badge bg-success">Cloned</span>
                        </td>
                        <td>{{$package->updated_at}}</td>
                        <td>
                            <a class="btn btn-outline-dark" href="{{$package->id}}">View</a>
                            <button type="button" class="btn btn-outline-dark" wire:click="update({{ $package->id }})" wire:loading.attr="disabled">Update</button>
                            <button type="button" class="btn btn-outline-dark" wire:click="delete({{ $package->id }})" wire:loading.attr="disabled">Delete</button>
                        </td>
                    @else
                        <td colspan="2">{{$package->repository_url}}</td>
                        <td><span class="badge bg-info">Processing...</span></td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $packages->links() }}

    </div>
</div>
