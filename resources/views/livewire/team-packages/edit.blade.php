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

              <button type="submit" class="btn btn-outline-dark">Save Package</button>
        </form>

</div>
