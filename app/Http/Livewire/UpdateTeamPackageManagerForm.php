<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;
use Livewire\Component;

class UpdateTeamPackageManagerForm extends Component
{
    /**
     * The team instance.
     *
     * @var mixed
     */
    public $team;

    /**
     * The component's state.
     *
     * @var array
     */
    public $state = [];

    public $settings = [];

    /**
     * Mount the component.
     *
     * @param  mixed  $team
     * @return void
     */
    public function mount($team)
    {
        $this->team = $team;

        $this->state = $team->withoutRelations()->toArray();

        $this->settings = $team->settings()->get();
    }

    public function updateTeamPackageManager()
    {
        $this->resetErrorBag();

        if (isset($this->settings['package_manager_templates_demo_domain'])) {
            $demoDomainParsed = parse_url($this->settings['package_manager_templates_demo_domain']);
            if (isset($demoDomainParsed['host'])) {
            $this->settings['package_manager_templates_demo_domain'] = $demoDomainParsed['host'];
            }
        }

        $this->team->settings()->apply((array)$this->settings);

        $this->emit('saved');
        $this->emit('refresh-navigation-menu');
    }

    public function regenerateToken()
    {
        $this->team->generateToken();
    }


    /**
     * Get the current user of the application.
     *
     * @return mixed
     */
    public function getUserProperty()
    {
        return Auth::user();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('teams.update-team-package-manager-form');
    }
}
