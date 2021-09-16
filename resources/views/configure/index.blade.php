@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Configure package manager</div>
                    <div class="card-body">

                        <form method="POST" action="{{ route('configure-save')}}">
                            @csrf

                            <div class="form-group row">
                                <label for="whmcs_url" class="col-4 col-form-label">Package Manager Name</label>
                                <div class="col-8">
                                    <div class="input-group">
                                        <input id="whmcs_url" name="package_manager_name" value="{{ $package_manager_name }}" placeholder="MyPackageMangaer" type="text" class="form-control">
                                    </div>
                                    <small>Example: microweber/packages</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="whmcs_url" class="col-4 col-form-label">Package Manager Homepage</label>
                                <div class="col-8">
                                    <div class="input-group">
                                        <input id="whmcs_url" name="package_manager_homepage" value="{{ $package_manager_homepage }}" placeholder="https://packages.yourwebsite.com" type="text" class="form-control">
                                    </div>
                                    <small>Example: https://packages.microweberapi.com</small>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="offset-4 col-8">
                                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Settings</button>
                                </div>
                            </div>


                        </form>
                    </div>
                </div>
            </div>
            @include('sidebar')
        </div>
    </div>
@endsection
