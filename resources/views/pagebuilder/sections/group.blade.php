@pbSchema(['name' => 'group.blade'])
<div class="pb-group">@foreach($children as $child) @include('pagebuilder.partials.node',['node'=>$child]) @endforeach</div>
