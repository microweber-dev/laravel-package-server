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

            <div>

                WHMCS PRODUCT IDS .... <br />
            </div>

            @endif


              <button type="submit" class="btn btn-outline-dark">Save Package</button>
        </form>

</div>
