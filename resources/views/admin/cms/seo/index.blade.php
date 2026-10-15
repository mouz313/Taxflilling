<x-layouts.app header="CMS · SEO & Digital Marketing">
    <div
        class="w-full space-y-4"
        x-data="{
            activeTab: '{{ session('tab', $tab ?? 'seo') }}',
            metaTitle: '{{ addslashes($settings['seo_meta_title'] ?? $firmName . ' | Certified Tax Consultant & FBR IRIS Filing Pakistan') }}',
            metaDescription: '{{ addslashes($settings['seo_meta_description'] ?? 'Fastest online Tax Filing in Pakistan. Individual & Business NTN Registration, Active Taxpayer List (ATL) verification, and SECP Company Incorporation.') }}',
            ogTitle: '{{ addslashes($settings['seo_og_title'] ?? '') }}',
            ogDescription: '{{ addslashes($settings['seo_og_description'] ?? '') }}',
            canonicalUrl: '{{ addslashes($settings['seo_canonical_url'] ?? url('/')) }}',
            currentOgImage: '{{ !empty($settings['seo_og_image']) ? (str_starts_with($settings['seo_og_image'], 'http') ? $settings['seo_og_image'] : asset('storage/' . $settings['seo_og_image'])) : '' }}'
        }"
    >
        <!-- 1. Top AdminLTE Card: Command Bar & Health Status -->
        <div class="card card-outline card-primary w-full">
            <div class="card-header flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <h3 class="card-title text-sm font-bold text-[#212529] flex items-center gap-2">
                        <i class="fas fa-chart-line text-[#007bff]"></i>
                        <span>Search Engine Optimization &amp; Ad Tracking Desk</span>
                    </h3>
                    <p class="text-xs text-[#6c757d] mt-0.5 m-0">
                        Google search rankings, WhatsApp/Facebook social share previews, Meta Pixel, Google Analytics 4, aur XML sitemaps manage karein.
                    </p>
                </div>

                <div class="card-tools flex items-center gap-2">
                    <a
                        href="{{ route('sitemap') }}"
                        target="_blank"
                        class="btn btn-xs btn-default font-semibold inline-flex items-center gap-1"
                        title="View Google XML Sitemap"
                    >
                        <i class="fas fa-sitemap text-[#007bff]"></i>
                        <span>XML Sitemap</span>
                    </a>
                    <a
                        href="{{ url('/robots.txt') }}"
                        target="_blank"
                        class="btn btn-xs btn-default font-semibold inline-flex items-center gap-1"
                        title="View Robots.txt"
                    >
                        <i class="fas fa-robot text-[#6c757d]"></i>
                        <span>Robots.txt</span>
                    </a>
                    <a
                        href="{{ route('home') }}"
                        target="_blank"
                        class="btn btn-xs btn-primary font-bold inline-flex items-center gap-1"
                    >
                        <i class="fas fa-globe"></i>
                        <span>View Website</span>
                    </a>
                </div>
            </div>

            <!-- SEO Health Bar -->
            <div class="card-body bg-[#f8f9fa] border-t border-[#dee2e6] py-3 px-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-2">
                    <div class="flex items-center space-x-2 text-xs">
                        <span class="font-bold text-[#495057]">SEO Readiness Score:</span>
                        <span class="badge {{ $readinessScore >= 80 ? 'badge-success' : ($readinessScore >= 50 ? 'badge-warning' : 'badge-danger') }} font-bold">
                            {{ $readinessScore }}% Complete
                        </span>
                        <span class="text-[#6c757d] hidden sm:inline">
                            {{ $readinessScore >= 80 ? 'Optimized for organic search engines and paid ads.' : 'Complete missing tags below to rank higher on Google.' }}
                        </span>
                    </div>

                    <div class="w-full md:w-56 bg-[#e9ecef] rounded-full h-2.5 overflow-hidden">
                        <div class="h-full {{ $readinessScore >= 80 ? 'bg-[#28a745]' : ($readinessScore >= 50 ? 'bg-[#ffc107]' : 'bg-[#dc3545]') }}" style="width: {{ $readinessScore }}%"></div>
                    </div>
                </div>

                <div class="mt-2 flex flex-wrap gap-1 text-[11px]">
                    <span class="badge {{ $checklist['meta_title'] ? 'badge-success' : 'badge-secondary' }}">{{ $checklist['meta_title'] ? '✓' : '○' }} Meta Title</span>
                    <span class="badge {{ $checklist['meta_description'] ? 'badge-success' : 'badge-secondary' }}">{{ $checklist['meta_description'] ? '✓' : '○' }} Meta Description</span>
                    <span class="badge {{ $checklist['og_image'] ? 'badge-success' : 'badge-secondary' }}">{{ $checklist['og_image'] ? '✓' : '○' }} Social Share Image</span>
                    <span class="badge {{ $checklist['google_analytics'] ? 'badge-success' : 'badge-secondary' }}">{{ $checklist['google_analytics'] ? '✓' : '○' }} Google Analytics</span>
                    <span class="badge {{ $checklist['meta_pixel'] ? 'badge-success' : 'badge-secondary' }}">{{ $checklist['meta_pixel'] ? '✓' : '○' }} Meta Pixel</span>
                    <span class="badge {{ $checklist['verification'] ? 'badge-success' : 'badge-secondary' }}">{{ $checklist['verification'] ? '✓' : '○' }} Search Console Verified</span>
                </div>
            </div>
        </div>

        @if(session('status'))
            <div class="p-3 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded text-xs font-semibold flex items-center justify-between shadow-2xs">
                <span>✅ {{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-[#155724] font-bold">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded text-xs space-y-1">
                <strong class="font-bold block">Validation Errors:</strong>
                <ul class="list-disc pl-5 m-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- 2. Main AdminLTE Card with Tabs -->
        <div class="card card-primary card-outline card-outline-tabs w-full">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs flex flex-wrap border-b border-[#dee2e6] bg-[#f8f9fa] px-3 pt-2">
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'seo'"
                            :class="activeTab === 'seo' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-search mr-1.5"></i>
                            <span>Google Search SEO</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'social'"
                            :class="activeTab === 'social' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fab fa-whatsapp mr-1.5 text-[#28a745]"></i>
                            <span>WhatsApp &amp; Social Share</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'tracking'"
                            :class="activeTab === 'tracking' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-chart-pie mr-1.5 text-[#17a2b8]"></i>
                            <span>Ad Pixels &amp; Analytics</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'schema'"
                            :class="activeTab === 'schema' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-map-marker-alt mr-1.5 text-[#dc3545]"></i>
                            <span>Local SEO &amp; Schema</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'scripts'"
                            :class="activeTab === 'scripts' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-code mr-1.5 text-[#6c757d]"></i>
                            <span>Custom Scripts</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-6">
                <!-- TAB 1: GOOGLE SEARCH ENGINE OPTIMIZATION -->
                <div x-show="activeTab === 'seo'" x-cloak class="space-y-5">
                    <!-- Live Google SERP Preview Callout -->
                    <div class="callout callout-info space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-[#117a8b] uppercase tracking-wider">
                            <span>Google Search Result Snippet Preview</span>
                            <span class="text-[11px] font-normal lowercase">desktop preview</span>
                        </div>
                        <div class="bg-white p-3 rounded border border-[#dee2e6] max-w-2xl font-sans">
                            <div class="flex items-center space-x-1.5 text-xs text-[#6c757d] mb-0.5">
                                <span class="w-4 h-4 rounded-full bg-[#007bff] text-white flex items-center justify-center text-[9px] font-bold">T</span>
                                <span class="font-medium text-[#212529] truncate" x-text="canonicalUrl"></span>
                                <span class="text-[#adb5bd]">&rsaquo; home</span>
                            </div>
                            <h4 class="text-[#1a0dab] hover:underline text-base font-medium leading-snug cursor-pointer truncate m-0" x-text="metaTitle || '{{ $firmName }} | Certified Tax Consultants'"></h4>
                            <p class="text-[#4d5156] text-xs mt-1 leading-relaxed line-clamp-2 m-0" x-text="metaDescription || 'Fastest online Tax Filing in Pakistan...'"></p>
                        </div>
                    </div>

                    <!-- Form -->
                    <form method="POST" action="{{ route('admin.cms.seo.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="seo">

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-[#495057] m-0">Meta Title <span class="text-[#dc3545]">*</span></label>
                                <span class="text-[11px] font-mono" :class="metaTitle.length > 60 ? 'text-[#ffc107] font-bold' : 'text-[#6c757d]'">
                                    <span x-text="metaTitle.length"></span> / 60 characters
                                </span>
                            </div>
                            <input
                                type="text"
                                name="seo_meta_title"
                                x-model="metaTitle"
                                required
                                class="form-control text-xs"
                                placeholder="e.g. TaxFiler Pakistan | Certified Tax Consultants & FBR IRIS Filing"
                            />
                            <p class="text-[11px] text-[#6c757d] mt-1 m-0">Primary title tag displayed on search engines.</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="text-xs font-bold text-[#495057] m-0">Meta Description <span class="text-[#dc3545]">*</span></label>
                                <span class="text-[11px] font-mono" :class="metaDescription.length > 160 ? 'text-[#ffc107] font-bold' : 'text-[#6c757d]'">
                                    <span x-text="metaDescription.length"></span> / 160 characters
                                </span>
                            </div>
                            <textarea
                                name="seo_meta_description"
                                x-model="metaDescription"
                                rows="3"
                                required
                                class="form-control text-xs"
                                placeholder="Fastest online Tax Filing in Pakistan..."
                            ></textarea>
                            <p class="text-[11px] text-[#6c757d] mt-1 m-0">Summary snippet that appears beneath title on search results.</p>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-[#495057] block mb-1">Meta Keywords</label>
                            <textarea
                                name="seo_meta_keywords"
                                rows="2"
                                class="form-control text-xs font-mono"
                            >{{ $settings['seo_meta_keywords'] ?? 'tax return filing pakistan, ntn registration fbr, atl status check, fbr iris login, secp company registration' }}</textarea>
                            <p class="text-[11px] text-[#6c757d] mt-1 m-0">Comma-separated keywords for indexing.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-[#dee2e6]">
                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">Robots Indexing</label>
                                <select
                                    name="seo_robots"
                                    class="form-control text-xs"
                                >
                                    <option value="index, follow" {{ ($settings['seo_robots'] ?? 'index, follow') === 'index, follow' ? 'selected' : '' }}>Index, Follow (Recommended for Production)</option>
                                    <option value="noindex, nofollow" {{ ($settings['seo_robots'] ?? '') === 'noindex, nofollow' ? 'selected' : '' }}>NoIndex, NoFollow (Hide from search engines)</option>
                                    <option value="index, nofollow" {{ ($settings['seo_robots'] ?? '') === 'index, nofollow' ? 'selected' : '' }}>Index, NoFollow</option>
                                    <option value="noindex, follow" {{ ($settings['seo_robots'] ?? '') === 'noindex, follow' ? 'selected' : '' }}>NoIndex, Follow</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">Canonical Base URL</label>
                                <input
                                    type="url"
                                    name="seo_canonical_url"
                                    x-model="canonicalUrl"
                                    class="form-control text-xs font-mono"
                                    placeholder="https://taxfiler.pk"
                                />
                            </div>
                        </div>

                        <!-- Verification Tokens -->
                        <div class="pt-3 border-t border-[#dee2e6]">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#212529] mb-2">Search Console Verification Keys</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-[#495057] block mb-1">Google Search Console Verification Token</label>
                                    <input
                                        type="text"
                                        name="seo_google_verification"
                                        value="{{ $settings['seo_google_verification'] ?? '' }}"
                                        class="form-control text-xs font-mono"
                                        placeholder="e.g. google-site-verification token"
                                    />
                                </div>

                                <div>
                                    <label class="text-xs font-semibold text-[#495057] block mb-1">Bing Webmaster Tools Verification Token</label>
                                    <input
                                        type="text"
                                        name="seo_bing_verification"
                                        value="{{ $settings['seo_bing_verification'] ?? '' }}"
                                        class="form-control text-xs font-mono"
                                        placeholder="e.g. Bing msvalidate.01 token"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Search Engine Settings &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: WHATSAPP & SOCIAL SHARE -->
                <div x-show="activeTab === 'social'" x-cloak class="space-y-5">
                    <!-- WhatsApp Link Preview Bubble -->
                    <div class="callout callout-success space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#155724] block">WhatsApp &amp; Facebook Share Preview</span>
                        <div class="max-w-md p-3 rounded bg-white border border-[#c3e6cb] space-y-2">
                            <div class="rounded overflow-hidden bg-[#343a40] border border-[#dee2e6] relative aspect-[1.91/1] flex items-center justify-center">
                                <template x-if="currentOgImage">
                                    <img :src="currentOgImage" alt="OG Preview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!currentOgImage">
                                    <div class="text-center p-4">
                                        <i class="fas fa-briefcase text-3xl text-white/50 block mb-1"></i>
                                        <span class="text-xs font-bold text-white block">{{ $firmName }}</span>
                                        <span class="text-[10px] text-[#adb5bd]">Upload social share banner image below</span>
                                    </div>
                                </template>
                            </div>

                            <div class="p-2 bg-[#f8f9fa] rounded border border-[#dee2e6] text-left">
                                <span class="text-[10px] font-bold text-[#6c757d] uppercase tracking-wider block" x-text="canonicalUrl.replace('http://', '').replace('https://', '')"></span>
                                <h4 class="text-xs font-bold text-[#212529] leading-snug mt-0.5 line-clamp-1 m-0" x-text="ogTitle || metaTitle"></h4>
                                <p class="text-[11px] text-[#6c757d] leading-snug mt-0.5 line-clamp-2 m-0" x-text="ogDescription || metaDescription"></p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.cms.seo.update') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="social">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">Social Share Title (OG Title)</label>
                                <input
                                    type="text"
                                    name="seo_og_title"
                                    x-model="ogTitle"
                                    value="{{ $settings['seo_og_title'] ?? '' }}"
                                    class="form-control text-xs"
                                    placeholder="Leave empty to use Meta Title"
                                />
                            </div>

                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">Twitter / X Card Type</label>
                                <select
                                    name="seo_twitter_card"
                                    class="form-control text-xs"
                                >
                                    <option value="summary_large_image" {{ ($settings['seo_twitter_card'] ?? 'summary_large_image') === 'summary_large_image' ? 'selected' : '' }}>summary_large_image (Large Hero Banner)</option>
                                    <option value="summary" {{ ($settings['seo_twitter_card'] ?? '') === 'summary' ? 'selected' : '' }}>summary (Small thumbnail)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-[#495057] block mb-1">Social Share Description (OG Description)</label>
                            <textarea
                                name="seo_og_description"
                                x-model="ogDescription"
                                rows="2"
                                class="form-control text-xs"
                                placeholder="Leave empty to use Meta Description"
                            >{{ $settings['seo_og_description'] ?? '' }}</textarea>
                        </div>

                        <div class="pt-3 border-t border-[#dee2e6]">
                            <label class="text-xs font-bold text-[#495057] block mb-1">Social Share Banner Image (1200 x 630 px)</label>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                <div class="w-28 h-16 rounded bg-[#f8f9fa] border border-[#dee2e6] overflow-hidden shrink-0 flex items-center justify-center">
                                    @if(!empty($settings['seo_og_image']))
                                        <img src="{{ str_starts_with($settings['seo_og_image'], 'http') ? $settings['seo_og_image'] : asset('storage/' . $settings['seo_og_image']) }}" alt="Current OG" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-xs text-[#adb5bd]">No Image</span>
                                    @endif
                                </div>
                                <div class="space-y-1.5 flex-1 w-full">
                                    <input
                                        type="file"
                                        name="seo_og_image_file"
                                        accept="image/png,image/jpeg,image/webp"
                                        class="form-control form-control-sm text-xs"
                                    />
                                    <input
                                        type="url"
                                        name="seo_og_image_url"
                                        value="{{ str_starts_with($settings['seo_og_image'] ?? '', 'http') ? $settings['seo_og_image'] : '' }}"
                                        placeholder="Or paste external banner image URL..."
                                        class="form-control form-control-sm text-xs font-mono"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Social Media Profiles -->
                        <div class="pt-3 border-t border-[#dee2e6] space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#212529]">Social Media Profiles</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-[#495057] block mb-1">Twitter / X Handle</label>
                                    <input type="text" name="seo_twitter_handle" value="{{ $settings['seo_twitter_handle'] ?? '@TaxFilerPk' }}" class="form-control text-xs">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#495057] block mb-1">Facebook Business Page</label>
                                    <input type="url" name="seo_facebook_url" value="{{ $settings['seo_facebook_url'] ?? '' }}" class="form-control text-xs">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#495057] block mb-1">LinkedIn Corporate Page</label>
                                    <input type="url" name="seo_linkedin_url" value="{{ $settings['seo_linkedin_url'] ?? '' }}" class="form-control text-xs">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#495057] block mb-1">YouTube Channel</label>
                                    <input type="url" name="seo_youtube_url" value="{{ $settings['seo_youtube_url'] ?? '' }}" class="form-control text-xs">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Social Sharing Settings &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 3: AD PIXELS & ANALYTICS -->
                <div x-show="activeTab === 'tracking'" x-cloak class="space-y-5">
                    <form method="POST" action="{{ route('admin.cms.seo.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="tracking">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">Google Analytics 4 (GA4) Measurement ID</label>
                                <input
                                    type="text"
                                    name="marketing_ga4_id"
                                    value="{{ $settings['marketing_ga4_id'] ?? '' }}"
                                    class="form-control text-xs font-mono"
                                    placeholder="G-XXXXXXXXXX"
                                />
                            </div>

                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">Google Tag Manager (GTM) Container ID</label>
                                <input
                                    type="text"
                                    name="marketing_gtm_id"
                                    value="{{ $settings['marketing_gtm_id'] ?? '' }}"
                                    class="form-control text-xs font-mono"
                                    placeholder="GTM-XXXXXXX"
                                />
                            </div>

                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">Meta (Facebook &amp; Instagram) Pixel ID</label>
                                <input
                                    type="text"
                                    name="marketing_meta_pixel_id"
                                    value="{{ $settings['marketing_meta_pixel_id'] ?? '' }}"
                                    class="form-control text-xs font-mono"
                                    placeholder="e.g. 123456789012345"
                                />
                            </div>

                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">TikTok Pixel ID</label>
                                <input
                                    type="text"
                                    name="marketing_tiktok_pixel_id"
                                    value="{{ $settings['marketing_tiktok_pixel_id'] ?? '' }}"
                                    class="form-control text-xs font-mono"
                                    placeholder="e.g. CXXXXXXXXXXXXXXX"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-xs font-bold text-[#495057] block mb-1">Google Ads Conversion ID</label>
                                <input
                                    type="text"
                                    name="marketing_google_ads_id"
                                    value="{{ $settings['marketing_google_ads_id'] ?? '' }}"
                                    class="form-control text-xs font-mono"
                                    placeholder="AW-XXXXXXXXX"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Ad Pixels &amp; Tracking &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 4: LOCAL SEO & SCHEMA -->
                <div x-show="activeTab === 'schema'" x-cloak class="space-y-5">
                    <form method="POST" action="{{ route('admin.cms.seo.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="schema">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">Business Classification (Schema.org Type)</label>
                                <select
                                    name="schema_business_type"
                                    class="form-control text-xs"
                                >
                                    <option value="TaxConsultant" {{ ($settings['schema_business_type'] ?? 'TaxConsultant') === 'TaxConsultant' ? 'selected' : '' }}>TaxConsultant (Official Tax Practice)</option>
                                    <option value="AccountingService" {{ ($settings['schema_business_type'] ?? '') === 'AccountingService' ? 'selected' : '' }}>AccountingService (Chartered Accountants / Audit)</option>
                                    <option value="LegalService" {{ ($settings['schema_business_type'] ?? '') === 'LegalService' ? 'selected' : '' }}>LegalService (Corporate Advocates / SECP Law)</option>
                                    <option value="FinancialService" {{ ($settings['schema_business_type'] ?? '') === 'FinancialService' ? 'selected' : '' }}>FinancialService (Wealth Management)</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs font-bold text-[#495057] block mb-1">Fee &amp; Price Range Indication</label>
                                <input
                                    type="text"
                                    name="schema_price_range"
                                    value="{{ $settings['schema_price_range'] ?? 'PKR 1,500 - 50,000' }}"
                                    class="form-control text-xs"
                                    placeholder="e.g. PKR 1,500 - 50,000"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-bold text-[#495057] block mb-1">Jurisdiction &amp; Area Served</label>
                            <input
                                type="text"
                                name="schema_area_served"
                                value="{{ $settings['schema_area_served'] ?? 'Pakistan, Karachi, Lahore, Islamabad, Rawalpindi, Faisalabad' }}"
                                class="form-control text-xs"
                            />
                        </div>

                        <!-- Geo Coordinates -->
                        <div class="pt-3 border-t border-[#dee2e6]">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#212529] mb-2">Office Geo Coordinates (For Google Maps Local 3-Pack)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-semibold text-[#495057] block mb-1">Latitude</label>
                                    <input
                                        type="text"
                                        name="schema_geo_latitude"
                                        value="{{ $settings['schema_geo_latitude'] ?? '31.5204' }}"
                                        class="form-control text-xs font-mono"
                                    />
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-[#495057] block mb-1">Longitude</label>
                                    <input
                                        type="text"
                                        name="schema_geo_longitude"
                                        value="{{ $settings['schema_geo_longitude'] ?? '74.3587' }}"
                                        class="form-control text-xs font-mono"
                                    />
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Local SEO &amp; Schema &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 5: CUSTOM MARKETING SCRIPTS -->
                <div x-show="activeTab === 'scripts'" x-cloak class="space-y-5">
                    <div class="p-3 bg-amber-50 border border-amber-300 rounded-lg text-amber-900 text-xs flex items-start gap-2">
                        <span class="text-base font-bold">⚠️</span>
                        <div>
                            <strong>Security Alert:</strong> Yahan sirf trusted code (e.g. Google Analytics, Clarity, Meta Pixel) darj karein. Ghair-tasdeeq shuda ya anjan scripts website ki security aur integrity ko mutasir kar sakti hain.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.cms.seo.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="scripts">

                        <div>
                            <label class="text-xs font-bold text-[#495057] block mb-1">Custom Header Scripts (&lt;head&gt; injection)</label>
                            <textarea
                                name="marketing_custom_head_scripts"
                                rows="5"
                                class="form-control text-xs font-mono bg-[#1e293b] text-[#4ade80]"
                                placeholder="<!-- Microsoft Clarity, Hotjar, or Custom Meta Tags -->"
                            >{{ $settings['marketing_custom_head_scripts'] ?? '' }}</textarea>
                        </div>

                        <div class="pt-3 border-t border-[#dee2e6]">
                            <label class="text-xs font-bold text-[#495057] block mb-1">Custom Body Scripts (&lt;body&gt; footer injection)</label>
                            <textarea
                                name="marketing_custom_body_scripts"
                                rows="5"
                                class="form-control text-xs font-mono bg-[#1e293b] text-[#4ade80]"
                                placeholder="<!-- Live Chat Widgets, Tawk.to, WhatsApp floating widgets -->"
                            >{{ $settings['marketing_custom_body_scripts'] ?? '' }}</textarea>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Custom Scripts &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
