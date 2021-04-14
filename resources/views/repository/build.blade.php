@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Build Packages</div>
                <div class="card-body">


                    <h3>Run the command on the terminal.</h3>
                    <div style="color: #fcfaff;background:#2922ff; padding: 5px;">{{ base_path() }}/build-packages.sh</div>

                    <br />
                    <a href="{{ route('home') }}" class="btn btn-outline-primary"><i class="fa fa-arrow-left"></i> Back</a>

                </div>
            </div>
        </div>

        @include('sidebar')

    </div>
</div>
@endsection
