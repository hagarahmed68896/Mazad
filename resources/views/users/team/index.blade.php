@extends('layouts.main')

@section('title', 'أعضاء الفريق | مزاد')

@push('styles')
<style>
    .team-tab-btn {
        transition: all 0.3s ease;
        border-radius: 20px;
    }
    .team-tab-btn.active {
        background-color: #1A73E8;
        color: white !important;
        box-shadow: 0 10px 15px -3px rgba(26, 115, 232, 0.15);
    }
    .team-card {
        border-radius: 24px;
        transition: all 0.3s ease;
        border: 1px solid #F1F5F9;
        background: white;
    }
    .team-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.05);
        border-color: #E2E8F0;
    }
    .team-trash-btn {
        transition: all 0.2s ease;
    }
    .team-trash-btn:hover {
        background-color: #FEE2E2;
        color: #EF4444 !important;
        transform: scale(1.1);
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-10" dir="rtl">
    <div class="container mx-auto px-4 lg:px-24">

        <!-- Breadcrumbs & Title -->
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
            <div class="flex items-center gap-2 text-sm font-bold text-gray-500">
                <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <i class="fa-solid fa-chevron-left text-xs"></i>
                <span class="text-white bg-primary px-4 py-1.5 rounded-full text-xs">أعضاء الفريق</span>
            </div>
        </div>

        <div class="text-center max-w-2xl mx-auto mb-10">
            <h1 class="text-3xl lg:text-4xl font-black text-gray-900 mb-2">أعضاء الفريق</h1>
        </div>

        <!-- Custom tabs & Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <!-- Tabs (Representative / Partner) -->
            <div class="flex bg-white p-1.5 rounded-[24px] border border-gray-100 shadow-sm max-w-xs w-full">
                <a href="{{ route('team.index', ['tab' => 'representative', 'q' => $search]) }}" class="team-tab-btn flex-1 text-center py-2.5 font-black text-sm text-gray-600 {{ $tab === 'representative' ? 'active' : '' }}">
                    المندوبين
                </a>
                <a href="{{ route('team.index', ['tab' => 'partner', 'q' => $search]) }}" class="team-tab-btn flex-1 text-center py-2.5 font-black text-sm text-gray-600 {{ $tab === 'partner' ? 'active' : '' }}">
                    مديرين الحساب
                </a>
            </div>

            <!-- Search input -->
            <form action="{{ route('team.index') }}" method="GET" class="relative max-w-md w-full">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input type="text" name="q" value="{{ $search }}" placeholder="ابحث عن..." class="w-full bg-white border border-gray-200 rounded-[20px] py-3.5 pr-12 pl-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right">
                <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary transition-colors">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </button>
            </form>
        </div>

        <!-- Add representative & Members grid -->
        <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 min-h-[500px]">
            
            <div class="flex justify-between items-center mb-8 border-b border-gray-100 pb-4">
                <h3 class="text-xl font-black text-gray-900 text-right">
                    @if($tab === 'representative') قائمة المندوبين المعتمدين @else قائمة مدراء الحساب @endif
                </h3>
                
                <a href="{{ route('team.create', ['type' => $tab === 'representative' ? 'representative' : 'partner']) }}" class="bg-primary hover:bg-blue-700 text-white font-black py-3 px-6 rounded-2xl text-sm transition-all shadow-md shadow-blue-100 flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>إضافة @if($tab === 'representative') مندوب @else مدير @endif</span>
                </a>
            </div>

            @if($members->isEmpty())
                <div class="text-center py-16">
                    <div class="w-20 h-20 bg-blue-50 text-primary rounded-full flex items-center justify-center text-3xl mx-auto mb-6">
                        <i class="fa-solid fa-users-slash"></i>
                    </div>
                    <h3 class="text-lg font-black text-gray-900 mb-2">لا توجد نتائج مطابقة</h3>
                    <p class="text-gray-400 font-bold text-sm mb-6">لم يتم العثور على أي أعضاء مسجلين تحت هذا التبويب.</p>
                </div>
            @else
                <!-- Members Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($members as $member)
                        @if($tab === 'representative')
                            <!-- Representative Card -->
                            <div class="team-card p-5 relative flex items-center justify-between text-right" id="member-card-{{ $member->id }}">
                                
                                <!-- Action Buttons (Left) -->
                                <div class="flex items-center gap-2">
                                    <button onclick="deleteMember({{ $member->id }}, '{{ $member->name }}')" class="team-trash-btn w-9 h-9 bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 rounded-full flex items-center justify-center cursor-pointer shadow-sm border border-gray-100 transition-all">
                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                    </button>
                                    <a href="{{ route('team.edit', $member->id) }}" class="w-9 h-9 bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 rounded-full flex items-center justify-center cursor-pointer shadow-sm border border-gray-100 transition-all">
                                        <i class="fa-regular fa-pen-to-square text-sm"></i>
                                    </a>
                                </div>

                                <!-- Member Info (Left inside card) -->
                                <div class="flex-1 pr-4 pl-4 text-right">
                                    <h4 class="text-base font-black text-gray-900 mb-1.5">{{ $member->name }}</h4>
                                    
                                    <span class="inline-block bg-[#E8F1FE] text-primary text-xs font-black px-3.5 py-1.5 rounded-full mb-2">
                                        {{ $member->ads_count }} إعلانات
                                    </span>

                                    <a href="{{ route('users.profile', $member->id) }}" class="block text-primary font-black text-xs hover:underline mt-1">
                                        عرض جميع الإعلانات
                                    </a>
                                </div>

                                <!-- Member Avatar (Right inside card) -->
                                <div class="w-20 h-20 rounded-2xl overflow-hidden border border-gray-100 shadow-inner flex-shrink-0 flex items-center justify-center bg-gray-50">
                                    @if($member->avatar)
                                        <img src="{{ $member->avatar }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-[#1A73E8]/10 text-[#1A73E8] flex items-center justify-center font-bold text-xl uppercase">
                                            {{ mb_substr($member->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>

                            </div>
                        @else
                            <!-- Manager Card -->
                            <div class="team-card p-5 flex items-center justify-between text-right" id="member-card-{{ $member->id }}">
                                
                                <!-- Action Buttons (Left) -->
                                <div class="flex items-center gap-2">
                                    <button onclick="deleteMember({{ $member->id }}, '{{ $member->name }}')" class="team-trash-btn w-9 h-9 bg-gray-50 text-gray-400 hover:bg-red-50 hover:text-red-500 rounded-full flex items-center justify-center cursor-pointer shadow-sm border border-gray-100 transition-all">
                                        <i class="fa-regular fa-trash-can text-sm"></i>
                                    </button>
                                    <a href="{{ route('team.edit', $member->id) }}" class="w-9 h-9 bg-gray-50 text-gray-400 hover:bg-gray-100 hover:text-gray-600 rounded-full flex items-center justify-center cursor-pointer shadow-sm border border-gray-100 transition-all">
                                        <i class="fa-regular fa-pen-to-square text-sm"></i>
                                    </a>
                                </div>

                                <!-- Member Info (Center) -->
                                <div class="flex-1 pr-4 pl-4 text-right">
                                    <h4 class="text-base font-black text-gray-900 mb-1">{{ $member->name }}</h4>
                                    <p class="text-gray-400 font-bold text-xs">مدير الحساب</p>
                                </div>

                                <!-- Member Avatar (Right) -->
                                <div class="w-20 h-20 rounded-2xl overflow-hidden border border-gray-100 shadow-inner flex-shrink-0 flex items-center justify-center bg-gray-50">
                                    @if($member->avatar)
                                        <img src="{{ $member->avatar }}" alt="{{ $member->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-[#1A73E8]/10 text-[#1A73E8] flex items-center justify-center font-bold text-xl uppercase">
                                            {{ mb_substr($member->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>

                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Custom styled pagination -->
                <div class="mt-12 flex justify-center border-t border-gray-100 pt-6">
                    {{ $members->links() }}
                </div>
            @endif

        </div>

    </div>
</div>

@push('scripts')
<script>
    function deleteMember(id, name) {
        Swal.fire({
            title: 'هل أنت متأكد؟',
            text: `سيتم حذف المندوب/العضو "${name}" بالكامل وإلغاء صلاحية وصوله للمنصة.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'نعم، احذفه',
            cancelButtonText: 'إلغاء',
            customClass: {
                popup: 'rounded-[32px]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Perform AJAX delete request
                fetch(`/settings/team/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove member card from DOM with anim
                        const card = document.getElementById(`member-card-${id}`);
                        if (card) {
                            card.style.transition = 'all 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(() => {
                                card.remove();
                                // Reload page if grid is empty
                                if (document.querySelectorAll('.team-card').length === 0) {
                                    location.reload();
                                }
                            }, 300);
                        }
                        
                        Swal.fire({
                            title: 'تم الحذف!',
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false,
                            customClass: { popup: 'rounded-[32px]' }
                        });
                    } else {
                        Swal.fire('خطأ', data.message || 'فشل حذف عضو الفريق', 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('خطأ', 'حدث خلل في الاتصال بالخادم.', 'error');
                });
            }
        });
    }
</script>
@endpush
@endsection
