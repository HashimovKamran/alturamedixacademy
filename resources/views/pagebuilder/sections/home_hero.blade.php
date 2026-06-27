@pbSchema(['name' => 'home_hero.blade'])
@php
    $siteName = trim((string) (($siteSettings['site_name'] ?? null) ?: ($settings['site_name'] ?? null) ?: 'ALTURAMEDIX ACADEMY'));
    $heroLogo = trim((string) ($siteSettings['hero_logo_image'] ?? ''));
    $sliders = \App\Models\Slider::query()->forLanguage($lang)->active()->orderBy('sort_order')->get();
    $stats = \App\Models\HomeStat::query()->forLanguage($lang)->active()->orderBy('sort_order')->get();
    $autoplay = max(2500, (int) ($content['autoplay_ms'] ?? 6200));
    $showStats = \App\Support\Cms\NativeBlockOptions::enabled($content, 'show_stats');
    $phone = trim((string) ($siteSettings['contact_phone'] ?? ''));
    $visibleIndexCount = max(3, $sliders->count());
    $socials = [
        ['key' => 'social_instagram', 'icon' => 'fa-brands fa-instagram', 'label' => 'Instagram'],
        ['key' => 'social_youtube', 'icon' => 'fa-brands fa-youtube', 'label' => 'YouTube'],
        ['key' => 'social_linkedin', 'icon' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'],
        ['key' => 'social_facebook', 'icon' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'],
    ];
@endphp
<section class="hero-section aa-home-hero">
    <aside class="aa-hero-rail" aria-label="{{ $siteName }} əlaqə linkləri">
        @if($phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="aa-rail-phone">{{ $phone }}</a>@else<span class="aa-rail-phone aa-rail-phone-empty"></span>@endif
        <a href="{{ $phone ? 'tel:'.preg_replace('/[^0-9+]/', '', $phone) : '#' }}" class="aa-rail-icon" aria-label="{{ $phone ?: 'Telefon' }}" @unless($phone) onclick="return false" @endunless><i class="fa-solid fa-phone"></i></a>
        @foreach($socials as $social)
            @php($url = trim((string) ($siteSettings[$social['key']] ?? '')))
            <a href="{{ $url ? \App\Support\Cms\SafeUrl::clean($url) : '#' }}" @if($url) target="_blank" rel="noopener noreferrer" @else onclick="return false" @endif class="aa-rail-icon" aria-label="{{ $social['label'] }}"><i class="{{ $social['icon'] }}"></i></a>
        @endforeach
    </aside>

    @if($heroLogo)
        <img class="aa-hero-logo-watermark" src="{{ asset(ltrim($heroLogo, '/')) }}" alt="" aria-hidden="true">
    @endif

    <div class="hero-slider" id="heroSlider" data-slider data-autoplay-ms="{{ $autoplay }}">
        @forelse($sliders as $index => $slider)
            @php($imageUrl = $slider->image_path ? asset(ltrim($slider->image_path, '/')) : '')
            <article class="hero-slide aa-hero-slide {{ $index === 0 ? 'active' : '' }}">
                <div class="aa-hero-art {{ $imageUrl ? '' : 'is-empty' }}" @if($imageUrl) style="background-image:linear-gradient(90deg,rgba(1,15,30,.97) 0%,rgba(1,15,30,.88) 35%,rgba(1,15,30,.16) 75%,rgba(1,15,30,.36) 100%),url('{{ $imageUrl }}')" @endif></div>
                <div class="container aa-hero-container">
                    <div class="aa-hero-copy">
                        <span class="aa-hero-eyebrow">{{ $siteSettings['hero_eyebrow'] ?? $siteName }}</span>
                        <h1 data-entity="slider" data-entity-id="{{ $slider->id }}" data-entity-field="title">{{ $slider->title }}</h1>
                        @if($slider->subtitle)<p class="aa-hero-subtitle" data-entity="slider" data-entity-id="{{ $slider->id }}" data-entity-field="subtitle">{{ $slider->subtitle }}</p>@endif
                        @if($slider->description)<p class="aa-hero-description" data-entity="slider" data-entity-id="{{ $slider->id }}" data-entity-field="description">{{ $slider->description }}</p>@endif
                        <div class="aa-hero-buttons">
                            @if($slider->button_1_text)<a class="aa-button aa-button-primary" href="{{ \App\Support\CleanUrl::to($slider->button_1_url ?: '#', $lang) }}"><span data-entity="slider" data-entity-id="{{ $slider->id }}" data-entity-field="button_1_text">{{ $slider->button_1_text }}</span><i class="fa-solid fa-arrow-right"></i></a>@endif
                            @if($slider->button_2_text)<a class="aa-button aa-button-outline" href="{{ \App\Support\CleanUrl::to($slider->button_2_url ?: '#', $lang) }}"><span data-entity="slider" data-entity-id="{{ $slider->id }}" data-entity-field="button_2_text">{{ $slider->button_2_text }}</span><i class="fa-solid fa-arrow-right"></i></a>@endif
                        </div>
                    </div>
                    <div class="aa-hero-index" aria-label="Slider sıra nömrələri">
                        @for($dotIndex = 0; $dotIndex < $visibleIndexCount; $dotIndex++)
                            @if($dotIndex < $sliders->count())
                                <button type="button" class="{{ $dotIndex === 0 ? 'active' : '' }}" data-slider-dot="{{ $dotIndex }}"><span>{{ str_pad((string) ($dotIndex + 1), 2, '0', STR_PAD_LEFT) }}</span></button>
                            @else
                                <span class="aa-hero-index-placeholder" aria-hidden="true">{{ str_pad((string) ($dotIndex + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            @endif
                        @endfor
                    </div>
                </div>
            </article>
        @empty
            <article class="hero-slide aa-hero-slide active"><div class="aa-hero-art is-empty"></div><div class="container aa-hero-container"><div class="aa-hero-copy"><span class="aa-hero-eyebrow">{{ $siteName }}</span><h1>{{ $siteSettings['site_name'] ?? $siteName }}</h1><p class="aa-hero-description">{{ $siteSettings['site_description'] ?? '' }}</p></div></div></article>
        @endforelse

        @if($sliders->count() > 1)
            <button class="slider-arrow slider-prev aa-slider-arrow aa-slider-prev" type="button" data-slider-prev aria-label="Əvvəlki slayd"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="slider-arrow slider-next aa-slider-arrow aa-slider-next" type="button" data-slider-next aria-label="Növbəti slayd"><i class="fa-solid fa-chevron-right"></i></button>
        @endif
    </div>

    @if($showStats && $stats->isNotEmpty())
        <div class="container aa-hero-stats-wrap"><div class="aa-hero-stats">
            @foreach($stats->take(4) as $stat)
                <div class="aa-stat-item"><i class="{{ $stat->icon_class ?: 'fa-solid fa-circle-info' }}"></i><span><strong data-entity="stat" data-entity-id="{{ $stat->id }}" data-entity-field="number_text">{{ $stat->number_text }}</strong><small data-entity="stat" data-entity-id="{{ $stat->id }}" data-entity-field="title">{{ $stat->title }}</small></span></div>
            @endforeach
        </div></div>
    @endif
</section>
