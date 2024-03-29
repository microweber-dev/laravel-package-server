<div class="row">

    <x-slot name="header">
        <div class="font-weight-bold">
            @if($package_id)
                {{ __('Edit package') }}
            @else
                {{ __('Add package') }}
            @endif
        </div>
    </x-slot>


    <form wire:submit.prevent="edit">

            @if(Auth::user()->allTeams()->count() > 0)
                <div class="mb-3 has-validation">
                    <label for="selectTeam" class="form-label">Add package to teams</label>

                    @foreach (Auth::user()->allTeams() as $team)
                    <div class="form-check">
                        <input class="form-check-input" id="inputTeam{{ $team->id }}" value="{{ $team->id }}" wire:model="team_ids" type="checkbox">
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

            <div class="w-md-75 mt-4 mb-4">
                <div class="form-group">
                    <x-jet-label for="credential_id" value="{{ __('Which credentials should we use (optional)') }}" />
                    <select id="credential_id" name="credential_id" wire:model.defer="credential_id" class="form-control">
                        <option value="">None</option>
                        @foreach($credentials as $credential)
                        <option value="{{$credential->id}}">{{$credential->description}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @error('team_owner_id') <div class="alert alert-danger">{{ $message }}</div> @enderror
           <div class="w-md-75 mt-4 mb-4">
                <div class="form-group">
                    <x-jet-label for="team_owner_id" value="{{ __('Owner Team') }}" />
                    <select id="team_owner_id" name="team_owner_id" wire:model.defer="team_owner_id" class="form-control">
                        <option value="">None</option>
                        @foreach (Auth::user()->allTeams() as $team)
                        <option value="{{$team->id}}">{{$team->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

                <div class="w-md-75 mt-4">
            @if($package_id)
                <button type="submit" class="btn btn-outline-dark">Save Package</button>
            @else
                <button type="submit" class="btn btn-outline-dark">Submit Package</button>
            @endif
                </div>
        </form>

</div>
