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

            @include('sidebar')

    </div>
</div>
@endsection
