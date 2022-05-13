<div class="row">

    <x-slot name="header">
        <div class="font-weight-bold">
            {{ $this->team->name }}  @if($package_id)
                {{ __('Edit package') }}
            @else
                {{ __('Add package') }}
            @endif
        </div>
        @if(!empty($this->team->slug))
            <div class="mt-2">
                <a href="{{route('packages.team.packages.json', $this->team->slug)}}" target="_blank">{{route('packages.team.packages.json', $this->team->slug)}}</a>
            </div>
        @endif
    </x-slot>


    <form wire:submit.prevent="edit">

        <div class="row">

            <div class="col-md-8">
                <div class="bg-body p-3">
                    <h3>{{ $this->team->name }} Package</h3>
                    <b class="text-uppercase">{{$package->name}}</b>
                    <br />
                    <br />
              <img src="{{$package->screenshot()}}" style="max-width: 100%" />
             </div>
            </div>

            <div class="col-md-4">
            <div class="bg-body p-3">

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

                    <div class="bg-info mb-3 p-3">
            <h5>Generate Buy link from:</h5>
            <select class="form-control" name="buy_url_from" wire:model="buy_url_from">
                <option value="license">WHMCS Plan\License</option>
                <option value="custom">Custom</option>
            </select>

            @if ($this->buy_url_from == 'custom')
                <br />
            <div class="mb-3 has-validation">
                <label for="inputBuyUrl" class="form-label">Buy Link</label>
                <input type="text" value="{{$this->buy_url}}" name="buy_url" wire:model.defer="buy_url" class="form-control @error('buy_url') is-invalid @enderror" id="inputBuyUrl" aria-describedby="buyUrlHelp">
                <div id="buyUrlHelp" class="form-text">The url of package purchase</div>
                <div class="invalid-feedback">
                    @error('buy_url')
                    {{ $message }}
                    @enderror
                </div>
            </div>
            @else
                <br />
                <b>Required WHMCS Plan\License for Install Package</b>
                <p>Select whmcs plan\license to generate buy link on premium packages</p>

                <select class="form-control" name="whmcs_primary_product_id" wire:model.defer="whmcs_primary_product_id">
                    @if(!empty($this->whmcs_product_types))
                        @foreach($this->whmcs_product_types as $whmcs_product_type_name=>$whmcs_product_type)
                            <optgroup label="{{ucfirst($whmcs_product_type_name)}}">
                                @foreach($whmcs_product_type as $whmcs_product)
                                    <option value="{{ $whmcs_product['pid']}}">{{$whmcs_product['name']}}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    @endif
                </select>
            @endif

        <br />

            </div>

                    <div class="bg-info mb-3 p-3">
            <h5>
                Repository Access
            </h5>
            <p>Select the following WHMCS plans\license to access this repository</p>

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

            </div>
            @endif


              <button type="submit" class="btn btn-outline-dark">Save Package</button>
            </div>
            </div>
            </div>
        </form>
</div>
