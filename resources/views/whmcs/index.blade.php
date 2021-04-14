@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Configure WHMCS</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('configure-whmcs-save')}}">


                            <div class="form-group row">
                                <label for="text" class="col-4 col-form-label">WHMCS URL</label>
                                <div class="col-8">
                                    <div class="input-group">
                                        <input id="text" name="text" placeholder="https://whmcs.yourwebsite.com" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="select" class="col-4 col-form-label">WHMSC Auth Type</label>
                                <div class="col-8">
                                    <select id="select" name="select" class="custom-select" aria-describedby="selectHelpBlock">
                                        <option value="api">API</option>
                                        <option value="password">Username & Password</option>
                                    </select>
                                    <span id="selectHelpBlock" class="form-text text-muted">This is how we will make connection with your WHMCS</span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="whmcs_api_identifier" class="col-4 col-form-label">WHMCS API IDENTIFIER</label>
                                <div class="col-8">
                                    <input id="whmcs_api_identifier" name="whmcs_api_identifier" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="whmcs_api_secret" class="col-4 col-form-label">WHMCS API SECRET</label>
                                <div class="col-8">
                                    <input id="whmcs_api_secret" name="whmcs_api_secret" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="username" class="col-4 col-form-label">Username</label>
                                <div class="col-8">
                                    <input id="username" name="username" type="text" class="form-control">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password" class="col-4 col-form-label">Password</label>
                                <div class="col-8">
                                    <input id="password" name="password" type="text" class="form-control">
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
