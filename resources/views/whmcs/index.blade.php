@extends('layouts.app')

@section('content')

    <script src="//code.jquery.com/jquery-1.11.3.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('.js-whmcs-auth-type').change(function() {
                var authType = $(this).val();
                toggleAuthType(authType);
            });
            toggleAuthType('api');
        });
        function toggleAuthType(type) {
            if (type == 'api') {
                $('.js-authbox-api').slideDown();
                $('.js-authbox-username-password').hide();
            } else {
                $('.js-authbox-api').hide();
                $('.js-authbox-username-password').slideDown();
            }
        }
    </script>

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
                                    <select id="select" name="select" class="custom-select js-whmcs-auth-type" aria-describedby="selectHelpBlock">
                                        <option value="api">API</option>
                                        <option value="password">Username & Password</option>
                                    </select>
                                    <span id="selectHelpBlock" class="form-text text-muted">This is how we will make connection with your WHMCS</span>
                                </div>
                            </div>
                            <div class="js-authbox-api" style="display:none">
                                <div class="form-group row">
                                    <label for="api_identifier" class="col-4 col-form-label">WHMCS Api Identifier</label>
                                    <div class="col-8">
                                        <input id="api_identifier" name="api_identifier" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="api_secret" class="col-4 col-form-label">WHMCS Api Secret</label>
                                    <div class="col-8">
                                        <input id="api_secret" name="api_secret" type="text" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="js-authbox-username-password" style="display:none">
                                <div class="form-group row">
                                    <label for="username" class="col-4 col-form-label">WHMSC Username</label>
                                    <div class="col-8">
                                        <input id="username" name="username" type="text" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="password" class="col-4 col-form-label">WHMSC Password</label>
                                    <div class="col-8">
                                        <input id="password" name="password" type="text" class="form-control">
                                    </div>
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
