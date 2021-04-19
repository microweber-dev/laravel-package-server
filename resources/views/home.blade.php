@extends('layouts.app')

@section('content')

    <style>
        .repository-screenshot {
            max-height:200px;
            clear: both;
            overflow:hidden;
        }
    </style>

<div class="container">
    <div class="row">

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="col-md-8">
        <div class="row">
            @foreach($repositories as $repository)
            <div class="col-md-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <a href="edit-repo?url={{ $repository['url'] }}">{{ $repository['url'] }}</a>

                        @if(isset($repository['build_info']))
                        <label class="badge badge-success">Version: {{$repository['build_info']['version']}}</label>
                        @endif
                    </div>
                    <div class="card-body">

                        @if(isset($repository['build_info']))

                           <h5> {{$repository['build_info']['description']}} </h5>

                        @if(isset($repository['build_info']['extra']['_meta']['screenshot']))
                            <div class="repository-screenshot">
                            <img src="{{ $repository['build_info']['extra']['_meta']['screenshot']}}" class="img-thumbnail" />
                            </div>
                        @endif

                    <span class="text-muted">Type</span><span class="text-primary">{{$repository['build_info']['type']}}</span> <br />
                            <span class="text-muted">Repository Name</span> <span class="text-primary">{{$repository['build_info']['name']}}</span>
                    <br />
                    @else
                        <div class="alert alert-info">No build info</div>
                    @endif

                    @if(isset($repository['whmcs_product_ids']))
                        <span style="color: #666">WHMCS Product Ids:  {{ $repository['whmcs_product_ids'] }}</span>
                    @endif

                    <a href="edit-repo?url={{ $repository['url']  }}" class="btn btn-sm btn-outline-success"><i class="fa fa-pen"></i> Edit</a>
                    <a href="delete-repo?url={{ $repository['url']  }}" class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i> Delete</a>

                    </div>
                </div>
            </div>
            @endforeach
        </div>
        </div>

        @include('sidebar')

    </div>
</div>
@endsection
