<?php

namespace App\Http\Livewire;

use App\Helpers\RepositoryPathHelper;
use App\Jobs\ProcessPackageSatis;
use App\Models\Package;
use App\Models\TeamPackage;
use App\View\Columns\BooleanSwitchColumn;
use App\View\Columns\ButtonConfirmColumn;
use App\View\Columns\HtmlColumn;
use App\View\Columns\ImageWithLinkColumn;
use App\View\Columns\ScreenshotColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\ButtonGroupColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\ImageColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;
use Rappasoft\LaravelLivewireTables\Views\Filters\DateFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Filters\TextFilter;

class TeamPackagesTable extends DataTableComponent
{
    protected $model = TeamPackage::class;

    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setReorderEnabled()
            ->setSearchEnabled()
            ->setSearchDebounce(0)
            ->setDefaultReorderSort('position', 'asc')
            ->setReorderMethod('changePosition')
            ->setFilterLayoutSlideDown()
            ->setRememberColumnSelectionDisabled()
            ->setSecondaryHeaderTrAttributes(function($rows) {
                return ['class' => 'bg-gray-100'];
            })
            ->setSecondaryHeaderTdAttributes(function(Column $column, $rows) {
                if ($column->isField('id')) {
                    return ['class' => 'text-red-500'];
                }
                return ['default' => true];
            })
            ->setTdAttributes(function(Column $column, $rows) {
                if($column->getTitle()=='Provider') {
                    return ['class' => 'text-center'];
                }
                if($column->getTitle()=='Paid') {
                    return ['class' => 'text-center'];
                }
                if($column->getTitle()=='Visible') {
                    return ['class' => 'text-center'];
                }
                return [];
            })
            ->setFooterTrAttributes(function($rows) {
                return ['class' => 'bg-gray-100'];
            })
            ->setFooterTdAttributes(function(Column $column, $rows) {
                if ($column->isField('name')) {
                    return ['class' => 'text-green-500'];
                }

                return ['default' => true];
            })
            ->setUseHeaderAsFooterEnabled()
            ->setHideBulkActionsWhenEmptyEnabled();
    }

    public function filters(): array
    {
        return [
           /* TextFilter::make('Package Name')
                ->config([
                    'maxlength' => 5,
                    'placeholder' => 'Search Package Name',
                ])
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('package.name', 'like', '%'.$value.'%');
                }),*/

            SelectFilter::make('Provider')
                ->setFilterPillTitle('Provider')
                ->options([
                    ''    => 'Any',
                    'github' => 'Github',
                    'gitlab'  => 'Gitlab',
                ])
                ->filter(function(Builder $builder, string $value) {
                    if (!empty($value)) {
                        $builder->whereHas('package', function (Builder $query) use($value) {
                            $query->where('repository_url', 'like', '%'.$value.'%');
                        });
                    }
                }),

            SelectFilter::make('Cloned')
                ->setFilterPillTitle('Cloned')
                ->options([
                    ''    => 'Any',
                    'success' => 'Success',
                    'running'  => 'Running',
                    'failed'  => 'Failed',
                ])
                ->filter(function(Builder $builder, string $value) {
                    if ($value === 'success') {
                        $builder->whereHas('package', function (Builder $query) {
                            $query->where('clone_status',Package::CLONE_STATUS_SUCCESS);
                        });
                    } elseif ($value === 'running') {
                        $builder->whereHas('package', function (Builder $query) {
                            $query->where('clone_status',Package::CLONE_STATUS_RUNNING);
                        });
                    } elseif ($value === 'failed') {
                        $builder->whereHas('package', function (Builder $query) {
                            $query->where('clone_status',Package::CLONE_STATUS_FAILED);
                        });
                    }
                }),

            SelectFilter::make('Visible')
                ->setFilterPillTitle('Visible')
                ->options([
                    '' => 'Any',
                    '1' => 'Visible',
                    '0' => 'Hidden',
                ])
                ->filter(function(Builder $builder, string $value) {
                    if ($value === '1') {
                        $builder->where('is_visible', 1);
                    } elseif ($value === '0') {
                        $builder->where('is_visible', 0);
                    }
                }),

            SelectFilter::make('Paid')
                ->setFilterPillTitle('Paid')
                ->options([
                    '' => 'Any',
                    '1' => 'Paid',
                    '0' => 'Free',
                ])
                ->filter(function(Builder $builder, string $value) {
                    if ($value === '1') {
                        $builder->where('is_paid', 1);
                    } elseif ($value === '0') {
                        $builder->where('is_paid', 0);
                    }
                }),

            DateFilter::make('Updated at')
                ->filter(function(Builder $builder, string $value) {
                    $builder->where('updated_at', '>=', $value);
                })
        ];
    }

    public function columns(): array
    {
        return [
            ScreenshotColumn::make('Screenshot','package.screenshot')
                ->location(function($row) {
                    if (!empty($row->package->screenshot)) {
                       return $row->package->screenshot();
                    }
                    return '';
                }),

            HtmlColumn::make('Details')
                ->setOutputHtml(function($row) {
                    $html = '<div><b>'.Str::limit($row->package->description, 40).'</b></div>';
                    $html .= '<div>'.$row->package->name.'</div>';
                    $html .= '<div>Added by: <b>'.$row->package->owner->name.'</b></div>';
                    if ($row->package->version > 0) {
                        $html .= '<div> <span class="badge bg-success">v'.$row->package->version.'</span></div>';
                    }
                    return $html;
                }),

            ImageWithLinkColumn::make('Provider','provider')
                ->location(function($row) {
                    return [
                        'target'=>'_blank',
                        'href'=>$row->package->repository_url,
                        'location'=>  asset('images/' . RepositoryPathHelper::getRepositoryProviderByUrl($row->package->repository_url).'.svg')
                    ];
                }),

            HtmlColumn::make('Clone Status','package.clone_status')
                ->setOutputHtml(function($row) {
                    if ($row->package->clone_status=='success') {
                        return '<span class="badge badge bg-success text-uppercase">Success</span>';
                    }
                    if ($row->package->clone_status=='running') {
                        return '<span class="badge badge bg-black text-uppercase">Running</span>';
                    }
                    if ($row->package->clone_status=='failed') {
                        return '<span class="badge badge bg-black text-uppercase">Failed</span>';
                    }
                    if ($row->package->clone_status=='waiting') {
                        return '<span class="badge badge bg-info text-uppercase">Waiting</span>';
                    }
                    if ($row->package->clone_status=='remote_success') {
                        return '<span class="badge badge bg-success text-uppercase">Remote Success</span>';
                    }
                    if ($row->package->clone_status=='remote_running') {
                        return '<span class="badge badge bg-black text-uppercase">Remote Running</span>';
                    }
                    if ($row->package->clone_status=='remote_failed') {
                        return '<span class="badge badge bg-black text-uppercase">Remote Failed</span>';
                    }
                    if ($row->package->clone_status=='remote_waiting') {
                        return '<span class="badge badge bg-info text-uppercase">Remote Waiting</span>';
                    }
                    return '';
                }),


            BooleanSwitchColumn::make('Visible', 'is_visible')
                ->options([
                    '0' => '<span class="badge badge bg-black text-uppercase">Hidden</span>',
                    '1' => '<span class="badge badge bg-success text-uppercase">Visible</span>'
                ])
                ->sortable(),
            BooleanSwitchColumn::make('Paid', 'is_paid')
                ->options([
                    '0' => '<span class="badge badge bg-success text-uppercase">Free</span>',
                    '1' => '<span class="badge badge bg-primary text-uppercase">$ Paid</span>',
                ])
                ->sortable(),

            Column::make('Last Update', 'updated_at')
                ->sortable(),

            ButtonGroupColumn::make('Actions')
                ->attributes(function($row) {
                    return [
                        'class' => 'space-x-2',
                    ];
                })
                ->buttons([
                    LinkColumn::make('View','view')
                        ->title(function($row) {
                            return 'View';
                        })
                        ->location(function($row) {
                            return route('my-packages.show', $row->package->id);
                        })
                        ->attributes(function($row) {
                            return [
                                'class' => 'btn btn-outline-dark btn-sm',
                            ];
                        }),
                    ButtonConfirmColumn::make('Update','update')
                        ->title(function($row){
                            return 'Update';
                        })
                        ->attributes(function($row) {
                            return [
                                'wire:click'=>'packageUpdate('.$row->package->id.')',
                                'wire:loading.attr'=>'disabled',
                                'class' => 'btn btn-outline-dark btn-sm',
                            ];
                        }),

                    LinkColumn::make('Edit','edit')
                        ->title(function($row){
                            return 'Edit';
                        })
                        ->location(function($row) {
                            return route('team-packages.edit', $row->id);
                        })
                        ->attributes(function($row) {
                            return [
                                'class' => 'btn btn-outline-dark btn-sm',
                            ];
                        }),
                    ButtonConfirmColumn::make('Delete')
                        ->title(function($row){
                            return 'Delete';
                        })
                        ->attributes(function($row) {
                            return [
                                'wire:click'=>'multiplePackageDelete('.$row->id.')',
                                'wire:loading.attr'=>'disabled',
                                'class' => 'btn btn-outline-dark btn-sm',
                            ];
                        })
            ])
        ];
    }

    public function builder() : Builder
    {
        $user = auth()->user();
        $team = $user->currentTeam;

        $query = TeamPackage::query();
        $query->select(['id','team_id','package_id']);
        $query->where('team_id', $team->id);

        $query->whereHas('package');

        if ($this->hasSearch()) {
            $search = $this->getSearch();
            $search = trim(strtolower($search));
            $query->whereHas('package', function (Builder $subQuery) use ($search) {
                $subQuery->whereRaw('LOWER(`name`) LIKE ? ',['%'.$search.'%']);
                $subQuery->orWhereRaw('LOWER(`keywords`) LIKE ? ',['%'.$search.'%']);
                $subQuery->orWhereRaw('LOWER(`description`) LIKE ? ',['%'.$search.'%']);
                $subQuery->orWhereRaw('LOWER(`repository_url`) LIKE ? ',['%'.$search.'%']);
            });
        }

        $query->whereHas('team');
        $query->with('package');
        $query->with('team');
        $query->orderBy('position','asc');

        return $query;
    }
    /**
     * Search the search query from the table array
     */
    public function clearSearch(): void
    {
        $this->{$this->getTableName()}['search'] = null;
        $this->refresh = true;
    }


    public function bulkActions(): array
    {
        $bulkActions = [
            'multiplePackageUpdate' => 'Update',
            'multiplePackageVisible' => 'Make Visible',
            'multiplePackageHidden' => 'Make Hidden',
            'multiplePackagePaid' => 'Make Paid',
            'multiplePackageFree' => 'Make Free',
        ];

        $user = auth()->user();
        $team = $user->currentTeam;
        $packageAccessPresets = $team->packageAccessPresets()->get();
        if ($packageAccessPresets !== null) {
            foreach ($packageAccessPresets as $preset) {
                $bulkActions['multiplePackageAccessPreset(' . $preset['id'].')'] = 'Make as ' . $preset['name'];
            }
        }

        $bulkActions['multiplePackageDelete'] = 'Delete';

        return $bulkActions;
    }

    public function multiplePackageAccessPreset($presetId = false) {
        if ($presetId) {
            $teamPackages = TeamPackage::whereIn('id', $this->getSelected())->get();
            if ($teamPackages !== null) {
                foreach ($teamPackages as $teamPackage) {
                    $teamPackage->is_paid = 1;
                    $teamPackage->package_access_preset_id = $presetId;
                    $teamPackage->save();
                }
            }
        }
    }

    public function multiplePackageUpdate()
    {
        $teamPackages = TeamPackage::whereIn('id', $this->getSelected())->get();
        if ($teamPackages !== null) {
            foreach ($teamPackages as $teamPackage) {

                $package = Package::where('id', $teamPackage->package_id)
                    ->userHasAccess()
                    ->first();
                if ($package == null) {
                    return [];
                }

                $package->updatePackageWithSatis();
            }
        }

        $this->clearSelected();
    }

    public function multiplePackageVisible()
    {
        TeamPackage::whereIn('id', $this->getSelected())->update(['is_visible' => 1]);
        $this->clearSelected();
    }

    public function multiplePackageHidden()
    {
        TeamPackage::whereIn('id', $this->getSelected())->update(['is_visible' => 0]);
        $this->clearSelected();
    }

    public function multiplePackagePaid()
    {
        TeamPackage::whereIn('id', $this->getSelected())->update(['is_paid' => 1]);
        $this->clearSelected();
    }

    public function multiplePackageFree()
    {
        TeamPackage::whereIn('id', $this->getSelected())->update(['is_paid' => 0]);
        $this->clearSelected();
    }

    public function changePosition($items): void
    {
        foreach ($items as $item) {
            TeamPackage::find((int)$item['value'])->update(['position' => (int)$item['order']]);
        }
    }

    public function packageUpdate($id)
    {
        $package = Package::where('id',$id)
            ->userHasAccess()
            ->first();
        if ($package == null) {
            return [];
        }

        $package->updatePackageWithSatis();

        $this->check_background_job = true;
        $this->refresh = true;
    }

    public function multiplePackageDelete($id = false)
    {
        $ids = [];
        if ($id) {
            $ids[] = $id;
        } else {
            $ids = $this->getSelected();
            $this->clearSelected();
        }

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $user = auth()->user();
                $team = $user->currentTeam;

                if (!$user->hasTeamRole($team, 'admin')) {
                    return [];
                }

                $findTeamPackage = TeamPackage::where('id', $id)->where('team_id', $team->id)->first();
                $findTeamPackage->delete();
            }
        }
    }
}
