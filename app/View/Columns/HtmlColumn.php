<?php

namespace App\View\Columns;

use Illuminate\Database\Eloquent\Model;
use Rappasoft\LaravelLivewireTables\Views\Column;

class HtmlColumn extends Column
{
    protected string $view = 'livewire-tables::includes.columns.html';

    public function __construct(string $title, string $from = null)
    {
        parent::__construct($title, $from);

        $this->label(fn () => null);
    }

    public function setOutputHtml(callable $callback): self
    {
        $this->outputHtmlCallback = $callback;

        return $this;
    }

    public function getContents(Model $row)
    {
        return app()->call($this->outputHtmlCallback, ['row' => $row]);
    }
}
