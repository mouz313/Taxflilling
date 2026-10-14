@php
    $ga4Id = setting('marketing_ga4_id');
    $gtmId = setting('marketing_gtm_id');
    $metaPixelId = setting('marketing_meta_pixel_id');
    $tiktokPixelId = setting('marketing_tiktok_pixel_id');
    $googleVerification = setting('seo_google_verification');
    $bingVerification = setting('seo_bing_verification');
    $customHead = setting('marketing_custom_head_scripts');
@endphp

{{-- 1. Search Console Verifications --}}
@if(!empty($googleVerification))
    <meta name="google-site-verification" content="{{ $googleVerification }}">
@endif
@if(!empty($bingVerification))
    <meta name="msvalidate.01" content="{{ $bingVerification }}">
@endif

{{-- 2. Google Tag Manager (GTM) --}}
@if(!empty($gtmId))
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ $gtmId }}');</script>
    <!-- End Google Tag Manager -->
@endif

{{-- 3. Google Analytics 4 (GA4) --}}
@if(!empty($ga4Id) && empty($gtmId))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{ $ga4Id }}');
    </script>
@endif

{{-- 4. Meta / Facebook Pixel --}}
@if(!empty($metaPixelId))
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window, document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $metaPixelId }}');
    fbq('track', 'PageView');
    </script>
    <!-- End Meta Pixel Code -->
@endif

{{-- 5. TikTok Pixel --}}
@if(!empty($tiktokPixelId))
    <!-- TikTok Pixel -->
    <script>
    !function (w, d, t) {
      w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
      ttq.load('{{ $tiktokPixelId }}');
      ttq.page();
    }(window, document, 'ttq');
    </script>
@endif

{{-- 6. Custom Raw Head Scripts --}}
@if(!empty($customHead))
    {!! $customHead !!}
@endif
