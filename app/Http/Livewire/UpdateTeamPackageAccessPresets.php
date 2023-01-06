<?php
namespace App\Http\Livewire;

use App\Models\Credential;
use App\Models\PackageAccessPreset;
use App\Rules\CanAddRepositoryToTeamRule;
use App\WhmcsManager;
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
    public $whmcs_product_ids = [];
    public $buy_url = '';
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
        $team = $user->currentTeam;

        $teamSettings = $team->settings()->get();

        $whmcsManger = new WhmcsManager($teamSettings);
        try {
            $whmcsProducts = $whmcsManger->getProducts();
        } catch (\Exception $e) {
            $whmcsProducts = [];
        }
        $whmcsProductsTypes = [];
        if (isset($whmcsProducts['products']['product'])) {
            foreach ($whmcsProducts['products']['product'] as $product) {
                $whmcsProductsTypes[$product['type']][] = $product;
            }
        }

        $this->whmcs_product_types = $whmcsProductsTypes;

        $this->presets = $team->packageAccessPresets()->get();

        return view('teams.update-team-package-access-presets');
    }

    public function showPresetForm()
    {
        $this->showPresetForm = true;

        $this->presetId = false;
        $this->name = '';
        $this->buy_url = '';
        $this->whmcs_product_ids = [];

    }

    public function hidePresetForm()
    {
        $this->showPresetForm = false;

        $this->presetId = false;
        $this->name = '';
        $this->buy_url = '';
        $this->whmcs_product_ids = [];
    }

    public function confirmDelete($id)
    {
        $this->confirmingDeleteId = $id;
    }

    public function delete($presetId)
    {
        $user = auth()->user();
        $teamId = $user->currentTeam->id;

        $findPreset = PackageAccessPreset::where('team_id', $teamId)->where('id', $presetId)->first();
        if ($findPreset != null) {
            $findPreset->delete();
        }
    }

    public function edit($presetId) {

        $user = auth()->user();
        $teamId = $user->currentTeam->id;

        $findPreset = PackageAccessPreset::where('team_id', $teamId)->where('id', $presetId)->first();
        if ($findPreset != null) {

            $this->showPresetForm = true;
            $this->presetEdit = true;
            $this->presetId = $findPreset->id;
            $this->name = $findPreset->name;

            if (isset($findPreset->settings['buy_url'])) {
                $this->buy_url = $findPreset->settings['buy_url'];
            }

            if (is_array($findPreset->settings['whmcs_product_ids'])) {
                $this->whmcs_product_ids = $findPreset->settings['whmcs_product_ids'];
            }
        }
    }

    public function save($presetId = false)
    {
        $validation = [];
        $validation['name'] = ['required'];
        $this->validate($validation);

        $user = auth()->user();
        $teamId = $user->currentTeam->id;

        if ($presetId) {
            $findPreset = PackageAccessPreset::where('team_id', $teamId)->where('id', $presetId)->first();
            if ($findPreset == null) {
                return [];
            }
            $preset = $findPreset;
        } else{
            $preset = new PackageAccessPreset();
            $preset->user_id = $user->id;
            $preset->team_id = $teamId;
        }

        $preset->name = $this->name;
        $preset->settings = [
            'buy_url'=>$this->buy_url,
            'whmcs_product_ids'=>$this->whmcs_product_ids,
        ];
        $preset->save();

        $this->name = '';
        $this->buy_url = '';
        $this->whmcs_product_ids = [];
        $this->showPresetForm = false;
    }
}
