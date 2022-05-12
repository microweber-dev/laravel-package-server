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

            HtmlColumn::make('Clone Status','package.clone_status')
                ->setOutputHtml(function($row) {
                    if ($row->package->clone_status=='success') {
                        return '<span class="badge badge bg-success text-uppercase">Success</span>';
                    }
                    if ($row->package->clone_status=='running') {
                        return '<span class="badge badge bg-black text-uppercase">Running</span>';
                    }
                    return '';
                }),

            ImageWithLinkColumn::make('Provider')
                ->location(function($row) {
                    return [
                      'target'=>'_blank',
                      'href'=>$row->package->repository_url,
                      'location'=>  asset('images/' . RepositoryPathHelper::getRepositoryProviderByUrl($row->package->repository_url).'.svg')
                    ];
                }),

            BooleanSwitchColumn::make('Visible', 'is_visible')
                ->options([
                    '0' => '<span class="badge badge bg-black text-uppercase">Hidden</span>',
                    '1' => '<span class="badge badge bg-success text-uppercase">Visible</span>'
                ])
                ->sortable()
                ->searchable(),
            BooleanSwitchColumn::make('Paid', 'is_paid')
                ->options([
                    '0' => '<span class="badge badge bg-success text-uppercase">Free</span>',
                    '1' => '<span class="badge badge bg-primary text-uppercase">$ Paid</span>',
                ])
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
                    ButtonConfirmColumn::make('Delete')
                        ->title(function($row){
                            return 'Delete';
                        })
                        ->attributes(function($row) {
                            return [
                                'wire:click'=>'packageDelete('.$row->id.')',
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
        $query->whereHas('team');
        $query->with('package');
        $query->with('team');
        $query->orderBy('position','asc');

        return $query;
    }

    public function bulkActions(): array
    {
        return [
            'packageVisible' => 'Make Visible',
            'packageHidden' => 'Make Hidden',
            'packagePaid' => 'Make Paid',
            'packageFree' => 'Make Free',
            'packageDelete' => 'Delete',
        ];
    }

    public function packageVisible()
    {
        TeamPackage::whereIn('id', $this->getSelected())->update(['is_visible' => 1]);
        $this->clearSelected();
    }

    public function packageHidden()
    {
        TeamPackage::whereIn('id', $this->getSelected())->update(['is_visible' => 0]);
        $this->clearSelected();
    }

    public function packagePaid()
    {
        TeamPackage::whereIn('id', $this->getSelected())->update(['is_paid' => 1]);
        $this->clearSelected();
    }

    public function packageFree()
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

        dispatch(new ProcessPackageSatis($package->id));

        $this->check_background_job = true;
    }

    public function packageDelete($id = false)
    {
        $ids = [];
        if ($id) {
            $ids = $id;
        } else {
            $ids = $this->getSelected();
            $this->clearSelected();
        }

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
