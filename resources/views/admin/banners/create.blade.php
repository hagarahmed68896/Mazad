@extends('layouts.admin')

@section('title', 'إضافة بانر إعلاني')

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">

    <!-- Header -->
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('admin.banners.index') }}" class="text-slate-500 hover:text-[#1A73E8] transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
        <h1 class="text-3xl font-black text-[#1C1C1C]">إضافة بانر إعلاني</h1>
    </div>

    <!-- Form Container -->
    <div class="bg-[#F8FAFC] border border-slate-100 rounded-[2rem] p-6 lg:p-10 mb-8 max-w-5xl">
        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" id="bannerForm">
            @csrf

            <!-- Section 1: بيانات البانر -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-6 bg-[#1A73E8] rounded-full"></div>
                    <h3 class="text-xl font-black text-[#1C1C1C]">بيانات البانر</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Global title (optional, can be used for grouping or ignore) -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">اسم المجموعة (اختياري)</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="مثلاً: بانرات رمضان"
                               class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm">
                    </div>

                    <!-- Global Status (Applied to all items) -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">الحالة العامة</label>
                        <div class="relative">
                            <select name="status" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none appearance-none shadow-sm">
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>نشط</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Section 2: المحتوى (Template) -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-6 bg-[#1A73E8] rounded-full"></div>
                    <h3 class="text-xl font-black text-[#1C1C1C]">إضافة محتوى</h3>
                </div>

                <div class="space-y-4" id="templateSection">
                    <!-- Image Upload -->
                    <div id="imageInputContainer">
                        <label class="block text-sm font-bold text-slate-700 mb-3">الصورة</label>
                        <div class="w-full bg-white border-2 border-dashed border-gray-300 rounded-2xl p-8 flex flex-col items-center justify-center hover:bg-[#1A73E8]/5 transition-colors cursor-pointer group relative">
                            <input type="file" id="template-file-upload" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="previewImage(event)">
                            <div id="upload-placeholder" class="flex flex-col items-center">
                                <div class="w-16 h-14 bg-[#1A73E8] text-white rounded-2xl flex items-center justify-center mb-3 shadow-lg shadow-[#1A73E8]/20 group-hover:-translate-y-1 transition-transform">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                </div>
                                <h4 class="text-sm font-black text-[#1C1C1C] mb-1">اسحب الملف هنا</h4>
                                <p class="text-xs text-slate-400 mb-3">أو</p>
                                <button type="button" class="px-5 py-2 bg-white border-2 border-[#1A73E8] text-[#1A73E8] rounded-xl font-black text-xs group-hover:bg-[#1A73E8] group-hover:text-white transition-all">
                                    تصفح الملفات
                                </button>
                            </div>
                            <div id="image-preview" class="hidden flex-col items-center">
                                <img id="preview-img" class="max-h-32 rounded-xl mb-3 shadow-sm" src="#" alt="Preview">
                                <p class="text-xs font-bold text-[#1A73E8] cursor-pointer">انقر لتغيير الصورة</p>
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">العنوان</label>
                        <input type="text" id="template-title" placeholder="أدخل العنوان"
                               class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">الوصف</label>
                        <textarea id="template-desc" placeholder="أدخل الوصف" rows="3"
                               class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm resize-none"></textarea>
                    </div>

                    <!-- Button Text -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">اسم الزر</label>
                        <div class="flex flex-row flex-wrap gap-4">
                            <input type="text" id="template-btn-text" placeholder="أدخل اسم الزر"
                                   oninput="updateButtonPreview()"
                                   class="flex-1 px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm">
                            <input type="hidden" id="template-show-btn" value="0">
                            <button type="button" id="template-toggle-btn" onclick="toggleButtonVisibility()"
                                    class="flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-slate-100 text-slate-400 border-slate-200">
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                <span id="template-toggle-label">مخفي</span>
                            </button>
                        </div>
                        <!-- Button Preview -->
                        <div id="template-btn-preview" class="hidden mt-3">
                            <p class="text-xs text-slate-400 mb-2">معاينة الزر:</p>
                            <span id="template-btn-preview-text" class="inline-block px-5 py-2.5 bg-[#1A73E8] text-white text-sm font-bold rounded-xl shadow-md shadow-[#1A73E8]/20">اسم الزر</span>
                        </div>
                    </div>
                </div>

                <!-- Add Content Button -->
                <button type="button" id="add-content-btn" onclick="addContentItem()" class="mt-6 w-full py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-sm hover:bg-[#1557b0] hover:shadow-lg hover:shadow-[#1A73E8]/20 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span id="add-content-label">إضافة للمحتوى</span>
                </button>
            </div>

            <!-- Preview Section -->
            <div id="previewSection" class="hidden mb-8">
                <hr class="border-slate-200/60 mb-6">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-1 h-6 bg-slate-300 rounded-full"></div>
                    <h3 class="text-xl font-black text-[#1C1C1C]">عرض المحتوى المضاف</h3>
                </div>
                <!-- Container for both visual cards AND hidden true inputs -->
                <div id="previewCards" class="flex flex-col gap-4"></div>
                <div id="submissionInputs" class="hidden"></div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('admin.banners.index') }}" class="px-8 py-4 bg-[#E2E8F0] text-slate-500 rounded-2xl font-black text-sm hover:bg-slate-300 transition-all text-center">إلغاء</a>
                <button type="submit" class="px-12 py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-sm hover:shadow-xl hover:shadow-[#1A73E8]/20 transition-all hover:-translate-y-0.5">حفظ الكل</button>
            </div>

        </form>
    </div>
</div>

<script>
    let previewItems   = [];   // [{title, desc, btn, imgSrc, showBtn, fileInputRef}]
    let editingIndex   = null; 
    let currentImgSrc  = null; 
    let itemCount      = 0;

    /* ─── Toggle button visibility ─── */
    function toggleButtonVisibility() {
        const btn    = document.getElementById('template-toggle-btn');
        const label  = document.getElementById('template-toggle-label');
        const hidden = document.getElementById('template-show-btn');
        const prev   = document.getElementById('template-btn-preview');
        const active = hidden.value === '1';

        if (!active) {
            hidden.value = '1';
            btn.className = 'flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-[#1A73E8] text-white border-[#1A73E8]';
            label.textContent = 'ظاهر';
            btn.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            prev.classList.remove('hidden');
            updateButtonPreview();
        } else {
            hidden.value = '0';
            btn.className = 'flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-slate-100 text-slate-400 border-slate-200';
            label.textContent = 'مخفي';
            btn.querySelector('svg').innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            prev.classList.add('hidden');
        }
    }

    function updateButtonPreview() {
        const inp  = document.getElementById('template-btn-text');
        const span = document.getElementById('template-btn-preview-text');
        if (inp && span) span.textContent = inp.value.trim() || 'اسم الزر';
    }

    /* ─── Image preview ─── */
    function previewImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                currentImgSrc = e.target.result;
                const ph  = document.getElementById('upload-placeholder');
                const pv  = document.getElementById('image-preview');
                const img = document.getElementById('preview-img');
                if (ph)  { ph.classList.add('hidden'); }
                if (pv)  { pv.classList.remove('hidden'); pv.classList.add('flex'); }
                if (img) { img.src = e.target.result; }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    /* ─── Add / Save content item ─── */
    function addContentItem() {
        const title   = (document.getElementById('template-title')?.value || '').trim();
        const desc    = (document.getElementById('template-desc')?.value  || '').trim();
        const btnText = (document.getElementById('template-btn-text')?.value || '').trim();
        const showBtn = (document.getElementById('template-show-btn')?.value || '0');
        const fileInp = document.getElementById('template-file-upload');

        if (!title && !currentImgSrc) {
            alert('أدخل عنوان أو صورة على الأقل');
            return;
        }

        const id = editingIndex !== null ? previewItems[editingIndex].id : itemCount++;
        
        // Handle logic fix: Instead of hidden string path, we MOVE or CLONE the file input
        // Since we want to submit MULTIPLE files, each added item needs its own real input
        let finalFileInp = null;
        if (fileInp.files.length > 0) {
            finalFileInp = fileInp.cloneNode(true);
            finalFileInp.id = `final-file-${id}`;
            finalFileInp.name = `items[${id}][image]`;
            // Important: cloning doen't clone the files property in all browsers, 
            // but for submission we can move the original and create a new template input
        }

        const data = {
            id      : id,
            title   : title,
            desc    : desc,
            btn     : btnText,
            showBtn : showBtn,
            imgSrc  : currentImgSrc,
            fileInp : finalFileInp
        };

        if (editingIndex !== null) {
            previewItems[editingIndex] = data;
            editingIndex = null;
        } else {
            previewItems.push(data);
        }

        renderPreviewCards();
        syncInputs();
        clearTemplate();
    }

    function clearTemplate() {
        document.getElementById('template-title').value = '';
        document.getElementById('template-desc').value = '';
        document.getElementById('template-btn-text').value = '';
        document.getElementById('template-show-btn').value = '0';
        currentImgSrc = null;

        // Reset file input by replacing it (to clear reference)
        const oldInp = document.getElementById('template-file-upload');
        const newInp = oldInp.cloneNode(true);
        newInp.value = '';
        oldInp.parentNode.replaceChild(newInp, oldInp);

        document.getElementById('upload-placeholder').classList.remove('hidden');
        document.getElementById('image-preview').classList.add('hidden');
        document.getElementById('template-btn-preview').classList.add('hidden');
        
        const btnToggle = document.getElementById('template-toggle-btn');
        btnToggle.className = 'flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-slate-100 text-slate-400 border-slate-200';
        document.getElementById('template-toggle-label').textContent = 'مخفي';

        document.getElementById('add-content-label').textContent = 'إضافة للمحتوى';
    }

    function loadItemIntoTemplate(index) {
        const item = previewItems[index];
        editingIndex = index;

        document.getElementById('template-title').value = item.title;
        document.getElementById('template-desc').value = item.desc;
        document.getElementById('template-btn-text').value = item.btn;
        document.getElementById('template-show-btn').value = item.showBtn;
        currentImgSrc = item.imgSrc;

        if (item.imgSrc) {
            document.getElementById('upload-placeholder').classList.add('hidden');
            document.getElementById('image-preview').classList.remove('hidden');
            document.getElementById('preview-img').src = item.imgSrc;
        }

        if (item.showBtn === '1') {
            const btn = document.getElementById('template-toggle-btn');
            btn.className = 'flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-[#1A73E8] text-white border-[#1A73E8]';
            document.getElementById('template-toggle-label').textContent = 'ظاهر';
            document.getElementById('template-btn-preview').classList.remove('hidden');
            updateButtonPreview();
        }

        document.getElementById('add-content-label').textContent = 'حفظ التعديل';
        document.getElementById('templateSection').scrollIntoView({ behavior: 'smooth' });
    }

    function removeItem(index) {
        previewItems.splice(index, 1);
        renderPreviewCards();
        syncInputs();
    }

    /* ─── Render visual cards ─── */
    function renderPreviewCards() {
        const container = document.getElementById('previewCards');
        const section   = document.getElementById('previewSection');

        if (previewItems.length === 0) {
            section.classList.add('hidden');
            return;
        }

        section.classList.remove('hidden');
        container.innerHTML = '';

        previewItems.forEach((item, i) => {
            const card = document.createElement('div');
            card.className = "group relative bg-white border border-slate-200 rounded-3xl p-4 flex gap-4 items-center hover:border-[#1A73E8] transition-all cursor-pointer";
            card.onclick = () => loadItemIntoTemplate(i);

            card.innerHTML = `
                <div class="w-24 h-16 bg-slate-100 rounded-xl overflow-hidden shrink-0">
                    ${item.imgSrc ? `<img src="${item.imgSrc}" class="w-full h-full object-cover">` : '<div class="w-full h-full flex items-center justify-center text-slate-300"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>'}
                </div>
                <div class="flex-1">
                    <h4 class="text-sm font-black text-[#1C1C1C] mb-1 line-clamp-1">${item.title || 'بدون عنوان'}</h4>
                    <p class="text-[10px] text-slate-400 line-clamp-1">${item.desc || 'بدون وصف'}</p>
                </div>
                <button type="button" onclick="event.stopPropagation(); removeItem(${i})" class="p-2 text-slate-300 hover:text-red-500 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            `;
            container.appendChild(card);
        });
    }

    /* ─── Logic Fix: Syncing files and data ─── */
    function syncInputs() {
        const target = document.getElementById('submissionInputs');
        target.innerHTML = '';
        
        previewItems.forEach((item, i) => {
            // Text fields
            const t = document.createElement('input'); t.type='hidden'; t.name=`items[${i}][title]`; t.value=item.title;
            const d = document.createElement('input'); d.type='hidden'; d.name=`items[${i}][description]`; d.value=item.desc;
            const b = document.createElement('input'); b.type='hidden'; b.name=`items[${i}][button_text]`; b.value=item.btn;
            target.appendChild(t); target.appendChild(d); target.appendChild(b);

            // File field fix
            // If we have a file reference, we need to append the cloned input to the form
            if (item.fileInp) {
                // We must use the SAME input objects or else files are lost
                // So when 'adding', we take the current input and put it here
                // and give template a new one.
                target.appendChild(item.fileInp);
            }
        });
    }
    
    // Override addContentItem to handle file transfer properly
    const originalAdd = addContentItem;
    addContentItem = function() {
        const fileInp = document.getElementById('template-file-upload');
        const id = editingIndex !== null ? previewItems[editingIndex].id : itemCount;
        
        // We create a container to hold the file input if it has a file
        let savedInp = null;
        if (fileInp.files.length > 0) {
            // We transfer the ACTUAL input to keep the file
            savedInp = fileInp;
            savedInp.id = `final-file-${id}`;
            savedInp.name = `items[${id}][image]`;
            savedInp.classList.add('hidden'); // hide it
            
            // Give template a NEW one
            const nextInp = savedInp.cloneNode(true);
            nextInp.id = 'template-file-upload';
            nextInp.name = '';
            nextInp.value = '';
            nextInp.classList.remove('hidden');
            savedInp.parentNode.replaceChild(nextInp, savedInp);
        } else if (editingIndex !== null && previewItems[editingIndex].fileInp) {
            // Keep existing if editing and no new file picked
            savedInp = previewItems[editingIndex].fileInp;
        }

        // Call original-like logic but with the savedInp
        const title   = (document.getElementById('template-title')?.value || '').trim();
        const desc    = (document.getElementById('template-desc')?.value  || '').trim();
        const btnText = (document.getElementById('template-btn-text')?.value || '').trim();
        const showBtn = (document.getElementById('template-show-btn')?.value || '0');

        if (!title && !currentImgSrc) {
            alert('أدخل عنوان أو صورة على الأقل');
            return;
        }

        const data = {
            id      : id,
            title   : title,
            desc    : desc,
            btn     : btnText,
            showBtn : showBtn,
            imgSrc  : currentImgSrc,
            fileInp : savedInp
        };

        if (editingIndex !== null) {
            previewItems[editingIndex] = data;
            editingIndex = null;
        } else {
            previewItems.push(data);
            itemCount++;
        }

        renderPreviewCards();
        syncInputs();
        clearTemplate();
    };

</script>
@endsection
