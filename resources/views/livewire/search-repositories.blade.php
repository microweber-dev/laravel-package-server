<div>

<input wire:model="keyword" type="text" class="form-control" placeholder="Search packages...">

<p>Microweber Packages is the official microweber cms composer repository. </p>

    @if (!empty($keyword))
You search for: {{$keyword}}
@endif

</div>
