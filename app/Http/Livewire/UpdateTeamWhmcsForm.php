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
            return ['error'=> $e->getMessage()];
        }

        if (empty($checkConnection)) {
            return ['error'=>'Something went wrong. Can\'t connect to the WHMCS.'];
        }

        if (isset($checkConnection['result']) && $checkConnection['result'] == 'error') {
            return ['error'=>$checkConnection['message']];
        }

        return ['success'=>'Connection with WHMCS is successfully.'];
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
