<script>
    function askForConfirm(title, element,event)
    {
        if (element.getAttribute('confirmed') == 1) {
            element.innerHTML = title;
            element.removeAttribute('confirmed');;
            return true;
        }

        event.stopImmediatePropagation();

        element.innerHTML = 'Sure?';
        element.setAttribute("confirmed", 1);

        return false;
    }
</script>


<button type="button" onclick="return askForConfirm('{{ $title }}',this, event)" {!! count($attributes) ? $column->arrayToAttributes($attributes) : '' !!}>
    {{ $title }}
</button>
