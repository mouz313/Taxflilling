@props([
    'name' => 'file',
    'label' => 'Upload Document',
    'accept' => '.pdf,.jpg,.jpeg,.png',
    'maxSize' => '10 MB',
    'helper' => 'PDF, JPG ya PNG (Max 10 MB)',
])

@php
    $inputId = 'upload-' . uniqid();
@endphp

<div
    x-data="{
        isDragging: false,
        fileName: '',
        fileSize: '',
        handleDrop(e) {
            this.isDragging = false;
            let files = e.dataTransfer.files;
            if (files.length) {
                this.setFile(files[0]);
            }
        },
        handleSelect(e) {
            let files = e.target.files;
            if (files.length) {
                this.setFile(files[0]);
            }
        },
        setFile(file) {
            this.fileName = file.name;
            let size = file.size / 1024;
            this.fileSize = size > 1024 ? (size / 1024).toFixed(1) + ' MB' : Math.round(size) + ' KB';
        },
        clearFile() {
            this.fileName = '';
            this.fileSize = '';
            document.getElementById('{{ $inputId }}').value = '';
        }
    }"
    class="w-full"
>
    <!-- Upload Dropzone -->
    <div
        x-show="!fileName"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop($event)"
        :class="isDragging ? 'border-blue-500 bg-blue-50/50' : 'border-slate-300 hover:border-slate-400 bg-white'"
        class="border-2 border-dashed rounded-xl p-6 text-center transition-colors cursor-pointer"
        @click="document.getElementById('{{ $inputId }}').click()"
    >
        <input
            type="file"
            id="{{ $inputId }}"
            name="{{ $name }}"
            accept="{{ $accept }}"
            class="hidden"
            @change="handleSelect($event)"
        />

        <div class="flex flex-col items-center justify-center space-y-2">
            <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center">
                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
            </div>
            <div class="text-sm text-slate-700">
                <span class="font-medium text-blue-600 hover:underline">Choose file</span>
                <span class="text-slate-500">ya yahan drag & drop karein</span>
            </div>
            <p class="text-xs text-slate-400">{{ $helper }}</p>
        </div>
    </div>

    <!-- Uploaded File Row -->
    <div x-show="fileName" x-cloak class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between">
        <div class="flex items-center space-x-3 truncate">
            <span class="text-xl text-blue-600">📄</span>
            <div class="truncate text-xs">
                <p class="font-medium text-slate-800 truncate" x-text="fileName"></p>
                <p class="text-slate-400 tabular-nums" x-text="fileSize"></p>
            </div>
        </div>
        <button
            type="button"
            @click.stop="clearFile()"
            class="text-xs font-medium text-red-600 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50 transition cursor-pointer"
        >
            Remove
        </button>
    </div>
</div>
