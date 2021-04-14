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
                    <div class="table-responsive">

                        <table class="table">
                        @foreach($repositories as $repository)


                                <tr>
                                    <td>
                                    <a href="edit-repo?url={{ $repository['url'] }}">{{ $repository['url'] }}</a> <br />
                                    @if(isset($repository['whmcs_product_ids']))
                                        <span style="color: #666">WHMCS Product Ids:  {{ $repository['whmcs_product_ids'] }}</span>
                                    @endif
                                    </td>
                                    <td class="text-right">
                                    <a href="edit-repo?url={{ $repository['url']  }}" class="btn btn-sm btn-outline-success"><i class="fa fa-pen"></i> Edit</a>
                                    <a href="delete-repo?url={{ $repository['url']  }}" class="btn btn-sm btn-outline-danger"><i class="fa fa-times"></i> Delete</a>
                                    </td>
                                </tr>

                        @endforeach
                        </table>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Action</div>

                <div class="card-body">
                    <a href="{{ route('add-repo') }}" class="btn btn-outline-primary btn-block"><i class="fa fa-plus"></i> Add New Repository</a>
                    <a href="{{ route('build-repo') }}" class="btn btn-outline-success btn-block"><i class="fa fa-rocket"></i> Build Packages</a>
                    <a href="{{ route('configure-whmcs') }}" class="btn btn-outline-success btn-block"><i class="fa fa-cogs"></i> Configure WHMCS</a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
