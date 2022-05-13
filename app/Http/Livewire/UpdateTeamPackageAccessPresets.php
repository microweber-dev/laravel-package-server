<?php
namespace App\Http\Livewire;

use App\Models\Credential;
use App\Rules\CanAddRepositoryToTeamRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Jetstream\InteractsWithBanner;
use Livewire\Component;

class UpdateTeamPackageAccessPresets extends Component
{
    use InteractsWithBanner;

    /**
     * The component's listeners.
     *
     * @var array
     */
    protected $listeners = [
        'refresh-navigation-menu' => '$refresh',
    ];

    public $domain = '';
    public $authenticationType = 'github-oauth';
    public $authenticationData = [];
    public $description = '';
    public $showPresetForm = false;
    public $presetId = false;
    public $presetEdit = false;
    public $confirmingDeleteId = false;

    /**
     * Render the component.
     *
     * @return Illuminate\View\View
     */
    public function render()
    {
        $user = auth()->user();

        if ($this->authenticationType == 'github-oauth') {
            $this->domain = 'github.com';
        }

        if ($this->authenticationType == 'gitlab-token') {
            $this->domain = 'gitlab.com';
        }

        $this->presets = [];

        return view('teams.update-team-package-access-presets');
    }

    public function showCredentialsForm()
    {
        $this->showCredentialsForm = true;

        $this->presetlId = false;
        $this->domain = '';
        $this->authenticationType = 'github-oauth';
        $this->authenticationData = [];
        $this->description = '';

    }

    public function hideCredentialsForm()
    {
        $this->showCredentialsForm = false;
    }

    public function confirmDelete($id)
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete($presetlId)
    {
        $user = auth()->user();
        $findCredential = Credential::where('user_id', $user->id)->where('id', $presetlId)->first();
        if ($findCredential != null) {
            $findCredential->delete();
        }
    }

    public function edit($presetlId) {

        $user = auth()->user();
        $findCredential = Credential::where('user_id', $user->id)->where('id', $presetlId)->first();
        if ($findCredential != null) {
            $this->showCredentialsForm = true;
            $this->presetlEdit = true;
            $this->presetlId = $findCredential->id;
            $this->domain = $findCredential->domain;
            $this->authenticationType = $findCredential->authentication_type;
            $this->authenticationData = $findCredential->authentication_data;
            $this->description = $findCredential->description;
        }
    }

    public function save($presetlId = false)
    {

        $validation = [];
        $validation['description'] = ['required'];
        $this->validate($validation);

        $user = auth()->user();

        if ($presetlId) {
            $findCredential = Credential::where('user_id', $user->id)->where('id', $presetlId)->first();
            if ($findCredential == null) {
                return [];
            }
            $presetl = $findCredential;
        } else{
            $presetl = new Credential();
            $presetl->user_id = $user->id;
        }

        $presetl->authentication_type = $this->authenticationType;
        $presetl->domain = $this->domain;
        $presetl->description = $this->description;
        $presetl->authentication_data = $this->authenticationData;
        $presetl->save();

        $this->showCredentialsForm = false;
    }
}
