<?php

namespace App\View\Columns;

use Illuminate\Database\Eloquent\Model;
use Rappasoft\LaravelLivewireTables\Exceptions\DataTableConfigurationException;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filters\SelectFilter;
use Rappasoft\LaravelLivewireTables\Views\Traits\Configuration\BooleanColumnConfiguration;
use Rappasoft\LaravelLivewireTables\Views\Traits\Helpers\BooleanColumnHelpers;

class BooleanSwitchColumn extends Column
{
    use BooleanColumnConfiguration,
        BooleanColumnHelpers;

    protected string $type = 'icons';
    protected bool $successValue = true;
    protected string $view = 'livewire.tables.includes.columns.boolean_switch';
    protected $callback;

    protected array $options = [];

    public function options(array $options = [])
    {
        $this->options = $options;
        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getContents(Model $row)
    {
        if ($this->isLabel()) {
            throw new DataTableConfigurationException('You can not specify a boolean column as a label.');
        }

        $value = $this->getValue($row);

        return view($this->getView())
            ->withColumn($this)
            ->withRow($row)
            ->withOptions($this->getOptions())
            ->withComponent($this->getComponent())
            ->withSuccessValue($this->getSuccessValue())
            ->withType($this->getType())
            ->withStatus($this->hasCallback() ? call_user_func($this->getCallback(), $value, $row) : (bool)$value === true);
    }
}
