<div class="row">

    <x-slot name="header">
        <h2 class="h4 font-weight-bold">
            {{ __('Show package') }} >  {{$package->description}}
        </h2>
    </x-slot>

    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="Information" wire:ignore>
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#Information"
                            type="button" role="tab" aria-controls="Information" aria-selected="true">
                        Information
                    </button>
                </li>
                <li class="nav-item" role="presentation" wire:ignore>
                    <button class="nav-link" id="Changelog-tab" data-bs-toggle="tab" data-bs-target="#Changelog"
                            type="button" role="tab" aria-controls="Changelog" aria-selected="false">Changelog
                    </button>
                </li>
                <li class="nav-item" role="Downloads" wire:ignore>
                    <button class="nav-link" id="Downloads-tab" data-bs-toggle="tab" data-bs-target="#Downloads"
                            type="button" role="tab" aria-controls="Downloads" aria-selected="false">Downloads
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="Information" role="tabpanel" aria-labelledby="Information-tab"
                     wire:ignore.self>

                    <div class="row mt-4">
                        <div class="col-md-8">
                            <img src="{{$package->screenshot()}}" style="max-width: 100%" />
                            <br />
                            <br />
                            {!! $package->readme() !!}
                        </div>
                        <div class="col-md-4">
                            <h1>{{$package->description}}

                                @if($package->version > 0)
                                    <span class="badge bg-success">v{{$package->version}}</span>
                                @endif
                            </h1>

                            <div class="mt-4">
                                <a href="{{$package->repository_url}}" target="_blank">
                                    {{$package->repository_url}}
                                </a>
                            </div>

                            @if($package->clone_status != \App\Models\Package::CLONE_STATUS_SUCCESS)
                                <div style="max-height: 100px;max-width: 700px;overflow: scroll" class="text-danger">
                                    {{$package->clone_log}}
                                </div>
                            @endif

                            <div class="mt-3">
                                Clone status: 
                            @if($package->clone_status == \App\Models\Package::CLONE_STATUS_SUCCESS)
                                <span class="badge bg-success">{{$package->clone_status}}</span>
                            @else
                                <span class="badge bg-danger">{{$package->clone_status}}</span>
                            @endif
                            </div>

                            <div class="mt-3">
                                <b>Last update: {{$package->updated_at}}</b>
                            </div>

                            <div class="mt-3">
                            <button type="button" class="btn btn-outline-dark btn-sm" wire:click="update({{ $package->id }})" wire:loading.attr="disabled">Update</button>
                            <a href="{{route('my-packages.edit', $package->id)}}" class="btn btn-outline-dark btn-sm">Edit</a>

                            @if($confirming_delete_id === $package->id)
                                <button wire:click="delete({{ $package->id }})" class="btn btn-outline-danger btn-sm">Sure?</button>
                            @else
                                <button wire:click="confirmDelete({{ $package->id }})"  class="btn btn-outline-dark btn-sm">Delete</button>
                            @endif
                            </div>


                        </div>
                    </div>

                </div>
                <div class="tab-pane fade" id="Changelog" role="tabpanel" aria-labelledby="Changelog-tab" wire:ignore.self>
                    {!! $package->readme() !!}
                </div>
                <div class="tab-pane fade" id="Downloads" role="tabpanel" aria-labelledby="Downloads-tab" wire:ignore.self>

                    <div class="container p-3" style="background: #fff;">

                        <div class="w-md-75 mb-4">
                            <div class="form-group">
                                <x-jet-label for="period_stats" value="{{ __('Period stats') }}"/>
                                <select id="period_stats" name="period_stats" wire:model="period_stats" class="form-control">
                                    <option value="hourly">Hourly</option>
                                    <option value="daily">Daily</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                </select>
                            </div>
                        </div>

                        @foreach($charts_data as $chart)
                        <h4>{{$chart['name']}}</h4>

                            @if(isset($chart['data']))
                                <table class="table table-bordered">
                                    <tr>
                                        <td>Date</td>
                                        <td>Downloads</td>
                                    </tr>
                            @foreach($chart['data'] as $chartDate=>$chartCount)
                                <tr>
                                    <td>{{$chartDate}}</td>
                                    <td><span class="badge bg-success">{{$chartCount}}</span></td>
                                </tr>
                              @endforeach

                                </table>
                            @endif

                        @endforeach

                    </div>

                </div>
            </div>
        </div>




    </div>


</div>
