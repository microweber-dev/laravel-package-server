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
                               max-height:500px;
                                overflow-x:hidden;
                                overflow-y:scroll;
                            }
                        </style>
                        <script>
                            $(document).ready(function() {
                                setInterval(function(){
                                    getLog();
                                }, 2000);
                            });
                            newLog = '';
                            oldLog = '';
                            function getLog() {
                                $.get("build-packages-output.log")
                                    .done(function(data) {
                                        newLog = data;
                                        newLog = data;
                                    });
                                if (oldLog != newLog) {
                                    oldLog = newLog;
                                    $('.js-package-manager-build-log').html('<pre>' + newLog + '</pre>');
                                    $(".js-package-manager-build-log").animate({scrollTop: $('.js-package-manager-build-log').prop("scrollHeight")}, 1000);
                                }
                            }
                            getLog();
                        </script>
                        <b>Process Log</b>
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
