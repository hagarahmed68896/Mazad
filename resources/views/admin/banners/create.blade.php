@extends('layouts.admin')

@section('title', 'إضافة بانر إعلاني')

@section('content')
<style>
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    
    .banners-slider {
        display: flex;
        overflow-x: auto;
        gap: 1.5rem;
        padding-bottom: 1.25rem;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
    }
    
    .banners-slider > div {
        flex: 0 0 calc(33.33% - 1rem);
        scroll-snap-align: start;
        flex-shrink: 0;
    }
    
    @media (max-width: 1024px) {
        .banners-slider > div { flex: 0 0 calc(50% - 0.75rem); }
    }
    
    @media (max-width: 640px) {
        .banners-slider > div { flex: 0 0 100%; }
    }
</style>

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
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">العنوان</label>
                        <input type="text" name="name" id="global-name" value="{{ old('name') }}" 
                               class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-3">الحالة</label>
                        <div class="relative">
                            <select name="status" class="w-full px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none appearance-none shadow-sm">
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>نشط</option>
                                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>غير نشط</option>
                            </select>
                          
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: إضافة للمحتوى -->
            <div class="mb-6">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-1 h-6 bg-[#1A73E8] rounded-full"></div>
                    <h3 class="text-xl font-black text-[#1C1C1C]">المحتوى</h3>
                </div>

                <div id="edit-indicator" class="hidden mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <p class="text-xs font-bold text-amber-700">أنت الآن تقوم بتعديل عنصر موجود. قم بتعديل البيانات ثم اضغط "حفظ التعديل".</p>
                </div>

                <div class="space-y-4" id="templateSection">
                    <!-- Image Upload -->
                    <div>
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
                                   class="flex-1 px-5 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-400 placeholder:font-normal shadow-sm">
                            <input type="hidden" id="template-show-btn" value="0">
                            <button type="button" id="template-toggle-btn" onclick="toggleButtonVisibility()"
                                    class="flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-slate-100 text-slate-400 border-slate-200">
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span id="template-toggle-label">ظهور الزر</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Content Button -->
                <button type="button" id="add-content-btn" onclick="addContentItem()" class="mt-6 w-full py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-sm hover:bg-[#1557b0] hover:shadow-lg hover:shadow-[#1A73E8]/20 transition-all flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>إضافة للمحتوى</span>
                </button>
            </div>

            <!-- Preview Section (Blue Cards) -->
            <div id="previewSection" class="hidden mb-8">
                <hr class="border-slate-200/60 mb-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-1 h-6 bg-slate-300 rounded-full"></div>
                    <h3 class="text-xl font-black text-[#1C1C1C]">عرض المحتوى</h3>
                </div>
                
                <div id="previewCards" class="banners-slider custom-scrollbar"></div>
                
                <!-- Hidden Container for final form inputs -->
                <div id="submissionInputs" class="hidden"></div>
            </div>

            <!-- Footer Buttons -->
            <div class="flex items-center justify-end gap-4">
                <button type="button" onclick="window.history.back()" class="px-8 py-4 bg-[#E2E8F0] text-slate-500 rounded-2xl font-black text-sm hover:bg-slate-300 transition-all text-center">إلغاء</button>
                <button type="submit" class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black text-sm hover:shadow-xl hover:shadow-[#1A73E8]/20 transition-all hover:-translate-y-0.5">حفظ</button>
            </div>

        </form>
    </div>
</div>

<script>
    let previewItems = []; 
    let editingIndex = null;
    let itemCount    = 0;
    let currentImgSrc = null;

    function toggleButtonVisibility() {
        const btn = document.getElementById('template-toggle-btn');
        const label = document.getElementById('template-toggle-label');
        const hidden = document.getElementById('template-show-btn');
        const active = hidden.value === '1';

        if (!active) {
            hidden.value = '1';
            btn.className = 'flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-[#1A73E8] text-white border-[#1A73E8]';
        } else {
            hidden.value = '0';
            btn.className = 'flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-slate-100 text-slate-400 border-slate-200';
        }
    }

    function previewImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                currentImgSrc = e.target.result;
                document.getElementById('upload-placeholder').classList.add('hidden');
                const pv = document.getElementById('image-preview');
                pv.classList.remove('hidden');
                pv.classList.add('flex');
                document.getElementById('preview-img').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function addContentItem() {
        const title = document.getElementById('template-title').value.trim();
        const desc = document.getElementById('template-desc').value.trim();
        const btn = document.getElementById('template-btn-text').value.trim();
        const showBtn = document.getElementById('template-show-btn').value;
        const fileInp = document.getElementById('template-file-upload');

        if (!title && !currentImgSrc) {
            alert('أدخل عنوان أو صورة على الأقل');
            return;
        }

        const id = editingIndex !== null ? previewItems[editingIndex].id : itemCount++;
        
        // Handle file transfer
        let finalFileInp = null;
        if (fileInp.files.length > 0) {
            finalFileInp = fileInp;
            finalFileInp.id = `final-file-${id}`;
            finalFileInp.name = `items[${id}][image]`;
            finalFileInp.classList.add('hidden');
            
            const nextInp = finalFileInp.cloneNode(true);
            nextInp.id = 'template-file-upload';
            nextInp.name = '';
            nextInp.value = '';
            nextInp.classList.remove('hidden');
            finalFileInp.parentNode.replaceChild(nextInp, finalFileInp);
        } else if (editingIndex !== null) {
            finalFileInp = previewItems[editingIndex].fileInp;
        }

        const data = { id, title, desc, btn, showBtn, imgSrc: currentImgSrc, fileInp: finalFileInp };

        if (editingIndex !== null) {
            previewItems[editingIndex] = data;
            editingIndex = null;
        } else {
            previewItems.push(data);
        }

        renderPreviewCards();
        syncSubmissionInputs();
        clearTemplate();
    }

    function clearTemplate() {
        document.getElementById('template-title').value = '';
        document.getElementById('template-desc').value = '';
        document.getElementById('template-btn-text').value = '';
        document.getElementById('template-show-btn').value = '0';
        document.getElementById('upload-placeholder').classList.remove('hidden');
        document.getElementById('image-preview').classList.add('hidden');
        document.getElementById('template-file-upload').value = '';
        document.getElementById('template-toggle-btn').className = 'flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-slate-100 text-slate-400 border-slate-200';
        document.getElementById('add-content-btn').querySelector('span').textContent = 'إضافة للمحتوى';
        document.getElementById('edit-indicator').classList.add('hidden');
        currentImgSrc = null;
    }

    function renderPreviewCards() {
        const container = document.getElementById('previewCards');
        const section = document.getElementById('previewSection');
        if (previewItems.length === 0) {
            section.classList.add('hidden');
            return;
        }
        section.classList.remove('hidden');
        container.innerHTML = '';

        previewItems.forEach((item, i) => {
            const card = document.createElement('div');
            // Design matches user screenshot: solid vibrant blue, compact height, takes 1/3 grid
            card.className = "relative w-full h-[140px] rounded-[1.25rem] overflow-hidden shadow-sm flex items-center bg-[#1A73E8] cursor-pointer hover:ring-4 hover:ring-[#1A73E8]/30 transition-all";
            card.onclick = () => editItem(i);
            card.innerHTML = `
                <!-- Content Right (60%) -->
                <div class="relative z-10 px-5 py-3 w-[60%] h-full flex flex-col justify-center">
                    <h4 class="text-white font-black text-[14px] mb-1 leading-tight">${item.title || 'العنوان'}</h4>
                    <p class="text-blue-100 text-[10px] font-medium leading-snug mb-2 line-clamp-2">${item.desc || 'الوصف يظهر هنا'}</p>
                    ${item.showBtn === '1' ? `<div><span class="bg-white text-[#1A73E8] text-[9px] font-black px-4 py-1.5 rounded-lg inline-block whitespace-nowrap shadow-sm">${item.btn || 'أضف إعلانك'}</span></div>` : ''}
                </div>
                <!-- Image Left (40%) -->
                <div class="absolute left-0 bottom-0 top-0 w-[40%] h-full flex items-center justify-center p-2 overflow-hidden">
                    ${item.imgSrc ? `<img src="${item.imgSrc}" class="h-full w-full object-contain">` : '<div class="w-full h-full bg-white/10 rounded-xl"></div>'}
                </div>
                <!-- Edit layer (Overlay on hover) -->
                <div class="absolute inset-0 bg-black/40 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center gap-3 z-20">
                    <button type="button" onclick="editItem(${i})" class="w-10 h-10 bg-white text-[#1A73E8] rounded-xl flex items-center justify-center shadow-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                    <button type="button" onclick="removeItem(${i})" class="w-10 h-10 bg-red-500 text-white rounded-xl flex items-center justify-center shadow-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                </div>
            `;
            container.appendChild(card);
        });
    }

    function editItem(i) {
        const item = previewItems[i];
        editingIndex = i;
        document.getElementById('template-title').value = item.title;
        document.getElementById('template-desc').value = item.desc;
        document.getElementById('template-btn-text').value = item.btn;
        document.getElementById('template-show-btn').value = item.showBtn;
        
        if (item.imgSrc) {
            document.getElementById('upload-placeholder').classList.add('hidden');
            const pv = document.getElementById('image-preview');
            pv.classList.remove('hidden');
            pv.classList.add('flex');
            document.getElementById('preview-img').src = item.imgSrc;
            currentImgSrc = item.imgSrc;
        }
        
        const btn = document.getElementById('template-toggle-btn');
        if (item.showBtn === '1') {
            btn.className = 'flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-[#1A73E8] text-white border-[#1A73E8]';
        } else {
            btn.className = 'flex items-center px-5 py-4 rounded-2xl font-black text-sm shrink-0 border transition-all duration-200 bg-slate-100 text-slate-400 border-slate-200';
        }
        
        document.getElementById('add-content-btn').querySelector('span').textContent = 'حفظ التعديل';
        document.getElementById('edit-indicator').classList.remove('hidden');
        document.getElementById('templateSection').scrollIntoView({ behavior: 'smooth' });
    }

    function removeItem(i) {
        previewItems.splice(i, 1);
        renderPreviewCards();
        syncSubmissionInputs();
    }

    function syncSubmissionInputs() {
        const target = document.getElementById('submissionInputs');
        target.innerHTML = '';
        previewItems.forEach((item, i) => {
            const h = (n,v) => { const inp = document.createElement('input'); inp.type='hidden'; inp.name=`items[${i}][${n}]`; inp.value=v; target.appendChild(inp); };
            h('title', item.title);
            h('description', item.desc);
            h('button_text', item.btn);
            h('show_button', item.showBtn);
            if (item.fileInp) target.appendChild(item.fileInp);
        });
    }
</script>
@endsection
