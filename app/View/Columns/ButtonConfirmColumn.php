<?php

namespace App\View\Columns;

use Illuminate\Database\Eloquent\Model;
use Rappasoft\LaravelLivewireTables\Exceptions\DataTableConfigurationException;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;

class ButtonConfirmColumn extends LinkColumn
{
    protected string $view = 'livewire.tables.includes.columns.button_confirm';

    public function getContents(Model $row)
    {
        if (! $this->hasTitleCallback()) {
            throw new DataTableConfigurationException('You must specify a title callback for an link column.');
        }

        return view($this->getView())
            ->withColumn($this)
            ->withRow($row)
            ->withTitle(app()->call($this->getTitleCallback(), ['row' => $row]))
            ->withAttributes($this->hasAttributesCallback() ? app()->call($this->getAttributesCallback(), ['row' => $row]) : []);
    }
}
