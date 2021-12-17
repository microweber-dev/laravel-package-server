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
                @livewire('package-add')
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
            <td>{{$package->name}}</td>
            <td>{{$package->repository_url}}</td>
            <td>
                @if ($package->is_cloned == 1)
                    <span class="badge bg-success">Cloned</span>
                @else
                    <span class="badge bg-danger">Error</span>
                @endif
            </td>
            <td>{{$package->updated_at}}</td>
            <td><a class="btn btn-outline-dark" href="{{$package->id}}">Update</a></td>
        </tr>
        @endforeach
        </tbody>
    </table>

    {{ $packages->links() }}

</div>
</div>
