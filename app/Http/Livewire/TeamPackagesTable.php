<?php

namespace App\Http\Livewire;

use App\Models\TeamPackage;
use App\View\Columns\BooleanSwitchColumn;
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
            ->setDefaultReorderSort('position', 'desc')
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
            ImageColumn::make('Screenshot')
                ->location(function($row) {
                    if (!empty($row->package->screenshot)) {
                       return $row->package()->screenshot();
                    }
                    return '';
                })
                ->attributes(function($row) {
                    return [
                        'class' => 'w-8 h-8 rounded-full',
                    ];
                }),
            Column::make('Position', 'position')
                ->collapseOnMobile()
                ->excludeFromColumnSelect(),
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
                    LinkColumn::make('Edit')
                        ->title(function($row){
                            return 'Edit ' . $row->name;
                        })
                        ->location(function($row) {
                            return $row->name;
                        })
                        ->attributes(function($row) {
                            return [
                                'target' => '_blank',
                                'class' => 'underline text-blue-500 hover:no-underline',
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
        $query->select(['id']);
        $query->where('team_id', $team->id);
        $query->whereHas('package', function (Builder $query) {
                //     $query->where('clone_status',Package::CLONE_STATUS_SUCCESS);
            });
        $query->whereHas('team');
        $query->with('package');
        $query->with('team');

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
}
