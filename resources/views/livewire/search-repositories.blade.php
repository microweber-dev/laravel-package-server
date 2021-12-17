<div>
<input wire:model="keyword" type="text" class="form-control" placeholder="Search packages...">
<small>Microweber Packages is the official microweber cms composer repository. </small>
@if (!empty($keyword))
<br />You search for: {{$keyword}}
@endif
</div>
