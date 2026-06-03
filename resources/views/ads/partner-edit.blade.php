@extends('layouts.main')

@section('title', 'تعديل الخدمة | لوحة تحكم الشريك')

@section('content')
<!-- Override Navbar, Sidebar and Footer of Main Layout for a focused standalone flow -->
<style>
    .navbar, .categories-nav, .footer { display: none !important; }
    body { background-color: #F8FAFC; }

    /* Custom Gradient Blue Footer */
    .custom-partner-footer {
        background: linear-gradient(135deg, #1A73E8 0%, #1557B0 100%);
        padding: 2.5rem 0;
        margin-top: auto;
    }
</style>

<div class="min-h-screen flex flex-col">
    <!-- Standalone Header -->
    <header class="bg-white py-6 shadow-sm border-b border-slate-100">
        <div class="container mx-auto flex justify-between items-center px-6 md:px-16">
            <a href="{{ route('ads.my') }}" class="px-6 py-2 border border-slate-200 rounded-xl font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-all text-sm shadow-sm">خروج</a>
            <div class="logo">
                <a href="/">
                    @include('partials.logo', ['size' => 'text-4xl'])
                </a>
            </div>
        </div>
    </header>

    <!-- Standalone Main Content Container -->
    <main class="flex-grow flex items-center justify-center py-16 px-4 sm:px-6 lg:px-8">
        <div class="bg-white w-full max-w-3xl rounded-[32px] shadow-[0_20px_50px_rgba(30,41,59,0.02)] border border-slate-100 p-8 sm:p-14" dir="rtl">
            
            <h1 class="text-4xl font-black text-slate-800 text-center mb-10 tracking-tight">تعديل الخدمة</h1>
            
            <form action="{{ route('ads.update', $ad->id) }}" method="POST" id="partnerAdForm" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Ad Image Section -->
                <div>
                    <label class="text-slate-700 font-bold mb-3 block text-sm">صورة الخدمة</label>
                    
                    <!-- Drag & Drop Container -->
                    <div id="dropZone" class="border-2 border-dashed border-slate-200 rounded-[24px] p-10 flex flex-col items-center justify-center bg-slate-50/50 hover:bg-slate-50 hover:border-primary/50 transition-all duration-300 cursor-pointer relative group">
                        <input type="file" name="image" id="fileInput" class="absolute inset-0 opacity-0 cursor-pointer z-10" accept="image/*">
                        
                        <div class="w-16 h-16 bg-blue-50 text-primary rounded-[20px] flex items-center justify-center mb-4 transition-all duration-300 group-hover:scale-105 shadow-sm">
                            <!-- Folder Upload Vector Icon -->
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V4a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        
                        <p class="text-slate-800 font-bold text-base mb-1">اسحب ملفاً جديداً هنا لتغيير الصورة</p>
                        <span class="text-slate-400 text-xs font-semibold mb-3">أو</span>
                        
                        <div class="px-6 py-2.5 border border-primary text-primary font-bold rounded-xl bg-white hover:bg-blue-50/80 transition-all duration-200 text-sm shadow-sm">
                            تصفح الملفات
                        </div>
                    </div>

                    <!-- Uploaded/Existing File Display Box -->
                    <div id="fileListContainer" class="mt-4 transition-all duration-300">
                        <div class="border border-slate-200 rounded-2xl p-4 flex justify-between items-center bg-slate-50/30">
                            <!-- Left: Trash & Eye Actions -->
                            <div class="flex items-center gap-4">
                                <button type="button" id="deleteFileBtn" class="w-10 h-10 rounded-xl bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-600 transition-all duration-200 flex items-center justify-center" title="حذف الصورة">
                                    <i class="fa-solid fa-trash-can text-base"></i>
                                </button>
                                <button type="button" id="previewFileBtn" class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-all duration-200 flex items-center justify-center" title="عرض الصورة">
                                    <i class="fa-solid fa-eye text-base"></i>
                                </button>
                            </div>
                            <!-- Right: Filename and Extension -->
                            <div class="flex items-center gap-2 text-slate-700 font-bold text-sm">
                                <span id="fileNameDisplay" class="truncate max-w-[250px] sm:max-w-[450px]">
                                    {{ $ad->image ? basename($ad->image) : 'صورة الخدمة الحالية' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ad Title Field -->
                <div>
                    <label class="text-slate-700 font-bold mb-2.5 block text-sm">عنوان الخدمة</label>
                    <input type="text" name="title" value="{{ old('title', $ad->title) }}" placeholder="أدخل عنوان الخدمة" required
                           class="w-full px-5 py-4 border border-slate-200 rounded-2xl bg-slate-50/30 hover:bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-slate-800 font-semibold placeholder-slate-400 outline-none text-right shadow-sm">
                </div>

                <!-- Ad Description Field -->
                <div>
                    <label class="text-slate-700 font-bold mb-2.5 block text-sm">وصف الخدمة</label>
                    <textarea name="description" rows="5" placeholder="أدخل وصف الخدمة" required
                              class="w-full px-5 py-4 border border-slate-200 rounded-2xl bg-slate-50/30 hover:bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-slate-800 font-semibold placeholder-slate-400 outline-none text-right resize-none shadow-sm">{{ old('description', $ad->description) }}</textarea>
                </div>

                <!-- Service Price Field -->
                <div>
                    <label class="text-slate-700 font-bold mb-2.5 block text-sm">سعر الخدمة</label>
                    <div class="relative flex items-stretch rounded-2xl overflow-hidden border border-slate-200 bg-slate-50/30 hover:bg-slate-50/50 focus-within:bg-white focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all duration-200 shadow-sm">
                        <div class="flex items-center justify-center px-6 bg-slate-100 border-r border-slate-200 text-slate-700 select-none shadow-inner">
                            <x-riyal-icon class="w-5 h-5 text-slate-800" />
                        </div>
                        <input type="number" name="price" value="{{ old('price', $ad->price) }}" placeholder="أدخل سعر الخدمة" required min="0" step="0.01"
                               class="w-full px-5 py-4 bg-transparent text-slate-800 font-semibold placeholder-slate-400 outline-none text-right [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                    </div>
                </div>

                <!-- Divider Heading -->
                <div class="pt-6 border-t border-slate-100 mt-10">
                    <h3 class="text-xl font-black text-slate-800 mb-6">بيانات التواصل</h3>
                </div>

                <!-- Contact Phone -->
                <div>
                    <label class="text-slate-700 font-bold mb-2.5 block text-sm">رقم الجوال</label>
                    <div class="relative flex items-stretch rounded-2xl overflow-hidden border border-slate-200 bg-slate-50/30 hover:bg-slate-50/50 focus-within:bg-white focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10 transition-all duration-200 shadow-sm">
                        <div class="flex items-center justify-center px-6 bg-slate-100 border-r border-slate-200 text-slate-500 font-black text-sm select-none shadow-inner" dir="ltr">
                            +966
                        </div>
                        <input type="tel" name="phone" value="{{ old('phone', $ad->user->phone ?? '') }}" placeholder="أدخل رقم الجوال" required
                               class="w-full px-5 py-4 bg-transparent text-slate-800 font-semibold placeholder-slate-400 outline-none text-right">
                    </div>
                </div>

                <!-- Contact Email -->
                <div>
                    <label class="text-slate-700 font-bold mb-2.5 block text-sm">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email', $ad->user->email ?? '') }}" placeholder="أدخل البريد الإلكتروني" required
                           class="w-full px-5 py-4 border border-slate-200 rounded-2xl bg-slate-50/30 hover:bg-slate-50/50 focus:bg-white focus:border-primary focus:ring-4 focus:ring-primary/10 transition-all duration-200 text-slate-800 font-semibold placeholder-slate-400 outline-none text-right shadow-sm">
                </div>

                <!-- Form Submit/Cancel Action buttons -->
                <div class="flex gap-4 items-center pt-8">
                    <button type="submit" id="submitAdBtn" class="flex-grow py-4.5 bg-primary hover:bg-blue-700 active:scale-[0.98] text-white font-black text-lg rounded-2xl shadow-xl shadow-blue-200 hover:shadow-blue-300 transition-all duration-250 flex items-center justify-center gap-2">
                        حفظ التعديلات
                    </button>
                    <a href="{{ route('ads.my') }}" class="w-1/3 py-4.5 bg-slate-100 hover:bg-slate-200 active:scale-[0.98] text-slate-500 hover:text-slate-700 font-black text-lg rounded-2xl text-center transition-all duration-200">
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </main>

    <!-- Custom Footing Section -->
    <footer class="custom-partner-footer">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center text-white/80 font-bold text-sm px-6 md:px-16">
            <div class="flex gap-8 mb-4 md:mb-0">
                <a href="#" class="hover:text-white transition-colors duration-200">الشروط و الاحكام</a>
                <a href="#" class="hover:text-white transition-colors duration-200">سياسة الخصوصية</a>
            </div>
            <div dir="ltr" class="font-outfit text-white/90">
                {{ \App\Models\Setting::where('key', 'copyright')->first()->value ?? '©2025 copyright' }}
            </div>
        </div>
    </footer>
</div>

<!-- Premium Preview Modal Overlay -->
<div id="imagePreviewModal" class="fixed inset-0 z-[13000] hidden items-center justify-center p-4 bg-black/75 backdrop-blur-md transition-all duration-300">
    <div class="relative bg-white max-w-2xl w-full rounded-3xl overflow-hidden shadow-[0_32px_64px_-12px_rgba(0,0,0,0.5)] animate-in zoom-in-95 duration-200">
        <button type="button" onclick="closePreviewModal()" class="absolute top-4 left-4 w-10 h-10 bg-black/50 hover:bg-black/75 text-white rounded-full flex items-center justify-center transition-all shadow-md z-20">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
        <div class="p-6 flex items-center justify-center bg-slate-900 min-h-[400px]">
            <img id="previewImageElement" src="{{ $ad->image ? (Str::startsWith($ad->image, 'http') ? $ad->image : '/' . ltrim($ad->image, '/')) : '' }}" alt="معاينة صورة الخدمة" class="max-w-full max-h-[60vh] object-contain rounded-xl shadow-lg">
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Premium input and form styling fixes */
    .py-4\.5 {
        padding-top: 1.125rem;
        padding-bottom: 1.125rem;
    }
</style>
@endpush

<!-- JavaScript logic for Drag & Drop + Live Preview -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const dropZone = document.getElementById("dropZone");
        const fileInput = document.getElementById("fileInput");
        const fileListContainer = document.getElementById("fileListContainer");
        const fileNameDisplay = document.getElementById("fileNameDisplay");
        const deleteFileBtn = document.getElementById("deleteFileBtn");
        const previewFileBtn = document.getElementById("previewFileBtn");
        const previewModal = document.getElementById("imagePreviewModal");
        const previewImg = document.getElementById("previewImageElement");
        const submitAdBtn = document.getElementById("submitAdBtn");
        const form = document.getElementById("partnerAdForm");

        let currentFileObject = null;
        let hasExistingImage = {!! $ad->image ? 'true' : 'false' !!};

        // Prevent default drag behaviors
        ["dragenter", "dragover", "dragleave", "drop"].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        // Add highlight classes on drag hover
        ["dragenter", "dragover"].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add("border-primary/70", "bg-blue-50/60");
            }, false);
        });

        ["dragleave", "drop"].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove("border-primary/70", "bg-blue-50/60");
            }, false);
        });

        // Handle drop event
        dropZone.addEventListener("drop", handleDrop, false);

        // Click zone to browse
        dropZone.addEventListener("click", () => {
            fileInput.click();
        });

        // Prevent click bubbling on input itself
        fileInput.addEventListener("click", (e) => {
            e.stopPropagation();
        });

        // Handle file select via browsing
        fileInput.addEventListener("change", function() {
            if (fileInput.files && fileInput.files[0]) {
                processSelectedFile(fileInput.files[0]);
            }
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files && files[0]) {
                fileInput.files = files; // Assign files to file input
                processSelectedFile(files[0]);
            }
        }

        function processSelectedFile(file) {
            if (!file.type.startsWith("image/")) {
                Swal.fire({
                    icon: 'error',
                    title: 'ملف غير صالح',
                    text: 'يجب اختيار ملف صورة فقط (jpeg, png, jpg, gif, webp).',
                    confirmButtonText: 'حسناً',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'rounded-xl bg-primary text-white px-6 py-2.5 font-bold outline-none'
                    }
                });
                return;
            }

            currentFileObject = file;
            fileNameDisplay.textContent = file.name;

            // Show list container with smooth sliding effect
            fileListContainer.classList.remove("hidden");
            fileListContainer.style.opacity = "1";
            fileListContainer.style.transform = "translateY(0)";

            // Read file for preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
            }
            reader.readAsDataURL(file);
        }

        // Delete selected file
        deleteFileBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            fileInput.value = ""; // Clear file input
            currentFileObject = null;
            
            // If they click delete on existing image, we show "no image chosen"
            previewImg.src = "";
            fileNameDisplay.textContent = "تم إزالة الصورة (يرجى رفع صورة جديدة)";
        });

        // Preview selected file in modal
        previewFileBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            if (previewImg.src) {
                previewModal.classList.remove("hidden");
                previewModal.classList.add("flex");
            }
        });

        // Form Submit Loading spinner state
        form.addEventListener("submit", function() {
            submitAdBtn.disabled = true;
            submitAdBtn.innerHTML = `
                <svg class="animate-spin h-5 w-5 text-white ml-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                جاري حفظ التعديلات...
            `;
        });
    });

    // Close preview modal
    function closePreviewModal() {
        const previewModal = document.getElementById("imagePreviewModal");
        previewModal.classList.remove("flex");
        previewModal.classList.add("hidden");
    }

    // Close modal on background click
    document.getElementById("imagePreviewModal").addEventListener("click", function(e) {
        if (e.target === this) {
            closePreviewModal();
        }
    });
</script>
@endsection
