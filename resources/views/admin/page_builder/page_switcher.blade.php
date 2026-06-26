<div class="card" style="padding:12px 16px;margin-bottom:12px">
<form method="get" action="{{ route('admin.page-builder.index') }}" style="display:flex;align-items:end;gap:12px;max-width:520px">
<input type="hidden" name="lang_code" value="{{ $language }}">
<label style="margin:0;flex:1">Redaktə ediləcək səhifə<select name="page" onchange="this.form.submit()">@foreach($pages as $item)<option value="{{ $item['page_key'] }}" @selected($item['page_key'] === $pageKey)>{{ $item['title'] }}</option>@endforeach</select></label>
</form>
</div>
