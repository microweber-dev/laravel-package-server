<div class="row">

    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            @if($package_id)
                {{ __('Edit package') }}
            @else
                {{ __('Add package') }}
            @endif
        </h2>
    </x-slot>


    <form wire:submit.prevent="edit">

            @if(Auth::user()->allTeams()->count() > 0)
                <div class="mb-3 has-validation">
                    <label for="selectTeam" class="form-label">Add package to teams</label>

                    @foreach (Auth::user()->allTeams() as $team)
                    <div class="form-check">
                        <input class="form-check-input" id="inputTeam{{ $team->id }}" value="{{ $team->id }}" wire:model.defer.lazy="team_ids" type="checkbox">
                        <label class="form-check-label" for="inputTeam{{ $team->id }}">
                            {{$team->name}}
                        </label>
                    </div>
                    @endforeach

                    <span class="text-danger">
                        @error('team_ids')
                        {{ $message }}
                        @enderror
                    </span>
                </div>
            @else

            @endif

            <div class="mb-3 has-validation">
                <label for="inputRepository" class="form-label">Repository Url</label>
                <input type="text" @if($package_id) disabled="disabled" @endif value="{{$repository_url}}" wire:model="repository_url" class="form-control @error('repository_url') is-invalid @enderror" id="inputRepository" aria-describedby="repositoryHelp">
                <div id="repositoryHelp" class="form-text">Enter the url of your git repository</div>
                <div class="invalid-feedback">
                    @error('repository_url')
                    {{ $message }}
                    @enderror
                </div>
            </div>

                @if($package_id)
                    <button type="submit" class="btn btn-outline-dark">Save Package</button>
                @else
                    <button type="submit" class="btn btn-outline-dark">Submit Package</button>
                @endif
        </form>

</div>
