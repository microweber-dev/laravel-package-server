<div class="row">

    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Show package') }}
        </h2>
    </x-slot>


    <div class="container p-3" style="background: #fff;">

        <div class="w-md-75 mb-4">
            <div class="form-group">
                <x-jet-label for="period_stats" value="{{ __('Period stats') }}" />
                <select id="period_stats" name="period_stats" wire:model="period_stats" class="form-control">
                    <option value="hourly">Hourly</option>
                    <option value="daily">Daily</option>
                    <option value="monthly">Monthly</option>
                </select>
            </div>
        </div>

        @dump($chart_js)
        
        <canvas id="download_statistic"></canvas>

        <script>
            var ctx = document.getElementById("download_statistic");
            var downloadStatistic = new Chart(ctx, {});
        </script>
    </div>

    <ul class="nav nav-tabs mt-5" id="myTab" role="tablist">
        <li class="nav-item" role="Information" wire:ignore >
            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#Information" type="button" role="tab" aria-controls="Information" aria-selected="true">
                Information
            </button>
        </li>
        <li class="nav-item" role="presentation" wire:ignore >
            <button class="nav-link" id="Changelog-tab" data-bs-toggle="tab" data-bs-target="#Changelog" type="button" role="tab" aria-controls="Changelog" aria-selected="false">Changelog</button>
        </li>
        <li class="nav-item" role="Downloads" wire:ignore>
            <button class="nav-link" id="Downloads-tab" data-bs-toggle="tab" data-bs-target="#Downloads" type="button" role="tab" aria-controls="Downloads" aria-selected="false">Downloads</button>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="Information" role="tabpanel" aria-labelledby="Information-tab" wire:ignore.self>


        </div>
        <div class="tab-pane fade" id="Changelog" role="tabpanel" aria-labelledby="Changelog-tab" wire:ignore.self>

        </div>
        <div class="tab-pane fade" id="Downloads" role="tabpanel" aria-labelledby="Downloads-tab" wire:ignore.self>

        </div>
    </div>



</div>
