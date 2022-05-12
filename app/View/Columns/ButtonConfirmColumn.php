<?php

namespace App\View\Columns;

use Illuminate\Database\Eloquent\Model;
use Rappasoft\LaravelLivewireTables\Exceptions\DataTableConfigurationException;
use Rappasoft\LaravelLivewireTables\Views\Columns\LinkColumn;

class ButtonConfirmColumn extends LinkColumn
{
    protected string $view = 'livewire.tables.includes.columns.button_confirm';

    protected $attributesWhenConfirmedCallback;

    public function attributesWhenConfirmed(callable $callback): self
    {
        $this->attributesWhenConfirmedCallback = $callback;

        return $this;
    }

    public function getAttributesWhenConfirmedCallback()
    {
        return $this->attributesWhenConfirmedCallback;
    }

    public function hasAttributesWhenConfirmedCallback()
    {
        return $this->attributesWhenConfirmedCallback !== null;
    }


    public function getContents(Model $row)
    {
        if (! $this->hasTitleCallback()) {
            throw new DataTableConfigurationException('You must specify a title callback for an link column.');
        }

        return view($this->getView())
            ->withColumn($this)
            ->withRow($row)
            ->withTitle(app()->call($this->getTitleCallback(), ['row' => $row]))
            ->withAttributesWhenConfirmed($this->hasAttributesWhenConfirmedCallback() ? app()->call($this->getAttributesWhenConfirmedCallback(), ['row' => $row]) : [])
            ->withAttributes($this->hasAttributesCallback() ? app()->call($this->getAttributesCallback(), ['row' => $row]) : []);
    }
}
