@pbSchema(['name' => 'columns.blade'])
@php
    $count = max(2, min(3, (int) ($content['columns'] ?? 2)));
@endphp
<div class="pb-columns pb-columns-{{ $count }}">@for($column=1;$column<=$count;$column++)<div class="pb-column">@foreach($children->filter(fn($child)=>($child['block']->slot_key??'')==='column_'.$column) as $child) @include('pagebuilder.partials.node',['node'=>$child]) @endforeach</div>@endfor</div>
