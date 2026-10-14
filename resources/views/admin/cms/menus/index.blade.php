<x-layouts.app>
    <x-slot:header>
        CMS &bull; Navigation Menu Builder (Drag & Drop)
    </x-slot:header>

    <div class="space-y-6">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 tracking-tight">Navigation Menu Builder</h2>
                <p class="text-xs text-slate-500 mt-1">Organize Header and Footer menus with drag & drop ordering and custom links.</p>
            </div>

            <!-- Tab Switcher (Header vs Footer) -->
            <div class="flex items-center space-x-2 bg-slate-100 p-1 rounded-xl">
                <a
                    href="{{ route('admin.cms.menus.index', ['location' => 'header']) }}"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition {{ $location === 'header' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Header Menu
                </a>
                <a
                    href="{{ route('admin.cms.menus.index', ['location' => 'footer']) }}"
                    class="px-4 py-2 rounded-lg text-xs font-bold transition {{ $location === 'footer' ? 'bg-white text-blue-600 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Footer Menu
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center justify-between">
                <span>{{ session('status') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Left Side: Add Menu Item Form -->
            <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5" x-data="{
                quickLink(title, url) {
                    $refs.titleInput.value = title;
                    $refs.urlInput.value = url;
                }
            }">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Add New Menu Link</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Add link to <strong class="text-slate-700 capitalize">{{ $location }}</strong> menu.</p>
                </div>

                <!-- Quick Pickers from CMS Pages and Categories -->
                <div class="space-y-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Quick Pick from Pages</label>
                        <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto pr-1">
                            @foreach($pages as $p)
                                <button
                                    type="button"
                                    @click="quickLink('{{ addslashes($p->title) }}', '/p/{{ $p->slug }}')"
                                    class="text-[11px] px-2 py-1 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 text-slate-700 rounded-lg transition font-medium border border-slate-200/60"
                                >
                                    + {{ $p->title }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Quick Pick from Service Categories</label>
                        <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto pr-1">
                            @foreach($categories as $c)
                                <button
                                    type="button"
                                    @click="quickLink('{{ addslashes($c->name) }}', '/services?tab={{ $c->slug }}')"
                                    class="text-[11px] px-2 py-1 bg-slate-100 hover:bg-emerald-50 hover:text-emerald-700 text-slate-700 rounded-lg transition font-medium border border-slate-200/60"
                                >
                                    {{ $c->icon }} {{ $c->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.cms.menus.store') }}" class="space-y-4 pt-3 border-t border-slate-100">
                    @csrf
                    <input type="hidden" name="location" value="{{ $location }}">

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Navigation Label *</label>
                        <input
                            type="text"
                            name="title"
                            x-ref="titleInput"
                            required
                            placeholder="e.g. Services, About Us"
                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 font-semibold"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Destination URL / Route *</label>
                        <input
                            type="text"
                            name="url"
                            x-ref="urlInput"
                            required
                            placeholder="e.g. /services, /p/about-us, https://..."
                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 font-mono"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Target Window</label>
                        <select
                            name="target"
                            class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 bg-white"
                        >
                            <option value="_self">Same Tab (_self)</option>
                            <option value="_blank">New Tab (_blank)</option>
                        </select>
                    </div>

                    @if($location === 'header' && $menuItems->count() > 0)
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Parent Menu (Optional Dropdown)</label>
                            <select
                                name="parent_id"
                                class="w-full text-xs px-3 py-2 rounded-xl border border-slate-300 focus:outline-hidden focus:border-blue-500 bg-white"
                            >
                                <option value="">Top Level (No Parent)</option>
                                @foreach($menuItems as $top)
                                    <option value="{{ $top->id }}">{{ $top->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button
                        type="submit"
                        class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                    >
                        + Add To {{ ucfirst($location) }} Menu
                    </button>
                </form>
            </div>

            <!-- Right Side: Menu Items List with Reordering -->
            <div 
                class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4"
                x-data="{
                    items: {{ Js::from($menuItems->map(fn($item) => ['id' => $item->id, 'sort_order' => $item->sort_order])) }},
                    dragIndex: null,
                    saving: false,
                    moveUp(index) {
                        if (index > 0) {
                            const temp = this.items[index];
                            this.items[index] = this.items[index - 1];
                            this.items[index - 1] = temp;
                            this.saveOrder();
                        }
                    },
                    moveDown(index) {
                        if (index < this.items.length - 1) {
                            const temp = this.items[index];
                            this.items[index] = this.items[index + 1];
                            this.items[index + 1] = temp;
                            this.saveOrder();
                        }
                    },
                    saveOrder() {
                        this.saving = true;
                        const payload = this.items.map((item, idx) => ({ id: item.id, sort_order: idx + 1 }));
                        fetch('{{ route('admin.cms.menus.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: payload })
                        })
                        .then(r => r.json())
                        .then(() => {
                            this.saving = false;
                            window.location.reload();
                        })
                        .catch(() => {
                            this.saving = false;
                            alert('Failed to save order');
                        });
                    }
                }"
            >
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">{{ ucfirst($location) }} Menu Items</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Use &uarr; and &darr; arrows to reorder links in real time.</p>
                    </div>
                    <div x-show="saving" class="text-xs text-blue-600 font-bold animate-pulse">
                        Saving changes...
                    </div>
                </div>

                @if($menuItems->isEmpty())
                    <div class="py-12 text-center text-slate-400 text-xs">
                        No menu items configured for this location yet. Use the left form to add your first menu item.
                    </div>
                @else
                    <div class="space-y-3" id="menu-items-list">
                        @foreach($menuItems as $index => $item)
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 hover:bg-slate-50 transition flex items-center justify-between gap-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex flex-col space-y-1">
                                        <button
                                            type="button"
                                            @click="moveUp({{ $index }})"
                                            class="w-6 h-6 flex items-center justify-center bg-white border border-slate-200 rounded text-slate-500 hover:text-blue-600 hover:bg-blue-50 text-[11px] font-bold"
                                            title="Move Up"
                                        >
                                            &uarr;
                                        </button>
                                        <button
                                            type="button"
                                            @click="moveDown({{ $index }})"
                                            class="w-6 h-6 flex items-center justify-center bg-white border border-slate-200 rounded text-slate-500 hover:text-blue-600 hover:bg-blue-50 text-[11px] font-bold"
                                            title="Move Down"
                                        >
                                            &darr;
                                        </button>
                                    </div>

                                    <div>
                                        <div class="font-bold text-slate-900 text-xs flex items-center space-x-2">
                                            <span>{{ $item->title }}</span>
                                            @if($item->target === '_blank')
                                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 font-mono">External</span>
                                            @endif
                                            @if($item->children->count() > 0)
                                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 font-bold">
                                                    {{ $item->children->count() }} Sub-links
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] font-mono text-slate-500 mt-0.5">
                                            {{ $item->url }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <form method="POST" action="{{ route('admin.cms.menus.destroy', $item) }}" onsubmit="return confirm('Remove this menu link?');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                            title="Delete"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if($item->children->count() > 0)
                                <div class="ml-8 space-y-2 border-l-2 border-blue-200 pl-3">
                                    @foreach($item->children as $child)
                                        <div class="p-2.5 rounded-lg border border-slate-200 bg-white flex items-center justify-between text-xs">
                                            <div>
                                                <span class="font-bold text-slate-800">&bull; {{ $child->title }}</span>
                                                <span class="text-slate-400 font-mono text-[10px] ml-2">{{ $child->url }}</span>
                                            </div>
                                            <form method="POST" action="{{ route('admin.cms.menus.destroy', $child) }}" onsubmit="return confirm('Remove sublink?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:underline text-[11px]">Remove</button>
                                            </form>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
