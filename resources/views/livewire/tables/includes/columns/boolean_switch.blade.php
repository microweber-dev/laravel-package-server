{{--

<div class="form-check form-switch">
    <input class="form-check-input" wire:click="{{\Illuminate\Support\Str::camel('set_'.$column->getField())}}({{$row->getKey()}})" value="1" type="checkbox" id="flexSwitchCheckBoolean{{$row->getKey()}}">
    <label class="form-check-label" for="flexSwitchCheckBoolean{{$row->getKey()}}">
        Yes
    </label>
</div>

{{$successValue}}
--}}

@if(isset($options[$status]))
    {!! $options[$status] !!}
@endif
