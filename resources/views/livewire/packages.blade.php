<div class="row">

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                @livewire('search-repositories')
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">


            </div>
        </div>
    </div>

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
                <a class="btn btn-outline-dark" href="{{$package->id}}">Update</a>
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
