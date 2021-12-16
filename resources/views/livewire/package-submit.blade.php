<form wire:submit.prevent="submitPackage">

    <div class="form-control">
    <input type="text" class="form-control" wire:model="repositoryUrl">
    @error('repository_url')
    <span class="error">{{ $message }}</span>
    @enderror
    </div>


    <button type="submit" class="btn btn-primary">Submit Package</button>

</form>
