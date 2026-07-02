@php
    $document = $pageBuilderDocument ?? [];
    $context = get_defined_vars();
@endphp

{!! app(\App\AlturaPageBuilder\Rendering\VisualDocumentRenderer::class)->renderDocument($document, $context) !!}
