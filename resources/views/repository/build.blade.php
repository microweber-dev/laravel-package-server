@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-md-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Build Packages</div>
                <div class="card-body">

                    @if ($show_log == '1')
                        <style>
                            .js-package-manager-build-log {
                                color: #d2d2d2;background: #000000; padding: 5px;
                            }
                        </style>
                        <script>
                            $(document).ready(function() {
                                setInterval(function(){
                                    getLog();
                                }, 1000);
                            });
                            function getLog() {
                                $.get("build-packages-output.log")
                                    .done(function(data) {
                                        $('.js-package-manager-build-log').html(data);
                                    });
                            }
                            getLog();
                        </script>
                        <div class="js-package-manager-build-log">
                            Loading..
                        </div>
                    @else
                        <div class="text-center">
                            <a href="{{ route('build-repo-run') }}" class="btn btn-outline-primary"><i class="fa fa-rocket"></i> Run Package Builder</a>
                        </div>
                    @endif
<!--
                    <h3>Run the command on the terminal.</h3>
                    <div style="color: #fcfaff;background:#2922ff; padding: 5px;">{{ base_path() }}/build-packages.sh</div>

                    <br />
                    <a href="{{ route('home') }}" class="btn btn-outline-primary"><i class="fa fa-arrow-left"></i> Back</a>
-->

                </div>
            </div>
        </div>

        @include('sidebar')

    </div>
</div>
@endsection
