<form wire:submit.prevent="submitPackage">

    <div class="input-group has-validation mb-3">
    <input type="text" class="form-control  @error('repository_url') is-invalid @enderror" wire:model="repository_url">


        <div class="invalid-feedback">
            @error('repository_url')
            {{ $message }}
            @enderror
        </div>

        <div class="input-group-append">
            <button type="submit" class="btn btn-outline-dark">Submit Package</button>
        </div>

    </div>

</form>
