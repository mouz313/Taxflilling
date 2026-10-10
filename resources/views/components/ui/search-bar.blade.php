@props([
    'placeholder' => 'Search by name, CNIC, phone...',
    'action' => '',
    'method' => 'GET',
    'searchName' => 'search',
    'searchValue' => request('search', ''),
    'filters' => null,
    'actions' => null,
])

<form action="{{ $action }}" method="{{ $method }}" class="w-full mb-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
        <!-- Search & Filter Controls -->
        <div class="flex flex-1 flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            <!-- Search Input Box -->
            <div class="relative flex-1 min-w-[240px]">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    name="{{ $searchName }}"
                    value="{{ $searchValue }}"
                    placeholder="{{ $placeholder }}"
                    class="w-full pl-10 pr-4 py-2 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-100 focus:border-emerald-500 transition shadow-sm hover:border-gray-300 placeholder-gray-400"
                />
            </div>

            <!-- Optional Custom Filters Slot (e.g. dropdowns) -->
            @if($filters)
                <div class="flex items-center gap-2">
                    {{ $filters }}
                </div>
            @endif

            <!-- Search Action Button -->
            <button
                type="submit"
                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <span>Filter</span>
            </button>

            @if(request()->anyFilled([$searchName, 'status', 'tax_year', 'source']))
                <a
                    href="{{ $action ?: url()->current() }}"
                    class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg border border-gray-200 transition"
                    title="Reset Filters"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>Reset</span>
                </a>
            @endif
        </div>

        <!-- Right Side Primary Action Slot (e.g. Add Button) -->
        @if($actions)
            <div class="flex items-center space-x-2">
                {{ $actions }}
            </div>
        @endif
    </div>
</form>
