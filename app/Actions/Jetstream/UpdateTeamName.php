<?php

namespace App\Actions\Jetstream;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;

class UpdateTeamName implements UpdatesTeamNames
{
    /**
     * Validate and update the given team's name.
     *
     * @param  mixed  $user
     * @param  mixed  $team
     * @param  array  $input
     * @return void
     */
    public function update($user, $team, array $input)
    {
        Gate::forUser($user)->authorize('update', $team);

        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('teams')->ignore($team->id, 'id')],
        ])->validateWithBag('updateTeamName');

        $host = false;
        $domain = $input['domain'];
        $parse = parse_url($domain);
        if (isset($parse['host'])) {
            $host = $parse['host'];
        }

        $team->forceFill([
            'name' => $input['name'],
            'slug' => $input['slug'],
            'domain' => $host,
            'is_private' => $input['is_private'],
        ])->save();
    }
}
