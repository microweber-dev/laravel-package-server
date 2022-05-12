<?php

namespace App\View\Columns;

use Rappasoft\LaravelLivewireTables\Views\Columns\ImageColumn;

class ImageWithLinkColumn extends ImageColumn
{
    protected string $view = 'livewire.tables.includes.columns.image_with_link';

}
