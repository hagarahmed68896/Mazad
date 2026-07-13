@extends('layouts.main')

@section('title', 'طلبات الخدمات الواردة | مزاد')

@section('content')
<div class="partner-orders-page bg-[#F8FAFC] min-h-screen py-12" dir="rtl" style="text-align: right; font-family: 'Cairo', sans-serif;">
    <div class="container mx-auto px-4 md:px-16">
        
        <!-- Header & Tabs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
            <div>
                <nav class="bg-primary px-5 py-2.5 w-fit rounded-full flex items-center gap-2.5 text-white text-xs font-bold shadow-lg shadow-primary/20 mb-4">
                    <a href="{{ route('ads.my') }}" class="hover:text-blue-100 transition-colors">لوحة تحكم الشريك</a>
                    <i class="fa-solid fa-chevron-left text-[8px] opacity-50"></i>
                    <span class="text-blue-100">طلبات الخدمات</span>
                </nav>
                <h1 class="text-3xl font-black text-[#1C1C1C]">طلبات الخدمات الواردة</h1>
            </div>

            <!-- Tabs -->
            <div class="flex gap-2 bg-slate-100 p-1.5 rounded-2xl w-fit">
                <a href="{{ route('partner.service-orders', ['status' => 'all']) }}" 
                   class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all {{ $status === 'all' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">الكل</a>
                <a href="{{ route('partner.service-orders', ['status' => 'paid']) }}" 
                   class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all {{ $status === 'paid' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">بانتظار الإنجاز</a>
                <a href="{{ route('partner.service-orders', ['status' => 'completed']) }}" 
                   class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all {{ $status === 'completed' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-800' }}">المكتملة</a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 px-6 py-4 rounded-2xl mb-8 font-bold text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-100 text-rose-800 px-6 py-4 rounded-2xl mb-8 font-bold text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-xmark text-rose-600 text-lg"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($orders->isEmpty())
            <!-- Empty State -->
            <div class="bg-white border border-slate-100 rounded-[32px] p-12 text-center max-w-xl mx-auto shadow-sm">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-regular fa-folder-open text-3xl text-slate-400"></i>
                </div>
                <h3 class="text-xl font-black text-[#1C1C1C] mb-2">لا توجد طلبات</h3>
                <p class="text-sm text-slate-400 font-bold leading-relaxed mb-6">لا توجد أي طلبات خدمات تطابق خيارات التصفية الحالية.</p>
                <a href="{{ route('ads.my') }}" class="btn btn-primary px-8 py-3 rounded-xl font-bold text-xs">العودة للوحة خدماتي</a>
            </div>
        @else
            <!-- Table Container -->
            <div class="bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden mb-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 font-black text-xs text-slate-500 uppercase tracking-wider">
                                <th class="p-6">رقم الطلب</th>
                                <th class="p-6">العميل</th>
                                <th class="p-6">الخدمة</th>
                                <th class="p-6">السعر</th>
                                <th class="p-6">الحالة</th>
                                <th class="p-6">تاريخ الطلب</th>
                                <th class="p-6">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm font-bold text-[#1C1C1C]">
                            @foreach($orders as $order)
                                <tr class="hover:bg-slate-50/30 transition-colors">
                                    <td class="p-6 text-primary">#{{ $order->order_number }}</td>
                                    <td class="p-6">
                                        <div class="flex flex-col">
                                            <span>{{ $order->buyer->name }}</span>
                                            <span class="text-[10px] text-slate-400 font-bold mt-0.5">{{ $order->buyer->phone }}</span>
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <div class="max-w-[200px] truncate">
                                            {{ $order->service->title ?? ($order->ad->title ?? '-') }}
                                        </div>
                                    </td>
                                    <td class="p-6">{{ number_format($order->price) }} ريال</td>
                                    <td class="p-6">
                                        @if($order->status === 'paid')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-100 text-amber-700 text-xs font-black">
                                                <i class="fa-solid fa-clock"></i>
                                                <span>بانتظار تقرير الإنجاز</span>
                                            </span>
                                        @elseif($order->status === 'under_review')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 text-xs font-black">
                                                <i class="fa-solid fa-spinner fa-spin"></i>
                                                <span>قيد المراجعة</span>
                                            </span>
                                        @elseif($order->status === 'completed')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-xs font-black">
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span>تم التسليم</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-6 text-slate-400 font-semibold">{{ $order->created_at->format('Y-m-d') }}</td>
                                    <td class="p-6">
                                        @if($order->status === 'paid')
                                            <button onclick="openCompleteOrderModal({{ $order->id }}, '{{ $order->order_number }}')" 
                                                    class="px-4 py-2 bg-primary hover:bg-blue-700 text-white rounded-xl font-black text-xs transition-all flex items-center gap-1.5 shadow-md shadow-blue-100">
                                                <i class="fa-solid fa-file-arrow-up"></i>
                                                <span>إرسال تقرير الإنجاز</span>
                                            </button>
                                        @elseif($order->status === 'completed' || $order->status === 'under_review')
                                            <button onclick="viewReportDetails({{ json_encode($order->report_text) }}, '{{ $order->report_file ? asset($order->report_file) : '' }}')" 
                                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-black text-xs transition-all flex items-center gap-1.5">
                                                <i class="fa-regular fa-file-lines"></i>
                                                <span>عرض التقرير المرسل</span>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            @if($orders->hasPages())
                <div class="flex justify-center mt-8">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

<!-- Modal: Complete Order & Send Report -->
<div id="completeOrderModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">
    <div class="bg-white w-full max-w-xl rounded-[32px] overflow-hidden shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-300">
        <!-- Header -->
        <div class="relative bg-gradient-to-br from-primary to-blue-700 p-6 text-white text-right">
            <h3 class="text-lg font-black" id="modalTitle">إرسال تقرير إنجاز الخدمة</h3>
            <p class="text-blue-100 text-xs font-semibold mt-1">يرجى توثيق ما تم إنجازه وإرفاق التقرير لتسوية المستحقات المالية.</p>
            <button onclick="closeCompleteOrderModal()" class="absolute top-5 left-5 w-8 h-8 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <!-- Form Body -->
        <form id="completeOrderForm" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-3">تفاصيل التقرير والعمل المنجز</label>
                <textarea name="report_text" required placeholder="يرجى كتابة تفاصيل الخدمة التي تم إنجازها والنتائج والبيانات المتاحة للعميل..."
                          class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-bold text-[#1C1C1C] focus:bg-white focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all outline-none h-40 resize-none"></textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-3">إرفاق ملف التقرير أو إيصال (اختياري)</label>
                <div class="border-2 border-dashed border-slate-200 rounded-2xl p-6 text-center hover:bg-slate-50 transition-colors cursor-pointer relative" onclick="document.getElementById('report_file_input').click()">
                    <input type="file" name="report_file" id="report_file_input" class="hidden" onchange="handleFileSelect(this)">
                    <div id="upload-placeholder">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                        <h4 class="text-sm font-black text-[#1C1C1C]">اضغط لرفع الملف</h4>
                        <p class="text-xs text-slate-400 font-semibold mt-1">PDF, JPG, PNG, DOC (حتى 10 ميجا)</p>
                    </div>
                    <div id="upload-preview" class="hidden flex items-center justify-center gap-2 text-primary font-bold text-sm">
                        <i class="fa-solid fa-file-circle-check text-lg"></i>
                        <span id="selected-file-name"></span>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-4 bg-primary text-white rounded-2xl font-black text-sm hover:bg-blue-700 shadow-lg shadow-blue-100 transition-all flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane"></i>
                <span>تقديم التقرير وتسليم الخدمة</span>
            </button>
        </form>
    </div>
</div>

<!-- Modal: View Report Details -->
<div id="viewReportModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">
    <div class="bg-white w-full max-w-xl rounded-[32px] overflow-hidden shadow-2xl border border-slate-100 animate-in fade-in zoom-in duration-300">
        <!-- Header -->
        <div class="relative bg-slate-50 p-6 border-b border-slate-100 text-right">
            <h3 class="text-lg font-black text-gray-900">تقرير الإنجاز المقدم</h3>
            <button onclick="closeViewReportModal()" class="absolute top-5 left-5 w-8 h-8 bg-slate-100 hover:bg-slate-200 rounded-full flex items-center justify-center text-slate-500 transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <!-- Body -->
        <div class="p-6 space-y-6">
            <div>
                <h4 class="text-xs font-black text-slate-400 mb-2 uppercase">تفاصيل التقرير والعمل المنجز</h4>
                <div class="bg-slate-50 rounded-2xl p-5 text-sm font-bold text-slate-700 leading-relaxed max-h-60 overflow-y-auto" id="viewReportText"></div>
            </div>
            <div id="viewReportFileContainer" class="hidden">
                <h4 class="text-xs font-black text-slate-400 mb-3 uppercase">الملف المرفق بالتقرير</h4>
                <a id="viewReportFileLink" href="" target="_blank" class="flex items-center justify-between p-4 bg-blue-50/50 border border-blue-100 rounded-2xl text-primary font-bold text-sm hover:bg-blue-50 transition-colors">
                    <span class="flex items-center gap-2.5">
                        <i class="fa-solid fa-file-arrow-down text-lg"></i>
                        <span>تحميل وقراءة الملف المرفق</span>
                    </span>
                    <i class="fa-solid fa-chevron-left text-xs opacity-50"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function openCompleteOrderModal(orderId, orderNumber) {
        const modal = document.getElementById('completeOrderModal');
        const form = document.getElementById('completeOrderForm');
        document.getElementById('modalTitle').textContent = `إرسال تقرير إنجاز الخدمة للطلب #${orderNumber}`;
        form.action = `/partner/service-orders/${orderId}/complete`;
        
        // Reset upload elements
        document.getElementById('report_file_input').value = '';
        document.getElementById('upload-placeholder').classList.remove('hidden');
        document.getElementById('upload-preview').classList.add('hidden');

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeCompleteOrderModal() {
        const modal = document.getElementById('completeOrderModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function handleFileSelect(input) {
        const placeholder = document.getElementById('upload-placeholder');
        const preview = document.getElementById('upload-preview');
        const fileName = document.getElementById('selected-file-name');

        if (input.files && input.files[0]) {
            fileName.textContent = input.files[0].name;
            placeholder.classList.add('hidden');
            preview.classList.remove('hidden');
        } else {
            placeholder.classList.remove('hidden');
            preview.classList.add('hidden');
        }
    }

    function viewReportDetails(reportText, reportFileUrl) {
        document.getElementById('viewReportText').textContent = reportText;
        const fileContainer = document.getElementById('viewReportFileContainer');
        const fileLink = document.getElementById('viewReportFileLink');

        if (reportFileUrl) {
            fileLink.href = reportFileUrl;
            fileContainer.classList.remove('hidden');
        } else {
            fileContainer.classList.add('hidden');
        }

        const modal = document.getElementById('viewReportModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeViewReportModal() {
        const modal = document.getElementById('viewReportModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
</script>
@endsection
