<x-jet-action-section>
    <x-slot name="title">
        {{ __('Package Access Presets') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Quick apply access presets to team packages') }}
    </x-slot>

    <x-slot name="content">

        @if($showPresetForm)
            <button wire:click="hidePresetForm()" class="btn btn-dark btn-sm">Close Preset Form</button>
        @else
            <button wire:click="showPresetForm()" class="btn btn-dark btn-sm">Add New Preset</button>
        @endif

        @if($showPresetForm)
            <div>

                @if ($presetEdit)
                    <div class="mt-3"><h4>{{ __('Edit preset') }}</h4></div>
                @else
                 <div class="mt-3"><h4>{{ __('Add New Preset') }}</h4></div>
                @endif

                <div class="w-md-75">
                    <div class="form-group">
                        <x-jet-label for="name" value="{{ __('Name') }}"/>

                        <x-jet-input id="name"
                                     type="text"
                                     class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                                     wire:model.defer="name"/>

                        <x-jet-input-error for="name"/>
                    </div>
                </div>

                    @if(!empty($this->whmcs_product_types))
                        @foreach($this->whmcs_product_types as $whmcs_product_type_name=>$whmcs_product_type)
                            <b>{{ucfirst($whmcs_product_type_name)}}</b> <br />
                            <div style="max-height: 200px;overflow-x: scroll">
                            @foreach($whmcs_product_type as $whmcs_product)
                                <div class="form-check">
                                    <input class="form-check-input" id="inputWhmcsProduct{{ $whmcs_product['pid']}}" value="{{ $whmcs_product['pid'] }}" wire:model.defer="whmcs_product_ids" type="checkbox">
                                    <label class="form-check-label" for="inputWhmcsProduct{{ $whmcs_product['pid'] }}">
                                        {{$whmcs_product['name']}}
                                    </label>
                                </div>
                            @endforeach
                            </div>
                        @endforeach
                    @endif

                <div class="d-flex align-items-baseline">
                    @if ($presetEdit)
                        <button type="button" wire:click="save({{$presetId}})" class="btn btn-outline-dark">Save</button>
                    @else
                        <button type="button" wire:click="save" class="btn btn-outline-dark">Create</button>
                    @endif
                </div>
                @endif
            </div>

            <div class="p-3">
                <table class="table table-borderless bg-white">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th scope="col">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($presets as $preset)
                        <tr  @if($confirmingDeleteId === $preset->id) class="table-danger" @endif>
                            <td>{{$preset->name}}</td>
                            <td>{{$preset->updated_at}}</td>
                            <td>
                                <button type="button" class="btn btn-outline-dark btn-sm" wire:click="edit({{ $preset->id }})">Edit</button>

                                @if($confirmingDeleteId === $preset->id)
                                    <button wire:click="delete({{ $preset->id }})"
                                            class="btn btn-outline-dark btn-sm">Sure?</button>
                                @else
                                    <button wire:click="confirmDelete({{ $preset->id }})"
                                            class="btn btn-outline-danger btn-sm">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </x-slot>
</x-jet-action-section>
