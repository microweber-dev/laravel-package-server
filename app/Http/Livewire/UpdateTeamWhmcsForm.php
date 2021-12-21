<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Laravel\Jetstream\Contracts\UpdatesTeamNames;
use Livewire\Component;

class UpdateTeamWhmcsForm extends Component
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

    public $connection_status = false;

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

        $this->getConnectionStatus();
    }

    public function updateTeamWhmcs()
    {
        $this->resetErrorBag();

        $this->team->settings()->apply((array)$this->settings);

        $this->emit('saved');
        $this->emit('refresh-navigation-menu');
    }

    public function getConnectionStatus()
    {
        try {
            $checkConnection = \Whmcs::GetProducts();
        } catch (\Exception $e) {
            $this->connection_status = false;
            return;
        }

        if (empty($checkConnection)) {
            $this->connection_status = false;
            return;
        }

        if (isset($checkConnection['result']) && $checkConnection['result'] == 'error') {
            $this->connection_status = false;
            return;
        }

        $this->connection_status = true;
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
        return view('teams.update-team-whmcs-form');
    }
}
