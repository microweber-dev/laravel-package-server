<div class="row">

    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            @if($package_id)
                {{ __('Edit team package') }}
            @else
                {{ __('Add team package') }}
            @endif
        </h2>
    </x-slot>


    <form wire:submit.prevent="edit">

            <div class="mb-3 has-validation">
                <label for="inputRepository" class="form-label">Repository Url</label>
                <input type="text" @if($package_id) disabled="disabled" @endif value="{{$repository_url}}" wire:model="repository_url" class="form-control @error('repository_url') is-invalid @enderror" id="inputRepository" aria-describedby="repositoryHelp">
                <div id="repositoryHelp" class="form-text">The url of git repository</div>
                <div class="invalid-feedback">
                    @error('repository_url')
                    {{ $message }}
                    @enderror
                </div>
            </div>

            <div class="w-md-75 mt-3">
                <div class="form-group">
                    <x-jet-label for="is_visible" value="{{ __('Is Visible') }}" />

                    <div class="form-check">
                        <x-jet-checkbox wire:model.defer="is_visible" id="is_visible" value="1" />
                        <label class="form-check-label" for="is_visible">
                            {{ __('Yes') }}
                        </label>
                    </div>

                </div>
            </div>

            <div class="w-md-75 mt-3">
                <div class="form-group">
                    <x-jet-label for="is_paid" value="{{ __('Is Paid') }}" />

                    <div class="form-check">
                        <x-jet-checkbox wire:model="is_paid" id="is_paid" value="1" />
                        <label class="form-check-label" for="is_paid">
                            {{ __('Yes') }}
                        </label>
                    </div>

                </div>
            </div>

        @if($this->is_paid == 1)

            <hr />
            <h5>Primary License</h5>
            <p>Select primary license to generate buy link on premium packages</p>

             <select class="form-control" name="whmcs_primary_product_id" wire:model.defer="whmcs_primary_product_id">
            @if(!empty($this->whmcs_product_types))
                @foreach($this->whmcs_product_types as $whmcs_product_type_name=>$whmcs_product_type)

                    @foreach($whmcs_product_type as $whmcs_product)
                        <option value="{{ $whmcs_product['pid']}}">{{$whmcs_product['name']}}</option>
                    @endforeach

                @endforeach
            @endif
             </select> 

        <br />


            <hr />
            <h5>
                Purchased Plan Requirements To Access This Repository
            </h5>
            <p>Select the following WHMCS plans to access this repository</p>

            @if(!empty($this->whmcs_product_types))
                @foreach($this->whmcs_product_types as $whmcs_product_type_name=>$whmcs_product_type)
                    <b>{{ucfirst($whmcs_product_type_name)}}</b> <br />
                    @foreach($whmcs_product_type as $whmcs_product)

                        <div class="form-check">
                            <input class="form-check-input" id="inputWhmcsProduct{{ $whmcs_product['pid']}}" value="{{ $whmcs_product['pid'] }}" wire:model.defer="whmcs_product_ids" type="checkbox">
                            <label class="form-check-label" for="inputWhmcsProduct{{ $whmcs_product['pid'] }}">
                                {{$whmcs_product['name']}}
                            </label>
                        </div>

                    @endforeach
                @endforeach
            @endif

            @endif


              <button type="submit" class="btn btn-outline-dark">Save Package</button>
        </form>

</div>
