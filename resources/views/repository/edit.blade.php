@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-md-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Manage Repository</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form class="form-horizontal" method="post">

                                @csrf

                                <!-- Select Basic -->
                                <div class="form-group">
                                    <label class="control-label" for="selectbasic">Repository Type</label>
                                    <select id="selectbasic" name="type" required="required" class="form-control">
                                        <option value="vcs">VCS</option>
                                    </select>
                                </div>

                                <!-- Text input-->
                                <div class="form-group">
                                    <label class="control-label" for="url">Url</label>
                                        <input id="url" name="url" value="{{ $url  }}" @if(!empty($url)) disabled="disabled" @endif type="text"  required="required"  placeholder="Repository url.." class="form-control input-md">
                                        <i class="help-block">Pate your repository URL.</i>
                                </div>

<!--                                &lt;!&ndash; Textarea &ndash;&gt;
                                <div class="form-group">
                                    <label class="control-label" for="textarea">WHMCS Product Ids</label>
                                    <textarea class="form-control" id="textarea" name="whmcs_product_ids">{{ $whmcs_product_ids  }}</textarea>
                                    <i class="help-block">Fill the product ids eperated with coma.</i>
                                </div>-->

                                    <hr />
                                    <h5>
                                        Purchased Plan Requirements To Access This Repository
                                    </h5>
                                    <p>Select the following WHMCS plans to access this repository</p>

                                    @if(!empty($whmcs_products_types))
                                        @foreach($whmcs_products_types as $whmcs_product_type_name=>$whmcs_product_type)
                                            <b>{{ucfirst($whmcs_product_type_name)}}</b> <br />
                                            @foreach($whmcs_product_type as $whmcs_product)
                                               <label><input type="checkbox" name="whmcs_product" value="" /> {{$whmcs_product['name']}}  </label> <br />
                                            @endforeach
                                        @endforeach
                                    @endif

                                <!-- Button -->
                                <div class="form-group">

                                    <a href="{{ route('home') }}" class="btn btn-outline-primary"><i class="fa fa-arrow-left"></i> Back</a>

                                    <button id="save" name="save" class="btn btn-success"><i class="fa fa-save"></i> Save repository</button>

                                    <a href="delete-repo?url={{ $url  }}" class="btn btn-outline-danger" style="float:right"><i class="fa fa-times"></i> Delete repository</a>

                                </div>

                        </form>


                    </div>
                </div>
            </div>
            @include('sidebar')
        </div>
    </div>
@endsection
