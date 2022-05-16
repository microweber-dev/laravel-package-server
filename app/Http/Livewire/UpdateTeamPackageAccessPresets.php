<?php
namespace App\Http\Livewire;

use App\Models\Credential;
use App\Models\PackageAccessPreset;
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

    public $title = '';
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

        $this->presets = [];

        return view('teams.update-team-package-access-presets');
    }

    public function showPresetForm()
    {
        $this->showPresetForm = true;

        $this->presetId = false;
        $this->name = '';

    }

    public function hidePresetForm()
    {
        $this->showPresetForm = false;
    }

    public function confirmDelete($id)
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete($presetId)
    {
        $user = auth()->user();
        $findPreset = PackageAccessPreset::where('user_id', $user->id)->where('id', $presetId)->first();
        if ($findPreset != null) {
            $findPreset->delete();
        }
    }

    public function edit($presetId) {

        $user = auth()->user();
        $findPreset = PackageAccessPreset::where('user_id', $user->id)->where('id', $presetId)->first();
        if ($findPreset != null) {
            $this->showPresetForm = true;
            $this->presetEdit = true;
            $this->presetId = $findPreset->id;
            $this->name = $findPreset->name;
        }
    }

    public function save($presetId = false)
    {
        $validation = [];
        $validation['name'] = ['required'];
        $this->validate($validation);

        $user = auth()->user();

        if ($presetId) {
            $findPreset = PackageAccessPreset::where('user_id', $user->id)->where('id', $presetId)->first();
            if ($findPreset == null) {
                return [];
            }
            $preset = $findPreset;
        } else{
            $preset = new PackageAccessPreset();
            $preset->user_id = $user->id;
        }

        $preset->name = $this->name;
        $preset->save();

        $this->showPresetForm = false;
    }
}
