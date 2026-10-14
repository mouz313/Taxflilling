@php
    $gtmId = setting('marketing_gtm_id');
    $metaPixelId = setting('marketing_meta_pixel_id');
    $customBody = setting('marketing_custom_body_scripts');
@endphp

{{-- 1. Google Tag Manager (noscript) --}}
@if(!empty($gtmId))
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
@endif

{{-- 2. Meta Pixel (noscript) --}}
@if(!empty($metaPixelId))
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ $metaPixelId }}&ev=PageView&noscript=1"
    /></noscript>
@endif

{{-- 3. Custom Raw Body Scripts (e.g. Chatbots, Remarketing) --}}
@if(!empty($customBody))
    {!! $customBody !!}
@endif
