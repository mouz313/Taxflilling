<x-layouts.app header="System & Firm Settings">
    <div class="w-full space-y-4" x-data="{ activeTab: '{{ session('tab', $tab ?? 'profile') }}' }">
        <!-- Top Status Alerts -->
        @if(session('status'))
            <div class="p-3 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded text-xs font-semibold flex items-center justify-between shadow-2xs">
                <span>✅ {{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-[#155724] font-bold">&times;</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-3 bg-[#f8d7da] text-[#721c24] border border-[#f5c6cb] rounded text-xs space-y-1">
                <strong class="font-bold block">Please fix the following errors:</strong>
                <ul class="list-disc pl-5 m-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main AdminLTE Card with Tabs -->
        <div class="card card-primary card-outline card-outline-tabs w-full">
            <!-- Card Header with Nav Tabs -->
            <div class="card-header p-0 border-bottom-0 flex flex-col sm:flex-row sm:items-center sm:justify-between bg-[#f8f9fa] border-b border-[#dee2e6]">
                <ul class="nav nav-tabs flex flex-wrap border-b-0 px-3 pt-2">
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'profile'"
                            :class="activeTab === 'profile' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-user-shield mr-1.5"></i>
                            <span>Admin Profile &amp; Security</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'branding'"
                            :class="activeTab === 'branding' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-building mr-1.5"></i>
                            <span>Firm Identity &amp; Branding</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'gateways'"
                            :class="activeTab === 'gateways' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-credit-card mr-1.5"></i>
                            <span>Payment Gateways &amp; Banks</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'email'"
                            :class="activeTab === 'email' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fas fa-envelope mr-1.5"></i>
                            <span>Email SMTP</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button
                            type="button"
                            @click="activeTab = 'firebase'"
                            :class="activeTab === 'firebase' ? 'active bg-white text-[#007bff] border-t-2 border-t-[#007bff] border-x border-[#dee2e6]' : 'text-[#495057]'"
                            class="nav-link py-2.5 px-4 font-bold text-xs"
                        >
                            <i class="fab fa-google mr-1.5 text-[#ea4335]"></i>
                            <span>Google Login</span>
                        </button>
                    </li>
                </ul>

                <div class="px-3 py-2 sm:py-0">
                    <a
                        href="{{ route('admin.cms.seo.index') }}"
                        class="btn btn-xs btn-outline-primary font-bold inline-flex items-center gap-1"
                    >
                        <i class="fas fa-chart-line"></i>
                        <span>SEO &amp; Marketing Settings &rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Card Body with Tabs Content -->
            <div class="card-body p-6">
                <!-- 1. PROFILE & PASSWORD TAB -->
                <div x-show="activeTab === 'profile'" x-cloak class="space-y-5">
                    <div class="border-b border-[#dee2e6] pb-2">
                        <h4 class="text-xs font-bold text-[#212529] uppercase tracking-wider m-0">Admin Profile &amp; Login Credentials</h4>
                        <p class="text-xs text-[#6c757d] mt-0.5 m-0">Manage personal credentials, contact info, and profile image.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="profile">

                        <!-- Avatar & Identity -->
                        <div class="flex items-center space-x-4">
                            <div class="relative w-14 h-14 rounded-full bg-[#f8f9fa] border border-[#dee2e6] flex items-center justify-center overflow-hidden shrink-0">
                                @if($user->profile_photo_path)
                                    <img src="{{ asset('storage/' . $user->profile_photo_path) }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    <span class="text-xl font-bold text-[#6c757d]">{{ substr($user->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Update Profile Picture</label>
                                <input
                                    type="file"
                                    name="avatar"
                                    accept="image/png, image/jpeg, image/jpg"
                                    class="form-control form-control-sm text-xs"
                                >
                                <span class="text-[10px] text-[#6c757d] mt-0.5 block">Supported: JPG, PNG (Max 2MB)</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Full Name *</label>
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $user->name) }}"
                                    required
                                    class="form-control text-xs"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Email Address (Login ID)</label>
                                <input
                                    type="email"
                                    value="{{ $user->email }}"
                                    disabled
                                    class="form-control text-xs bg-[#e9ecef] cursor-not-allowed"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Mobile / WhatsApp</label>
                                <input
                                    type="text"
                                    name="phone"
                                    value="{{ old('phone', $user->phone) }}"
                                    placeholder="+92 300 1234567"
                                    class="form-control text-xs font-mono"
                                >
                            </div>
                        </div>

                        <!-- Password Change Section -->
                        <div class="border-t border-[#dee2e6] pt-3 space-y-2">
                            <h5 class="text-xs font-bold text-[#212529] uppercase tracking-wider m-0">Change Password (Leave empty to keep current)</h5>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">Current Password</label>
                                    <input
                                        type="password"
                                        name="current_password"
                                        placeholder="••••••••"
                                        class="form-control text-xs"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">New Password</label>
                                    <input
                                        type="password"
                                        name="new_password"
                                        placeholder="Min 8 characters"
                                        class="form-control text-xs"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">Confirm New Password</label>
                                    <input
                                        type="password"
                                        name="new_password_confirmation"
                                        placeholder="Repeat new password"
                                        class="form-control text-xs"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Profile Settings &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 2. FIRM & BRANDING TAB -->
                <div x-show="activeTab === 'branding'" x-cloak class="space-y-5">
                    <div class="border-b border-[#dee2e6] pb-2">
                        <h4 class="text-xs font-bold text-[#212529] uppercase tracking-wider m-0">Tax Advisory Firm &amp; Corporate Branding</h4>
                        <p class="text-xs text-[#6c757d] mt-0.5 m-0">Firm identity displayed on client portal, landing page, and invoices.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.company.update') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="branding">

                        <!-- Logo Section -->
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded bg-[#f8f9fa] border border-[#dee2e6] flex items-center justify-center overflow-hidden shrink-0 p-1">
                                @if($consultancy?->logo_path)
                                    <img src="{{ asset('storage/' . $consultancy->logo_path) }}" alt="Logo" class="max-h-full max-w-full object-contain">
                                @else
                                    <i class="fas fa-landmark text-2xl text-[#6c757d]"></i>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Firm Logo</label>
                                <input
                                    type="file"
                                    name="logo"
                                    accept="image/*"
                                    class="form-control form-control-sm text-xs"
                                >
                                <span class="text-[10px] text-[#6c757d] mt-0.5 block">PNG or SVG with transparent background recommended.</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Tax Firm Legal Name *</label>
                                <input
                                    type="text"
                                    name="firm_name"
                                    value="{{ old('firm_name', setting('firm_name', $consultancy?->name)) }}"
                                    required
                                    placeholder="e.g. Tax Consultants & Corporate Advisors"
                                    class="form-control text-xs font-semibold"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Tagline / Subtitle</label>
                                <input
                                    type="text"
                                    name="firm_tagline"
                                    value="{{ old('firm_tagline', setting('firm_tagline')) }}"
                                    placeholder="e.g. Pakistan's Leading Tax & Corporate Filing Partner"
                                    class="form-control text-xs"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">FBR NTN Registration No.</label>
                                <input
                                    type="text"
                                    name="ntn_registration_no"
                                    value="{{ old('ntn_registration_no', $consultancy?->ntn_registration_no) }}"
                                    placeholder="e.g. 7861234-5"
                                    class="form-control text-xs font-mono"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Official WhatsApp Support No.</label>
                                <input
                                    type="text"
                                    name="firm_whatsapp"
                                    value="{{ old('firm_whatsapp', setting('firm_whatsapp', $consultancy?->phone)) }}"
                                    placeholder="+92 300 1234567"
                                    class="form-control text-xs font-mono"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Official Support Email</label>
                                <input
                                    type="email"
                                    name="firm_email"
                                    value="{{ old('firm_email', setting('firm_email', $consultancy?->email)) }}"
                                    placeholder="support@taxconsultants.pk"
                                    class="form-control text-xs"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Office Contact Phone</label>
                                <input
                                    type="text"
                                    name="firm_phone"
                                    value="{{ old('firm_phone', setting('firm_phone', $consultancy?->phone)) }}"
                                    placeholder="+92 21 34567890"
                                    class="form-control text-xs"
                                >
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-[#495057] mb-1">Office Physical Address</label>
                                <textarea
                                    name="firm_address"
                                    rows="2"
                                    placeholder="Suite / Floor, Building, Commercial Area, City..."
                                    class="form-control text-xs"
                                >{{ old('firm_address', setting('firm_address', $consultancy?->address)) }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Firm Branding &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 3. PAYMENT GATEWAYS & ACCOUNTS TAB -->
                <div x-show="activeTab === 'gateways'" x-cloak class="space-y-5">
                    <div class="border-b border-[#dee2e6] pb-2">
                        <h4 class="text-xs font-bold text-[#212529] uppercase tracking-wider m-0">Payment Gateways &amp; Bank Accounts</h4>
                        <p class="text-xs text-[#6c757d] mt-0.5 m-0">Details shown to clients when making fee payments (Bank Transfer, Raast, JazzCash, EasyPaisa).</p>
                    </div>

                    <form method="POST" action="{{ route('settings.company.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="gateways">

                        <!-- Bank Transfer Details -->
                        <div class="callout callout-info space-y-3">
                            <h5 class="text-xs font-bold text-[#117a8b] uppercase tracking-wider m-0 flex items-center gap-1.5">
                                <i class="fas fa-university"></i>
                                <span>Official Bank Transfer (Meezan / HBL / UBL)</span>
                            </h5>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">Bank Name</label>
                                    <input
                                        type="text"
                                        name="bank_name"
                                        value="{{ old('bank_name', setting('bank_name')) }}"
                                        placeholder="e.g. Meezan Bank Limited"
                                        class="form-control text-xs"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">Account Title</label>
                                    <input
                                        type="text"
                                        name="bank_title"
                                        value="{{ old('bank_title', setting('bank_title')) }}"
                                        placeholder="e.g. Tax Consultants PK Pvt Ltd"
                                        class="form-control text-xs"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">Account Number</label>
                                    <input
                                        type="text"
                                        name="bank_account"
                                        value="{{ old('bank_account', setting('bank_account')) }}"
                                        placeholder="e.g. 01020304050607"
                                        class="form-control text-xs font-mono"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">IBAN Number</label>
                                    <input
                                        type="text"
                                        name="bank_iban"
                                        value="{{ old('bank_iban', setting('bank_iban')) }}"
                                        placeholder="e.g. PK00MEZN0000010203040506"
                                        class="form-control text-xs font-mono"
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Raast & Mobile Wallets -->
                        <div class="callout callout-success space-y-3">
                            <h5 class="text-xs font-bold text-[#155724] uppercase tracking-wider m-0 flex items-center gap-1.5">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Instant Raast &amp; Mobile Wallets</span>
                            </h5>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">Raast ID (Phone / IBAN)</label>
                                    <input
                                        type="text"
                                        name="raast_id"
                                        value="{{ old('raast_id', setting('raast_id')) }}"
                                        placeholder="03001234567"
                                        class="form-control text-xs font-mono"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">JazzCash Account No.</label>
                                    <input
                                        type="text"
                                        name="jazzcash_mobile"
                                        value="{{ old('jazzcash_mobile', setting('jazzcash_mobile')) }}"
                                        placeholder="03001234567"
                                        class="form-control text-xs font-mono"
                                    >
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-[#495057] mb-1">EasyPaisa Account No.</label>
                                    <input
                                        type="text"
                                        name="easypaisa_mobile"
                                        value="{{ old('easypaisa_mobile', setting('easypaisa_mobile')) }}"
                                        placeholder="03001234567"
                                        class="form-control text-xs font-mono"
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Payment Settings &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 4. EMAIL / SMTP TAB -->
                <div x-show="activeTab === 'email'" x-cloak class="space-y-5">
                    <div class="border-b border-[#dee2e6] pb-2">
                        <h4 class="text-xs font-bold text-[#212529] uppercase tracking-wider m-0">Email SMTP Configuration</h4>
                        <p class="text-xs text-[#6c757d] mt-0.5 m-0">Automated emails for application status updates, client notifications, and invoices.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.company.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="email">

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Mail Driver / Mailer</label>
                                <input
                                    type="text"
                                    name="mail_mailer"
                                    value="{{ old('mail_mailer', setting('mail_mailer', 'smtp')) }}"
                                    placeholder="smtp"
                                    class="form-control text-xs font-mono"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">SMTP Host</label>
                                <input
                                    type="text"
                                    name="mail_host"
                                    value="{{ old('mail_host', setting('mail_host', 'smtp.mailtrap.io')) }}"
                                    placeholder="smtp.mailgun.org"
                                    class="form-control text-xs font-mono"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">SMTP Port</label>
                                <input
                                    type="text"
                                    name="mail_port"
                                    value="{{ old('mail_port', setting('mail_port', '587')) }}"
                                    placeholder="587, 465, or 2525"
                                    class="form-control text-xs font-mono"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">SMTP Username</label>
                                <input
                                    type="text"
                                    name="mail_username"
                                    value="{{ old('mail_username', setting('mail_username')) }}"
                                    placeholder="username / api_key"
                                    class="form-control text-xs"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">SMTP Password</label>
                                <input
                                    type="password"
                                    name="mail_password"
                                    value="{{ old('mail_password', setting('mail_password')) }}"
                                    placeholder="••••••••"
                                    class="form-control text-xs"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Encryption Protocol</label>
                                <select
                                    name="mail_encryption"
                                    class="form-control text-xs"
                                >
                                    <option value="tls" {{ setting('mail_encryption') === 'tls' ? 'selected' : '' }}>TLS (Recommended)</option>
                                    <option value="ssl" {{ setting('mail_encryption') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="" {{ empty(setting('mail_encryption')) ? 'selected' : '' }}>None</option>
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-[#495057] mb-1">From Sender Email</label>
                                <input
                                    type="email"
                                    name="mail_from_address"
                                    value="{{ old('mail_from_address', setting('mail_from_address', 'noreply@taxconsultants.pk')) }}"
                                    placeholder="notifications@taxconsultants.pk"
                                    class="form-control text-xs"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">From Sender Name</label>
                                <input
                                    type="text"
                                    name="mail_from_name"
                                    value="{{ old('mail_from_name', setting('mail_from_name', 'Tax Consultants PK')) }}"
                                    placeholder="Tax Consultants PK"
                                    class="form-control text-xs font-semibold"
                                >
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Email Settings &rarr;
                            </button>
                        </div>
                    </form>
                </div>

                <!-- 5. FIREBASE & GOOGLE LOGIN TAB -->
                <div x-show="activeTab === 'firebase'" x-cloak class="space-y-5">
                    <div class="border-b border-[#dee2e6] pb-2">
                        <h4 class="text-xs font-bold text-[#212529] uppercase tracking-wider m-0">Firebase &amp; Google Authentication</h4>
                        <p class="text-xs text-[#6c757d] mt-0.5 m-0">Enable 1-click Google Sign-In for clients and real-time notifications.</p>
                    </div>

                    <form method="POST" action="{{ route('settings.company.update') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="firebase">

                        <div class="callout callout-warning space-y-1">
                            <strong class="font-bold block text-xs">Google Login Integration:</strong>
                            <p class="m-0 text-xs">Enter your Firebase Project configuration from the Google Firebase Console (Project Settings &rarr; General &rarr; Web Apps).</p>
                        </div>

                        <div class="pt-1">
                            <label class="inline-flex items-center space-x-2 text-xs font-bold text-[#212529] cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="google_login_enabled"
                                    value="1"
                                    {{ setting('google_login_enabled') === '1' ? 'checked' : '' }}
                                    class="w-4 h-4 text-[#28a745] rounded border-[#ced4da]"
                                >
                                <span>Enable 1-Click Google Sign-In for Clients</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Firebase API Key</label>
                                <input
                                    type="text"
                                    name="firebase_api_key"
                                    value="{{ old('firebase_api_key', setting('firebase_api_key')) }}"
                                    placeholder="AIzaSy..."
                                    class="form-control text-xs font-mono"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Firebase Project ID</label>
                                <input
                                    type="text"
                                    name="firebase_project_id"
                                    value="{{ old('firebase_project_id', setting('firebase_project_id')) }}"
                                    placeholder="taxfiling-pk-prod"
                                    class="form-control text-xs font-mono"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">Auth Domain</label>
                                <input
                                    type="text"
                                    name="firebase_auth_domain"
                                    value="{{ old('firebase_auth_domain', setting('firebase_auth_domain')) }}"
                                    placeholder="taxfiling-pk-prod.firebaseapp.com"
                                    class="form-control text-xs font-mono"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#495057] mb-1">App ID</label>
                                <input
                                    type="text"
                                    name="firebase_app_id"
                                    value="{{ old('firebase_app_id', setting('firebase_app_id')) }}"
                                    placeholder="1:123456789:web:abcdef"
                                    class="form-control text-xs font-mono"
                                >
                            </div>
                        </div>

                        <div class="flex justify-end pt-3 border-t border-[#dee2e6]">
                            <button type="submit" class="btn btn-primary font-bold text-xs">
                                <i class="fas fa-save mr-1"></i> Save Firebase Configuration &rarr;
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
