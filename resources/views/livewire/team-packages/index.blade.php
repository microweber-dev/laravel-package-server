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
                <th scope="col">#</th>
                <th scope="col">Repository Url</th>
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
                        {{$teamPackage->id}}
                    </td>

                    <td>
                        {{$teamPackage->package->repository_url}}
                    </td>

                    <td>
                        @if($teamPackage->is_visible == 1)
                            <p class="badge bg-success">Yes</p>
                        @else
                            <p class="badge bg-danger">No</p>
                        @endif
                    </td>
                    <td>
                        @if($teamPackage->is_paid == 1)
                            <p class="badge bg-success">Yes</p>
                        @else
                            <p class="badge bg-danger">No</p>
                        @endif
                    </td>
                    <td>
                        {{$teamPackage->package->owner->name}}
                    </td>

                    <td>{{$teamPackage->updated_at}}</td>
                    <td>
                        <a class="btn btn-outline-dark" href="{{$teamPackage->id}}">View</a>
                        <a href="{{route('team-packages.edit', $teamPackage->id)}}" class="btn btn-outline-dark">Edit</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $teamPackages->links() }}

    </div>
</div>
