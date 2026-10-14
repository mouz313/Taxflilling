<x-layouts.app header="CMS · Services Marketplace Manager">
    <div class="w-full space-y-4">
        <!-- 1. Authentic AdminLTE Small-Boxes Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <!-- Box 1: Total Services -->
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalCount }}</h3>
                    <p>Total Services</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <a href="{{ route('admin.cms.services.index', ['category' => 'all']) }}" class="small-box-footer">
                    <span>In Master Catalog</span> <i class="fas fa-arrow-circle-right ml-1"></i>
                </a>
            </div>

            <!-- Box 2: Live & Active -->
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $activeCount }}</h3>
                    <p>Live &amp; Active</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="small-box-footer">
                    <span>Publicly Available</span>
                </div>
            </div>

            <!-- Box 3: Draft / Inactive -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $totalCount - $activeCount }}</h3>
                    <p>Draft / Inactive</p>
                </div>
                <div class="icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
                <div class="small-box-footer">
                    <span>Hidden from Visitors</span>
                </div>
            </div>

            <!-- Box 4: Categories -->
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ count($categories) }}</h3>
                    <p>Active Sectors</p>
                </div>
                <div class="icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="small-box-footer">
                    <span>Business Categories</span>
                </div>
            </div>
        </div>

        @if(session('status'))
            <div class="p-3 bg-[#d4edda] text-[#155724] border border-[#c3e6cb] rounded text-xs font-semibold flex items-center justify-between shadow-2xs">
                <span>✅ {{ session('status') }}</span>
                <button type="button" onclick="this.parentElement.remove()" class="text-[#155724] font-bold">&times;</button>
            </div>
        @endif

        <!-- 2. Services Data Card (AdminLTE) -->
        <div class="card card-primary card-outline w-full">
            <div class="card-header flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <h3 class="card-title text-sm font-bold text-[#212529] flex items-center gap-2">
                    <i class="fas fa-boxes-stacked text-[#007bff]"></i>
                    <span>Business Services Catalog &amp; Pricing</span>
                </h3>

                <!-- Card Tools -->
                <div class="card-tools flex flex-wrap items-center gap-2">
                    <!-- Category Filter Buttons -->
                    <div class="inline-flex rounded-md shadow-2xs" role="group">
                        <a
                            href="{{ route('admin.cms.services.index', array_merge(request()->query(), ['category' => 'all'])) }}"
                            class="btn btn-xs {{ $category === 'all' ? 'btn-primary font-bold' : 'btn-default' }}"
                        >
                            All ({{ $totalCount }})
                        </a>
                        @foreach($categories as $catKey => $catData)
                            <a
                                href="{{ route('admin.cms.services.index', array_merge(request()->query(), ['category' => $catKey])) }}"
                                class="btn btn-xs {{ $category === $catKey ? 'btn-primary font-bold' : 'btn-default' }}"
                            >
                                <span>{{ $catData['icon'] }}</span>
                                <span class="hidden sm:inline ml-1">{{ $catData['name'] }}</span>
                            </a>
                        @endforeach
                    </div>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('admin.cms.services.index') }}" class="flex items-center gap-1">
                        @if($category !== 'all')
                            <input type="hidden" name="category" value="{{ $category }}">
                        @endif
                        <div class="relative">
                            <input
                                type="text"
                                name="search"
                                value="{{ $search }}"
                                placeholder="Search title or code..."
                                class="form-control form-control-sm text-xs w-36 sm:w-48 pl-7"
                            />
                            <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-[#adb5bd] text-xs"></i>
                        </div>
                        <button type="submit" class="btn btn-xs btn-primary font-semibold">Filter</button>
                        @if(!empty($search))
                            <a href="{{ route('admin.cms.services.index', ['category' => $category]) }}" class="text-xs text-[#dc3545] hover:underline px-1">Clear</a>
                        @endif
                    </form>

                    <!-- Add New Service Button -->
                    <a
                        href="{{ route('admin.cms.services.create') }}"
                        class="btn btn-xs btn-success font-bold inline-flex items-center gap-1"
                    >
                        <i class="fas fa-plus"></i>
                        <span>Add Service</span>
                    </a>

                    <a
                        href="{{ route('services.index') }}"
                        target="_blank"
                        class="btn btn-xs btn-default inline-flex items-center gap-1"
                        title="Live Client Marketplace Preview"
                    >
                        <i class="fas fa-globe text-[#007bff]"></i>
                        <span>Preview</span>
                    </a>
                </div>
            </div>

            <!-- Table -->
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    <table class="table table-hover table-striped w-full text-xs text-nowrap align-middle">
                        <thead>
                            <tr>
                                <th>Service Details</th>
                                <th>Category</th>
                                <th>Fee (PKR)</th>
                                <th>Turnaround</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $item)
                                <tr class="{{ !$item->is_active ? 'opacity-60 bg-[#f8f9fa]' : '' }}">
                                    <td>
                                        <div class="flex items-center space-x-2.5">
                                            <span class="text-xl shrink-0">{{ $item->icon }}</span>
                                            <div>
                                                <div class="flex items-center space-x-1.5">
                                                    <span class="font-bold text-[#212529]">{{ $item->heading }}</span>
                                                    @if($item->popular)
                                                        <span class="badge badge-warning text-[9px] font-extrabold uppercase">★ Popular</span>
                                                    @endif
                                                </div>
                                                @if($item->urdu)
                                                    <div class="text-[11px] text-[#007bff] font-medium">{{ $item->urdu }}</div>
                                                @endif
                                                <div class="text-[10px] text-[#6c757d] font-mono">Code: {{ $item->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary font-semibold">
                                            {{ $categories[$item->category]['icon'] ?? '📁' }} {{ $item->category_name }}
                                        </span>
                                    </td>
                                    <td class="font-bold text-[#212529] font-mono tabular-nums">
                                        {{ $item->currency }} {{ number_format($item->fee, 0) }}
                                    </td>
                                    <td class="text-[#495057]">
                                        <i class="far fa-clock mr-1 text-[#6c757d]"></i>{{ $item->turnaround }}
                                    </td>
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('admin.cms.services.toggle', $item) }}" class="inline">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="badge {{ $item->is_active ? 'badge-success' : 'badge-secondary' }} px-2.5 py-1 font-bold cursor-pointer transition hover:opacity-80"
                                                title="Toggle Active / Hidden"
                                            >
                                                {{ $item->is_active ? 'Active' : 'Hidden' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end space-x-1">
                                            <a
                                                href="{{ route('admin.cms.services.edit', $item) }}"
                                                class="btn btn-xs btn-default text-[#007bff]"
                                                title="Edit Service"
                                            >
                                                <i class="fas fa-edit mr-1"></i>Edit
                                            </a>
                                            <form
                                                method="POST"
                                                action="{{ route('admin.cms.services.destroy', $item) }}"
                                                onsubmit="return confirm('Are you sure you want to delete \'{{ addslashes($item->heading) }}\'?');"
                                                class="inline"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-xs btn-default text-[#dc3545]"
                                                    title="Delete Service"
                                                >
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-[#adb5bd]">
                                        <i class="fas fa-search text-3xl mb-2 block"></i>
                                        <p class="text-sm font-semibold text-[#495057] m-0">Koi service nahi mili</p>
                                        <p class="text-xs text-[#6c757d] mt-1 m-0">Nayi service add karne ke liye upar 'Add Service' button dabayein.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($services->hasPages())
                <div class="card-footer clearfix bg-white border-t border-[#dee2e6]">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
