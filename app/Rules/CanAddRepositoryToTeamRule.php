<?php
namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CanAddRepositoryToTeamRule implements Rule
{
    protected $customMessage;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {

        if (!is_array($value)) {
            $value = [];
        }
        
        $value = array_filter($value);
        if (empty($value)) {
            $this->customMessage = 'You must select one team to add this repository';
            return false;
        }

        $teamIdsFound = [];
        foreach ($value as $teamKey=>$teamValue) {
            foreach (auth()->user()->allTeams() as $team) {
                if ($teamValue == $team->id) {
                    if (auth()->user()->hasTeamPermission($team, 'repository:create')) {
                        $teamIdsFound[$teamKey] = $teamValue;
                    }
                }
            }
        }

        $this->customMessage = 'You must be member of this team to add repositories';

        return ($value === $teamIdsFound);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->customMessage;
    }
}
