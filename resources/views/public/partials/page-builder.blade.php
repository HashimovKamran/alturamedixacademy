@php
    $blocks = $pageBuilderBlocks ?? collect();
    $registry = app(\App\Support\Cms\PageBlockRegistry::class);
    $sanitizer = app(\App\Support\Cms\SafeHtml::class);
    $defaults = $registry->defaults();
    $cleanSegment = fn ($value) => preg_replace('/[^a-zA-Z0-9_\-\s]/', '', (string) $value);
    $settingsFor = function ($block) use ($defaults) {
        $json = json_decode((string) $block->settings_json, true);
        $settings = array_merge($defaults, is_array($json) ? $json : []);
        if (is_array($json) && empty($json['theme'])) {
            $background = strtolower(trim((string) ($json['bg_color'] ?? '')));
            $settings['theme'] = in_array($background, ['#071728', '#061326', '#092b49'], true) ? 'dark'
                : (str_starts_with($background, '#fff3') ? 'brand' : (str_starts_with($background, '#f') && $background !== '#ffffff' ? 'muted' : 'surface'));
            $padding = (int) ($json['padding_y'] ?? 48);
            $settings['spacing'] = $padding <= 28 ? 'small' : ($padding <= 46 ? 'medium' : 'large');
        }
        return $settings;
    };
    $safeHtml = fn ($html) => $sanitizer->clean((string) $html);
    $contentFor = function ($block): array {
        $content = $block->content_json;
        if (is_string($content)) $content = json_decode($content, true);
        if (is_array($content) && $content !== []) return $content;
        if (in_array((string) $block->block_type, ['cards', 'faq'], true)) {
            return ['items' => collect(preg_split('/\r\n|\r|\n/', (string) $block->body))->filter()->map(function ($line) {
                $parts = explode('|', trim((string) $line), 2);
                return ['title' => trim($parts[0] ?? ''), 'text' => trim($parts[1] ?? '')];
            })->values()->all()];
        }
        return ['html' => (string) $block->body];
    };
    $blockClasses = function (array $settings) use ($cleanSegment) {
        $classes = [
            'pb-public-section',
            'pb-layout-' . $cleanSegment($settings['layout'] ?? 'card'),
            'pb-align-' . $cleanSegment($settings['align'] ?? 'left'),
            'pb-shadow-' . $cleanSegment($settings['shadow'] ?? 'soft'),
            'pb-anim-' . $cleanSegment($settings['animation'] ?? 'fade-up'),
            'pb-width-' . $cleanSegment($settings['max_width'] ?? 'full'),
        ];
        $classes[] = 'pb-theme-' . $cleanSegment($settings['theme'] ?? 'surface');
        return implode(' ', $classes);
    };
    $blockStyle = function (array $settings) use ($registry) {
        [$background, $text, $accent] = $registry->theme((string) ($settings['theme'] ?? 'surface'));
        $radius = max(0, min(80, (int) ($settings['radius'] ?? 24)));
        $paddingY = match ($settings['spacing'] ?? 'large') {'small' => 24, 'medium' => 40, default => 56};
        return '--pb-bg:' . $background
            . ';--pb-text:' . $text
            . ';--pb-accent:' . $accent
            . ';--pb-radius:' . $radius . 'px;--pb-py:' . $paddingY . 'px;';
    };
@endphp

@if($blocks->isNotEmpty())
<div class="pb-public-wrap">
    @foreach($blocks as $block)
        @php
            $settings = $settingsFor($block);
            $type = (string) $block->block_type;
            $image = trim((string) $block->image_path);
            $buttonText = trim((string) $block->button_text);
            $buttonUrl = \App\Support\Cms\SafeUrl::clean($block->button_url);
            $content = $contentFor($block);
        @endphp
        <section class="{{ $blockClasses($settings) }}" style="{{ $blockStyle($settings) }}">
            <div class="container">
                <div class="pb-public-box pb-type-{{ $cleanSegment($type) }}">
                    @if($type === 'hero')
                        <div class="pb-hero-grid">
                            <div class="pb-content">
                                @if($block->subtitle)<div class="pb-kicker">{{ $block->subtitle }}</div>@endif
                                @if($block->title)<h2>{{ $block->title }}</h2>@endif
                                @if($block->body)<div class="pb-body">{!! $safeHtml($block->body) !!}</div>@endif
                                @if($buttonText !== '')<a class="pb-btn" href="{{ $buttonUrl }}">{{ $buttonText }} <i class="fa-solid fa-arrow-right"></i></a>@endif
                            </div>
                            @if($image !== '')
                                <div class="pb-media"><img src="{{ asset(ltrim($image, '/')) }}" alt="{{ $block->title }}"></div>
                            @endif
                        </div>
                    @elseif($type === 'image_text')
                        <div class="pb-split {{ ($settings['image_position'] ?? 'right') === 'left' ? 'image-left' : 'image-right' }}">
                            @if($image !== '')
                                <div class="pb-media"><img src="{{ asset(ltrim($image, '/')) }}" alt="{{ $block->title }}"></div>
                            @endif
                            <div class="pb-content">
                                @if($block->subtitle)<div class="pb-kicker">{{ $block->subtitle }}</div>@endif
                                @if($block->title)<h2>{{ $block->title }}</h2>@endif
                                @if($block->body)<div class="pb-body">{!! $safeHtml($block->body) !!}</div>@endif
                                @if($buttonText !== '')<a class="pb-btn" href="{{ $buttonUrl }}">{{ $buttonText }} <i class="fa-solid fa-arrow-right"></i></a>@endif
                            </div>
                        </div>
                    @elseif($type === 'cards')
                        @if($block->subtitle)<div class="pb-kicker">{{ $block->subtitle }}</div>@endif
                        @if($block->title)<h2>{{ $block->title }}</h2>@endif
                        <div class="pb-cards">
                            @foreach(($content['items'] ?? []) as $item)
                                    <div class="pb-card">
                                        <i class="fa-solid fa-circle-check"></i>
                                        <strong>{{ $item['title'] ?? '' }}</strong>
                                        @if(trim((string)($item['text'] ?? '')) !== '')<p>{{ $item['text'] }}</p>@endif
                                    </div>
                            @endforeach
                        </div>
                    @elseif($type === 'faq')
                        @if($block->subtitle)<div class="pb-kicker">{{ $block->subtitle }}</div>@endif
                        @if($block->title)<h2>{{ $block->title }}</h2>@endif
                        <div class="pb-faq">
                            @foreach(($content['items'] ?? []) as $item)
                                    <details>
                                        <summary>{{ $item['title'] ?? '' }}</summary>
                                        @if(trim((string)($item['text'] ?? '')) !== '')<p>{{ $item['text'] }}</p>@endif
                                    </details>
                            @endforeach
                        </div>
                    @elseif($type === 'cta')
                        <div class="pb-cta">
                            @if($block->subtitle)<div class="pb-kicker">{{ $block->subtitle }}</div>@endif
                            @if($block->title)<h2>{{ $block->title }}</h2>@endif
                            @if($block->body)<div class="pb-body">{!! $safeHtml($block->body) !!}</div>@endif
                            @if($buttonText !== '')<a class="pb-btn" href="{{ $buttonUrl }}">{{ $buttonText }} <i class="fa-solid fa-arrow-right"></i></a>@endif
                        </div>
                    @else
                        @if($block->subtitle)<div class="pb-kicker">{{ $block->subtitle }}</div>@endif
                        @if($block->title)<h2>{{ $block->title }}</h2>@endif
                        @if($block->body)<div class="pb-body">{!! $safeHtml($block->body) !!}</div>@endif
                        @if($buttonText !== '')<a class="pb-btn" href="{{ $buttonUrl }}">{{ $buttonText }} <i class="fa-solid fa-arrow-right"></i></a>@endif
                    @endif
                </div>
            </div>
        </section>
    @endforeach
</div>
@endif
