@extends('layouts.admin')

@section('title', 'إدارة مناطق ومدن المزاد')

@section('content')
<div class="container-fluid mx-auto px-4 sm:px-6 lg:px-8 py-8" dir="rtl" x-data="cityManager()">
    
    <!-- Header Section -->
    <div class="mb-10">
        <h1 class="text-3xl font-black text-[#1C1C1C] mb-2 ">إدارة المناطق والمدن</h1>
    </div>

    <div class="flex flex-col mt-4 md:flex-row md:items-center justify-between gap-6 mb-12">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 md:w-auto w-full">
            <!-- Search Bar -->
            <form action="{{ route('admin.cities.index') }}" method="GET" class="relative group md:w-[350px] w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="ابحث" 
                       class="w-full pl-10 pr-12 py-4 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:ring-4 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all shadow-sm placeholder:text-slate-400">
                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-slate-400 group-focus-within:text-[#1A73E8] transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </form>

            <button @click="openAddModal()" 
                    class="px-8 py-4 bg-[#1A73E8] text-white rounded-xl font-black text-sm hover:translate-y-[-2px] hover:shadow-xl hover:shadow-[#1A73E8]/20 transition-all flex items-center justify-center gap-3 group">
                <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>إضافة مدينة</span>
            </button>
        </div>
    </div>

    <!-- Cities Grid -->
    <div class="{{ $regions->isEmpty() ? 'flex flex-col items-center justify-center min-h-[50vh]' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8' }}">
        @forelse($regions as $region)
            <div class="group bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all p-4 flex items-center justify-between">
                <!-- Right: Content -->
                <div class="text-right">
                    <h3 class="text-base font-black text-[#1C1C1C] mb-1 leading-tight">{{ $region->name }}</h3>
                    <div class="flex items-center justify-end gap-3 text-slate-500">
                        <div class="flex items-center gap-1.5 order-2">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>   
                            <span class="text-[11px] font-bold">{{ number_format($region->users_count) }} مستخدم</span>
                        </div>
                        <div class="flex items-center gap-1.5 order-1">
                        <img class="w-3.5 h-3.5 text-slate-400" src="{{ asset('images/legal - outline.svg') }}" alt="">    
                        <span class="text-[11px] font-bold">{{ number_format($region->ads_count) }} إعلان</span>
                        </div>
                    </div>
                </div>  
            
                <!-- Left: Actions -->
                <div class="flex items-center gap-2">
                    <button @click="confirmDelete('{{ route('admin.cities.destroy', $region->id) }}')" 
                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-500/40 hover:bg-red-50 hover:text-red-500 transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    <button @click="openEditModal({{ $region->toJson() }})" 
                            class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-500/40 hover:bg-[#1A73E8]/10 hover:text-[#1A73E8] transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center text-center p-10 md:p-20 bg-white rounded-[3rem] border border-slate-100 shadow-2xl max-w-2xl w-full transition-all duration-500 animate-in fade-in zoom-in">
                <div class="w-32 h-32 bg-slate-50 rounded-full flex items-center justify-center mb-10 text-slate-300">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h3 class="text-3xl font-black text-slate-400 mb-4">لا توجد مناطق حالياً</h3>
                <p class="text-slate-400 font-bold max-w-xs">يمكنك البدء بإضافة مدن جديدة من الزر أعلاه</p>
            </div>
        @endforelse
    </div>

    @if($regions->hasPages())
        <div class="mt-16 flex justify-center">
            <x-custom-pagination :paginator="$regions" />
        </div>
    @endif

    <!-- REDESIGNED Modal for Add/Edit -->
    <div x-show="modalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div @click.away="closeModal()" 
             class="bg-white w-[95%] max-w-[360px] rounded-3xl shadow-2xl overflow-hidden transform transition-all flex flex-col max-h-[85vh]"
             x-show="modalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <!-- Modal Header -->
            <div class="px-4 py-3.5 flex items-center justify-between border-b border-slate-100 shrink-0">
                <button @click="closeModal()" class="w-7 h-7 flex items-center justify-center rounded-full text-slate-400 hover:bg-slate-50 hover:text-slate-600 transition-colors focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
                <h3 class="text-base font-black text-[#1C1C1C]" x-text="editMode ? 'تعديل بيانات المدينة' : 'إضافة مدينة'"></h3>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-4 overflow-y-auto custom-scrollbar flex-1">
                <form :action="editMode ? '{{ url('admin/cities') }}/' + (currentRegion ? currentRegion.id : '') : '{{ route('admin.cities.store') }}'" method="POST" id="mainForm" class="space-y-4">
                    @csrf
                    <template x-if="editMode">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    
                    <!-- City Name Field -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 text-right">اسم المدينة</label>
                        <input type="text" name="name" x-model="cityName" required
                               placeholder="أدخل اسم المدينة"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-300 placeholder:font-normal text-right shadow-sm">
                    </div>

                    <!-- Visual Divider -->
                    <hr class="border-slate-100 my-3">

                    <!-- Areas Section -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5 text-right">اسم المنطقة</label>
                        <div class="flex gap-2">
                            <input type="text" x-model="newAreaName" placeholder="اسم المنطقة"
                                   @keyup.enter="addArea()"
                                   class="flex-1 px-3 py-2 bg-white border border-slate-200 rounded-xl text-sm font-bold text-[#1C1C1C] focus:ring-2 focus:ring-[#1A73E8]/10 focus:border-[#1A73E8] transition-all outline-none placeholder:text-slate-300 placeholder:font-normal text-right shadow-sm">
                            <button type="button" @click="addArea()"
                                    class="px-4 py-2 bg-[#1A73E8] text-white rounded-xl font-bold text-xs hover:shadow-md hover:shadow-[#1A73E8]/20 transition-all shrink-0">
                                أضف
                            </button>
                        </div>

                        <!-- Areas List (Auto Expanding, scrolls with page) -->
                        <div class="mt-3 space-y-2" x-show="areas.length > 0">
                            <template x-for="(area, index) in areas" :key="index">
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 px-3 py-1.5 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-between transition-all hover:bg-white hover:border-slate-200">
                                        <!-- Spacer or Counter for alignment -->
                                        <div>
                                            <template x-if="area.users_count !== undefined">
                                                <span class="px-1.5 py-0.5 bg-[#1A73E8]/10 rounded font-black text-[#1A73E8]" style="font-size: 8px;" x-show="area.users_count > 0" x-text="(area.users_count || 0) + ' مستخدم'"></span>
                                            </template>
                                        </div>
                                        <div class="flex flex-col items-end">
                                            <span class="text-xs font-bold text-[#1C1C1C]" x-text="area.name"></span>
                                            <!-- Hidden inputs for Add Mode -->
                                            <template x-if="!editMode">
                                                <input type="hidden" name="areas[]" :value="area.name">
                                            </template>
                                        </div>
                                    </div>
                                    <button type="button" @click="deleteArea(area, index)"
                                            class="w-[34px] h-[34px] flex items-center justify-center rounded-xl bg-[#FFF5F5] text-[#FF4D4F] hover:bg-red-500  transition-all shrink-0 border border-transparent hover:border-red-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Modal Footer -->
            <!-- justify-end pushes to LEFT in RTL -->
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50 shrink-0 flex items-center justify-end gap-2">
                <button type="submit" form="mainForm"
                        class="px-5 py-2 bg-[#1A73E8] text-white rounded-xl font-bold text-xs hover:shadow-md hover:shadow-[#1A73E8]/20 transition-all"
                        x-text="editMode ? 'حفظ' : 'إضافة المدينة'">
                </button>
                <template x-if="editMode">
                    <button @click="closeModal()" class="px-4 py-2 bg-white border border-slate-200 text-slate-500 font-bold text-xs rounded-xl hover:bg-slate-100 transition-all hover:text-slate-700">إلغاء</button>
                </template>
            </div>
        </div>
    </div>

    <!-- CUSTOM Delete Confirmation Modal -->
    <div x-show="deleteModalOpen" 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white w-full max-w-md rounded-md p-10 text-center shadow-2xl transform transition-all"
             x-show="deleteModalOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-8"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            
            <div class="w-24 h-24 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-8 text-red-500">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>

            <h3 class="text-2xl font-black text-[#1C1C1C] mb-4">هل أنت متأكد؟</h3>
            <p class="text-slate-500 font-bold mb-10 leading-relaxed text-sm">سيتم حذف هذه المدينة وجميع المناطق التابعة لها بشكل نهائي. لا يمكن التراجع عن هذا الإجراء.</p>

            <div class="flex flex-col gap-3">
                <form :action="deleteUrl" method="POST" class="w-full">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-4 bg-red-500 text-white rounded-2xl font-black text-sm hover:bg-red-600 transition-all shadow-lg shadow-red-500/20">تأكيد الحذف</button>
                </form>
                <button @click="deleteModalOpen = false" class="w-full py-4 bg-slate-100 text-slate-500 rounded-2xl font-black text-sm hover:bg-slate-200 transition-all">إلغاء</button>
            </div>
        </div>
    </div>

</div>

<script>
function cityManager() {
    return {
        modalOpen: false,
        deleteModalOpen: false,
        deleteUrl: '',
        editMode: false,
        cityName: '',
        newAreaName: '',
        areas: [],
        currentRegion: null,

        openAddModal() {
            this.editMode = false;
            this.cityName = '';
            this.currentRegion = null;
            this.areas = [];
            this.modalOpen = true;
        },

        openEditModal(region) {
            this.editMode = true;
            this.currentRegion = region;
            this.cityName = region.name;
            this.areas = region.cities || [];
            this.modalOpen = true;
        },

        closeModal() {
            this.modalOpen = false;
        },

        confirmDelete(url) {
            this.deleteUrl = url;
            this.deleteModalOpen = true;
        },

        async addArea() {
            if (!this.newAreaName.trim()) return;

            if (this.editMode) {
                // Persistent Add (Server-side)
                try {
                    const response = await fetch(`{{ url('admin/cities') }}/${this.currentRegion.id}/areas`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ name: this.newAreaName })
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.status) {
                            this.areas.push(result.data || { id: Date.now(), name: this.newAreaName, users_count: 0 });
                            this.newAreaName = '';
                        } else {
                            window.location.reload();
                        }
                    }
                } catch (error) {
                    console.error('Error adding area:', error);
                }
            } else {
                // Temporary Add (Local array)
                this.areas.push({ name: this.newAreaName });
                this.newAreaName = '';
            }
        },

        async deleteArea(area, index) {
            if (this.editMode && area.id) {
                // Persistent Delete (Server-side)
                try {
                    const response = await fetch(`{{ url('admin/cities/areas') }}/${area.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (response.ok) {
                        this.areas.splice(index, 1);
                    }
                } catch (error) {
                    console.error('Error deleting area:', error);
                }
            } else {
                // Local Delete
                this.areas.splice(index, 1);
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }

.font-display { font-family: 'Inter', 'Outfit', sans-serif; }

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

/* Custom Pagination Styling */
.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}
.page-item .page-link {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border: 1px solid #f1f5f9;
    border-radius: 1rem;
    color: #64748b;
    font-weight: 800;
    font-size: 0.9rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
}
.page-item.active .page-link {
    background: #1A73E8;
    color: white;
    border-color: #1A73E8;
    box-shadow: 0 10px 15px -3px rgba(26, 115, 232, 0.3);
}
.page-item:hover:not(.active) .page-link {
    background: #f8fafc;
    color: #1A73E8;
    transform: translateY(-2px);
}
</style>
@endsection
