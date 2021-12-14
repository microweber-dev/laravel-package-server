@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Sync Repositories from Git</div>

                    <div class="card-body">
                        <form class="form-horizontal" method="post" action="{{route('gitsync.save')}}">
                        @csrf

                        @if(!empty($projects))
                        @foreach($projects as $project)

                            <div class="form-group row" style="background: #f2d9778f;padding: 15px;margin-bottom: 5px;border-radius: 4px;">
                                <label for="import_url" class="col-4 col-form-label"> {{$project['name_with_namespace']}}</label>
                                <div class="col-8">
                                    <select id="import_url" name="repositories[{{$project['id']}}][url]" class="custom-select">
                                        <option value="{{$project['ssh_url_to_repo']}}">SSH</option>
                                        <option value="{{$project['http_url_to_repo']}}">HTTPS</option>
                                    </select>
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        <input name="repositories[{{$project['id']}}][import]" id="import_{{$project['id']}}" type="checkbox" class="custom-control-input" value="1">
                                        <label for="import_{{$project['id']}}" class="custom-control-label">Import</label>
                                    </div>
                                </div>
                            </div>

                        @endforeach

                            <div class="form-group row">
                                <div class="offset-4 col-8">
                                    <button name="save" type="submit" class="btn btn-outline-success">Start Importing</button>
                                </div>
                            </div>

                        @else
                            No repositories found
                        @endif

                        </form>
                    </div>
                </div>
            </div>
            @include('sidebar')
        </div>
    </div>
@endsection
