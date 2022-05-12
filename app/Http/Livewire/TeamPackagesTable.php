<?php

namespace App\Http\Livewire;

use App\Helpers\RepositoryPathHelper;
use App\Jobs\ProcessPackageSatis;
use App\Models\Package;
use App\Models\TeamPackage;
use App\View\Columns\BooleanSwitchColumn;
use App\View\Columns\ButtonConfirmColumn;
use App\View\Columns\HtmlColumn;
use App\View\Columns\ScreenshotColumn;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Columns\ButtonGroupColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\ImageColumn;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;

class TeamPackagesTable extends DataTableComponent
{
    protected $model = TeamPackage::class;


    public function configure(): void
    {
        $this->setPrimaryKey('id')
            ->setReorderEnabled()
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

            ImageColumn::make('Provider','package.screenshot')
                ->location(function($row) {
                    return asset('images/' . RepositoryPathHelper::getRepositoryProviderByUrl($row->package->repository_url).'.svg');
                }),

            HtmlColumn::make('Status','package.clone_status')
            ->setOutputHtml(function($row) {
                return $row->package->clone_status;
            }),

          /*  Column::make('Position', 'position')
                ->sortable()
                ->excludeFromColumnSelect(),*/
            BooleanSwitchColumn::make('Is Visible', 'is_visible')
                ->sortable()
                ->searchable(),
            BooleanSwitchColumn::make('Is Paid', 'is_paid')
                ->sortable()
                ->searchable(),
            Column::make('Last Update', 'updated_at')
                ->sortable()
                ->searchable(),

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
                            return route('my-packages.edit', $row->package->id);
                        })
                        ->attributes(function($row) {
                            return [
                                'class' => 'btn btn-outline-dark btn-sm',
                            ];
                        }),
                    ButtonConfirmColumn::make('Delete','delete')
                        ->title(function($row){
                            return 'Delete';
                        })
                        ->attributes(function($row) {
                            return [
                                'wire:click'=>'pacakgeDelete('.$row->package->id.')',
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
        $query->whereHas('package', function (Builder $query) {
            //$query->where('clone_status',Package::CLONE_STATUS_SUCCESS);
        });
        $query->whereHas('team');
        $query->with('package');
        $query->with('team');
        $query->orderBy('position','asc');

        return $query;
    }

    public function bulkActions(): array
    {
        return [
            'visible' => 'Is Visible',
            'paid' => 'Is Paid',
        ];
    }

    public function visible()
    {
        TeamPackage::whereIn('id', $this->getSelected())->update(['is_visible' => 1]);

        $this->clearSelected();
    }

    public function paid()
    {
        TeamPackage::whereIn('id', $this->getSelected())->update(['is_paid' => 1]);

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

        dispatch(new ProcessPackageSatis($package->id));

        $this->check_background_job = true;
    }

    public function pacakgeDelete($id)
    {
        $user = auth()->user();
        $team = $user->currentTeam;

        if (!$user->hasTeamRole($team, 'admin')) {
            return [];
        }

        $findTeamPackage = TeamPackage::where('id', $id)->where('team_id', $team->id)->first();
        $findTeamPackage->delete();
    }
}
