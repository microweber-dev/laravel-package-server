<div class="row">

    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Add package') }}
        </h2>
    </x-slot>

    <form wire:submit.prevent="store">

            @if(Auth::user()->allTeams()->count() > 0)
                <div class="mb-3 has-validation">
                    <label for="selectTeam" class="form-label">Select Teams</label>

                    @foreach (Auth::user()->allTeams() as $team)
                    <div class="form-check">
                        <input class="form-check-input" wire:model="team_ids.{{ $team->id }}" type="checkbox" id="flexCheckTeam{{$team->id}}">
                        <label class="form-check-label" for="flexCheckTeam{{$team->id}}">
                            {{$team->name}}
                        </label>
                    </div>
                    @endforeach

                    <div class="invalid-feedback">
                        @error('team_ids')
                        {{ $message }}
                        @enderror
                    </div>
                </div>
            @else

            @endif

            <div class="mb-3 has-validation">
                <label for="inputRepository" class="form-label">Repository Url</label>
                <input type="text" wire:model="repository_url" class="form-control @error('repository_url') is-invalid @enderror" id="inputRepository" aria-describedby="repositoryHelp">
                <div id="repositoryHelp" class="form-text">Enter the url of your git repository</div>
                <div class="invalid-feedback">
                    @error('repository_url')
                    {{ $message }}
                    @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-outline-dark">Submit Package</button>
        </form>

</div>
