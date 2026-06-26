@pbSchema(['name' => 'contact_form.blade'])
@php
    $ui = \App\Support\PublicUiText::all($lang);
@endphp
<section class="contact-form">
@if($content['title']??false)<h2 data-inline-field="title">{{ $content['title'] }}</h2>@endif
@if($content['text']??false)<p data-inline-field="text">{{ $content['text'] }}</p>@endif
@if(session('contact_success'))<div class="alert alert-ok">{{ session('contact_success') }}</div>@endif
@if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ route('contact.store') }}">@csrf<input type="hidden" name="lang" value="{{ $lang }}"><div class="form-row"><label>{{ $ui['full_name'] }}</label><input name="full_name" value="{{ old('full_name') }}" required></div><div class="form-row"><label>Email</label><input type="email" name="email" value="{{ old('email') }}"></div><div class="form-row"><label>{{ $ui['phone'] }}</label><input name="phone" value="{{ old('phone') }}"></div><div class="form-row"><label>{{ $ui['subject'] }}</label><input name="subject" value="{{ old('subject') }}"></div><div class="form-row"><label>{{ $ui['message']??'Mesaj' }}</label><textarea name="message" required>{{ old('message') }}</textarea></div><button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> {{ $ui['send'] }}</button></form>
</section>
