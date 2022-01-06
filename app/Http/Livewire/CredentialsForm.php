<?php
namespace App\Http\Livewire;

use App\Models\Credential;
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

    /**
     * Render the component.
     *
     * @return Illuminate\View\View
     */
    public function render()
    {
        if ($this->authenticationType == 'github-oauth') {
            $this->domain = 'github.com';
        }

        if ($this->authenticationType == 'gitlab-token') {
             $this->domain = 'gitlab.com';
        }

        return view('profile.credentials-form');
    }

    public function create()
    {
        $user = auth()->user();

        $credential = new Credential();
        $credential->user_id = $user->id;
        $credential->authentication_type = $this->authenticationType;
        $credential->domain = $this->domain;
        $credential->description = $this->description;
        $credential->authentication_data = $this->authenticationData;
        $credential->save();

    ///    dump($this->credentialType);
    }
}
