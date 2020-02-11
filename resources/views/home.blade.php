@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Repositories</div>

                <div class="card-body">

                    @foreach($repositories as $repository)

                        <div class="row repobox">
                            <div class="col-md-8">
                                <a href="{{ $repository['url'] }}" target="_blank">{{ $repository['url'] }}</a> <br />
                                @if(isset($repository['whmcs_product_ids']))
                                    <span style="color: #666">WHMCS Product Ids:  {{ $repository['whmcs_product_ids'] }}</span>
                                @endif
                            </div>
                            <div class="col-md-4 text-right">
                            <a href="edit-repo?url={{ $repository['url']  }}" class="btn btn-outline-success"><i class="fa fa-pen"></i> Edit</a>
                            <a href="delete-repo?url={{ $repository['url']  }}" class="btn btn-outline-danger"><i class="fa fa-times"></i> Delete</a>
                             </div>
                         </div>

                    @endforeach

                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Action</div>

                <div class="card-body">
                    <a href="{{ route('add-repo') }}" class="btn btn-success btn-block"><i class="fa fa-plus"></i> Add New Repository</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
