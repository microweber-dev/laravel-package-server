@php
    $uniqieIdBtn = uniqid();
@endphp

<script>
    function askForConfirm{{$uniqieIdBtn}}(element)
    {
        if (element.getAttribute('confirmed') == 1) {
            element.innerHTML = '{{ $title }}';
            element.removeAttribute('confirmed');
            return true;
        }

        element.innerHTML = 'Sure?';
        element.setAttribute("confirmed", 1);
        return false;
    }
</script>


<button type="button" onclick="return askForConfirm{{$uniqieIdBtn}}(this);" {!! count($attributes) ? $column->arrayToAttributes($attributes) : '' !!}>
    {{ $title }}
</button>
