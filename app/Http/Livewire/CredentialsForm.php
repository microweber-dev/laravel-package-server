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

class CredentialsForm extends Component
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
    public $showCredentialsForm = false;
    public $credentialId = false;
    public $credentialEdit = false;
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

        $this->credentials = $user->credentials()->get();

        return view('profile.credentials-form');
    }

    public function showCredentialsForm()
    {
        $this->showCredentialsForm = true;

        $this->credentialId = false;
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

    public function delete($credentialId)
    {
        $user = auth()->user();
        $findCredential = Credential::where('user_id', $user->id)->where('id', $credentialId)->first();
        if ($findCredential != null) {
            $findCredential->delete();
        }
    }

    public function edit($credentialId) {

        $user = auth()->user();
        $findCredential = Credential::where('user_id', $user->id)->where('id', $credentialId)->first();
        if ($findCredential != null) {
             $this->showCredentialsForm = true;
             $this->credentialEdit = true;
             $this->credentialId = $findCredential->id;
             $this->domain = $findCredential->domain;
             $this->authenticationType = $findCredential->authentication_type;
             $this->authenticationData = $findCredential->authentication_data;
             $this->description = $findCredential->description;
        }
    }

    public function save($credentialId = false)
    {

        $validation = [];
        $validation['description'] = ['required'];
        $this->validate($validation);

        $user = auth()->user();

        if ($credentialId) {
            $findCredential = Credential::where('user_id', $user->id)->where('id', $credentialId)->first();
            if ($findCredential == null) {
                return [];
            }
            $credential = $findCredential;
        } else{
            $credential = new Credential();
            $credential->user_id = $user->id;
        }

        $credential->authentication_type = $this->authenticationType;
        $credential->domain = $this->domain;
        $credential->description = $this->description;
        $credential->authentication_data = $this->authenticationData;
        $credential->save();

        $this->showCredentialsForm = false;
    }
}
