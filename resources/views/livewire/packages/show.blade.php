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

        <canvas id="download_statistic"></canvas>

        <script>
            var download_statistic_data = {
                type: 'bar',
                data: {
                    labels: [
                        "2022-01",
                    ],
                    datasets: [
                        {
                            label: 'Downloads by month',
                            data: [
                                4,
                            ],
                            borderWidth: 2
                        },
                    ]
                },
                options: {
                    tooltips: {
                        mode: 'point'
                    },
                    height: '300px',
                    scales: {
                        xAxes: [],
                        yAxes: [{
                            ticks: {
                                beginAtZero:true
                            }
                        }]
                    },
                }
            };
            var download_statistic = new Chart(document.getElementById("download_statistic"), download_statistic_data);

            document.addEventListener('livewire:load', function () {
                window.livewire.on('chart-data', chartData => {

                    download_statistic.data.labels = [];
                    download_statistic.data.datasets = [];
                    download_statistic.update();
                    
                    Object.entries(chartData.data.datasets).forEach(([key, dataset]) => {
                        download_statistic.data.datasets.push(dataset);
                    });

                    Object.entries(chartData.data.labels).forEach(([key, labels]) => {
                        download_statistic.data.labels.push(labels);
                    });

                    download_statistic.update();

                });
            });
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
