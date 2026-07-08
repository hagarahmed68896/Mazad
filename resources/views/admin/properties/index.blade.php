@extends('layouts.admin')

@section('title', 'تعديل بيانات الخصائص')

@section('content')
<!-- Delete Confirmation Modal -->
<div x-data="{ show: false, deleteUrl: '' }"
     x-on:open-delete-modal-index.window="show = true; deleteUrl = $event.detail.url"
     x-show="show"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center"
     style="display:none">

    <!-- Backdrop -->
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="show = false"></div>

    <!-- Modal Box -->
    <div class="relative bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md mx-4 text-center"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">

        <!-- Icon -->
        <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mx-auto mb-5">
            <svg class="w-8 h-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h3 class="text-xl font-black text-[#1C1C1C] mb-2">تأكيد حذف الخاصية</h3>
        <p class="text-sm text-slate-400 font-bold mb-8">هل أنت متأكد من رغبتك في حذف هذه الخاصية؟<br>لا يمكن التراجع عن هذا الإجراء.</p>

        <div class="flex items-center gap-3" dir="rtl">
            <button type="button"
                    @click="show = false"
                    class="flex-1 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl font-black text-sm transition-all active:scale-95">
                إلغاء
            </button>
            <button type="button"
                    @click="$dispatch('confirm-delete-index', { url: deleteUrl }); show = false"
                    class="flex-1 py-3.5 bg-red-500 hover:bg-red-600 text-white rounded-2xl font-black text-sm shadow-lg shadow-red-500/20 transition-all active:scale-95">
                نعم، احذف
            </button>
        </div>
    </div>
</div>

<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl">

    <!-- Header & Title -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('admin.categories.index') }}" class="p-2 bg-white border border-slate-200 rounded-xl text-slate-400 hover:text-[#1A73E8] transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
        </a>
        <h1 class="text-2xl font-black text-[#1C1C1C]">تعديل بيانات الخصائص</h1>
    </div>

    @if(session('success'))
        <div class="max-w-4xl px-5 py-4 bg-green-50 border border-green-200 rounded-2xl text-green-700 font-bold mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-6 max-w-4xl px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 font-bold text-sm space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <!-- Main Form Container -->
    <div class="max-w-4xl bg-[#F8F9FA] border border-slate-100 rounded-3xl p-6 shadow-sm relative" x-data="propertiesIndex()" @confirm-delete-index.window="performDelete($event.detail.url)">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-black text-[#1C1C1C] text-right">بيانات الخصائص</h2>
            <a href="{{ route('admin.categories.properties.create', $category->id) }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-[#1A73E8] text-white rounded-xl text-sm font-black shadow-sm hover:bg-blue-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                أضف حقل ادخال
            </a>
        </div>

        <div class="space-y-6" id="sortable-list">

            <!-- الفئة -->
            <div class="ns-ignore">
                <label class="block text-sm font-black text-[#6F6F6F] mb-2 text-right">الفئة</label>
                <input type="text" readonly value="{{ $category->name }}"
                       class="w-full px-5 py-3.5 bg-white border border-slate-200 rounded-xl text-sm font-bold text-slate-500 cursor-not-allowed">
            </div>

            {{-- ======================================================= --}}
            {{-- Pages with their assigned properties --}}
            {{-- ======================================================= --}}
            @foreach($pages as $page)
                <div class="ns-ignore">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-1 h-px bg-slate-200"></div>
                        <span class="text-xs font-black whitespace-nowrap px-3 py-1 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg">
                            📄 {{ $page->name }}
                        </span>
                        <div class="flex-1 h-px bg-slate-200"></div>
                    </div>
                </div>

                @forelse($page->properties as $property)
                    @php
                        $opts = is_array($property->options) ? $property->options : json_decode($property->options, true) ?? [];
                        if (empty($opts)) $opts = [''];
                    @endphp
                    @include('admin.properties._property_row', compact('category','property','opts'))
                @empty
                    <div class="py-4 text-center border border-dashed border-slate-200 rounded-2xl bg-white ns-ignore">
                        <p class="text-slate-400 font-bold text-sm">لا توجد خصائص لهذه الصفحة بعد</p>
                    </div>
                @endforelse
            @endforeach

            {{-- ======================================================= --}}
            {{-- General properties (no page assigned) --}}
            {{-- ======================================================= --}}
            @if($properties->isNotEmpty())
                <div class="ns-ignore">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex-1 h-px bg-amber-200"></div>
                        <span class="text-xs font-black whitespace-nowrap px-3 py-1 bg-amber-50 border border-amber-300 text-amber-700 rounded-lg">
                            ⚠️ خصائص عامة (بدون صفحة)
                        </span>
                        <div class="flex-1 h-px bg-amber-200"></div>
                    </div>
                    <p class="text-xs text-amber-600 font-bold text-right mb-3">هذه الخصائص غير مرتبطة بأي صفحة. لربطها بصفحة، احذفها وأضفها مجدداً مع تحديد الصفحة.</p>
                </div>
            @endif

            @forelse($properties as $property)
                @php
                    $opts = is_array($property->options) ? $property->options : json_decode($property->options, true) ?? [];
                    if (empty($opts)) $opts = [''];
                @endphp
                @include('admin.properties._property_row', compact('category','property','opts'))
            @empty
                @if($pages->isEmpty())
                    <div class="py-12 text-center border-2 border-dashed border-slate-200 rounded-2xl bg-white">
                        <p class="text-slate-400 font-bold mb-3">لا توجد خصائص، جرب إضافة خاصية جديدة</p>
                    </div>
                @endif
            @endforelse

        </div>

    </div>
</div>

@push('scripts')
<script>
    function getTypeLabel(val) {
        const types = {
            'text': 'النصوص',
            'number': 'الأرقام',
            'price': 'الأسعار',
            'radio': 'اختيار واحد',
            'checkbox': 'اختيار متعدد',
            'date': 'التواريخ والأوقات',
            'file': 'الملفات والصور',
            'map': 'الموقع الجغرافي'
        };
        return types[val] || 'حدد نوع حقل الادخال';
    }

    function getStatusLabel(val) {
        if(val === '1' || val === true) return 'إلزامي';
        if(val === '0' || val === false) return 'اختياري';
        return 'حدد الحالة';
    }

    function getHideableLabel(val) {
        if(val === '1' || val === 1 || val === true || val === 'true') return 'مسموح بالإخفاء';
        return 'تظهر دائماً';
    }

    function propertiesIndex() {
        return {
            performDelete(url) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;

                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('sortable-list');
        var sortable = Sortable.create(el, {
            handle: '.cursor-move',
            animation: 150,
            filter: '.ns-ignore',
            onEnd: function () {
                let order = [];
                document.querySelectorAll('.property-item').forEach((item) => {
                    order.push(item.getAttribute('data-id'));
                });

                fetch("{{ route('admin.categories.properties.reorder', $category->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ order: order })
                }).then(res => res.json()).then(data => {
                    if(data.success) {
                        // Order saved silently
                    }
                }).catch(err => console.error(err));
            }
        });
    });
</script>
@endpush
@endsection
