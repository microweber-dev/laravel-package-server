<?php
namespace App\Http\Livewire;

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
    public $credentialType = 'github-oauth';
    public $accessToken = '';

    /**
     * Render the component.
     *
     * @return Illuminate\View\View
     */
    public function render()
    {
        if ($this->credentialType == 'github-oauth') {
            $this->domain = 'github.com';
        }

        if ($this->credentialType == 'gitlab-token') {
             $this->domain = 'gitlab.com';
        }

        return view('profile.credentials-form');
    }

    public function create()
    {
        dump($this->credentialType);
    }
}
