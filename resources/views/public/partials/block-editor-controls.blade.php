<div class="pb-editor-toolbar" data-editor-toolbar>
    <span class="pb-editor-label"><i class="fa-solid fa-grip"></i> {{ $definition['label'] }}</span>
    <button type="button" data-block-action="up" title="Yuxarı"><i class="fa-solid fa-arrow-up"></i></button>
    <button type="button" data-block-action="down" title="Aşağı"><i class="fa-solid fa-arrow-down"></i></button>
    <button type="button" data-block-action="duplicate" title="Kopyala"><i class="fa-regular fa-copy"></i></button>
    <details class="pb-editor-popover"><summary title="Ayarlar"><i class="fa-solid fa-sliders"></i></summary>
        <form data-block-settings>
            @foreach($definition['fields'] as $field)
                @php
                    $value=$content[$field['key']]??($field['default']??'');
                @endphp
                @if($field['type']==='checkbox')
                    <label><input type="checkbox" name="content[{{ $field['key'] }}]" value="1" @checked((bool)$value)> {{ $field['label'] }}</label>
                @elseif($field['type']==='select')
                    <label>{{ $field['label'] }}<select name="content[{{ $field['key'] }}]">@foreach($field['options'] as $key=>$label)<option value="{{ $key }}" @selected((string)$value===(string)$key)>{{ $label }}</option>@endforeach</select></label>
                @elseif(!in_array($field['type'],['richtext','textarea','repeater'],true))
                    <label>{{ $field['label'] }}<input type="{{ $field['type']==='number'?'number':'text' }}" name="content[{{ $field['key'] }}]" value="{{ $value }}"></label>
                @endif
            @endforeach
            <label>Rəng<select name="theme">@foreach(['surface'=>'Ağ','muted'=>'Boz','brand'=>'Brend','dark'=>'Tünd'] as $key=>$label)<option value="{{ $key }}" @selected(($settings['theme']??'surface')===$key)>{{ $label }}</option>@endforeach</select></label>
            <label>Boşluq<select name="spacing">@foreach(['small'=>'Kiçik','medium'=>'Orta','large'=>'Böyük'] as $key=>$label)<option value="{{ $key }}" @selected(($settings['spacing']??'large')===$key)>{{ $label }}</option>@endforeach</select></label>
            <label>Şəkil<input type="file" name="image_path" accept=".jpg,.jpeg,.png,.webp,.gif,.svg"></label>
            <button type="submit" class="pb-editor-save">Yadda saxla</button>
        </form>
    </details>
    @if(!($definition['system']??false))<button type="button" data-block-action="delete" class="danger" title="Sil"><i class="fa-regular fa-trash-can"></i></button>@endif
</div>
