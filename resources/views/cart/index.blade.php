@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen pt-24 pb-20">
    <div class="container px-4 md:px-16 mx-auto">
        <div class="flex items-center gap-4 mb-10">
            <h1 class="text-4xl font-black text-gray-900">سلة التسوق</h1>
            <span class="bg-primary text-white px-4 py-1 rounded-full text-sm font-black">{{ $cartItems->count() }} منتجات</span>
        </div>

        @if($cartItems->isEmpty())
        <div class="bg-white rounded-[40px] p-20 text-center shadow-sm border border-gray-100">
            <div class="w-32 h-32 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-8">
                <i class="fa-solid fa-cart-shopping text-5xl text-gray-200"></i>
            </div>
            <h2 class="text-2xl font-black text-gray-900 mb-4">سلة التسوق فارغة</h2>
            <p class="text-gray-500 font-medium mb-10 max-w-md mx-auto">يبدو أنك لم تقم بإضافة أي منتجات إلى سلتك بعد. ابدأ بالتسوق الآن واكتشف أفضل العروض.</p>
            <a href="{{ route('home') }}" class="bg-primary text-white px-12 py-5 rounded-[24px] font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all inline-block">ابدأ التسوق الآن</a>
        </div>
        @else
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
            <!-- Items List -->
            <div class="lg:col-span-2 space-y-6">
                @foreach($cartItems as $item)
                <div class="bg-white rounded-[32px] p-6 flex flex-col md:flex-row items-center gap-8 shadow-sm border border-gray-100 group hover:border-primary/20 transition-all">
                    <div class="w-32 h-32 rounded-[24px] overflow-hidden flex-shrink-0 shadow-md">
                        <img src="{{ Str::startsWith($item->ad->image, 'http') ? $item->ad->image : '/' . ltrim($item->ad->image, '/') }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <div class="flex-1 text-center md:text-right">
                        <div class="flex flex-col md:flex-row justify-between items-start gap-4 mb-4">
                            <div>
                                <span class="text-xs font-black text-primary bg-primary/5 px-3 py-1 rounded-lg mb-2 inline-block">{{ $item->ad->category->name }}</span>
                                <h3 class="text-xl font-black text-gray-900">{{ $item->ad->title }}</h3>
                                <p class="text-gray-400 text-sm font-bold mt-1">بواسطة: {{ $item->ad->user->name }}</p>
                            </div>
                            <div class="text-gray-900 flex items-center gap-1">
                                <span class="font-black text-2xl">{{ number_format($item->price) }}</span>
                                <x-riyal-icon class="w-5 h-5" />
                            </div>
                        </div>
                        
                        <div class="flex flex-col md:flex-row items-center justify-between gap-6 pt-6 border-t border-gray-50">
                            <div class="flex items-center gap-4 bg-gray-50 rounded-2xl p-1 px-4 border border-gray-100">
                                <button onclick="updateCartQty({{ $item->id }}, 1)" class="w-8 h-8 flex items-center justify-center text-primary hover:bg-white rounded-lg transition-all"><i class="fa-solid fa-plus"></i></button>
                                <span class="font-black text-lg min-w-[30px]" id="qty-{{ $item->id }}">{{ $item->quantity }}</span>
                                <button onclick="updateCartQty({{ $item->id }}, -1)" class="w-8 h-8 flex items-center justify-center text-gray-400 hover:bg-white rounded-lg transition-all"><i class="fa-solid fa-minus"></i></button>
                            </div>
                            
                            <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 font-black text-sm hover:text-rose-700 transition-colors flex items-center gap-2">
                                    <i class="fa-solid fa-trash-can"></i> حذف من السلة
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-[40px] p-8 shadow-xl shadow-gray-200/50 border border-gray-100 sticky top-32">
                    <h2 class="text-2xl font-black text-gray-900 mb-8">ملخص الطلب</h2>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center text-gray-500 font-bold">
                            <span>إجمالي المنتجات</span>
                            <div class="flex items-center gap-1 text-gray-900">
                                <span>{{ number_format($cartItems->sum(fn($i) => $i->price * $i->quantity)) }}</span>
                                <x-riyal-icon class="w-4 h-4" />
                            </div>
                        </div>
                        <div class="flex justify-between items-center text-gray-500 font-bold">
                            <span>الشحن</span>
                            <span class="text-amber-600">يحدده المعلن</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-500 font-bold pt-4 border-t border-gray-50">
                            <span class="text-gray-900 font-black text-xl">الإجمالي</span>
                            <div class="flex items-center gap-1 text-primary">
                                <span class="font-black text-3xl">{{ number_format($cartItems->sum(fn($i) => $i->price * $i->quantity)) }}</span>
                                <x-riyal-icon class="w-6 h-6" />
                            </div>
                        </div>
                    </div>

                    <button class="w-full bg-primary text-white font-black py-5 rounded-[24px] hover:bg-blue-700 transition-all shadow-xl shadow-blue-100 text-lg flex items-center justify-center gap-3">
                        إتمام عملية الشراء <i class="fa-solid fa-arrow-left"></i>
                    </button>
                    
                    <div class="mt-8 pt-8 border-t border-gray-50">
                        <div class="flex items-center gap-4 text-gray-400">
                            <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center flex-shrink-0 text-xl">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <p class="text-xs font-bold leading-relaxed">تسوق آمن 100%. بياناتك مشفرة ومحمية دائماً مع نظام مزاد المعتمد.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function updateCartQty(itemId, val) {
        const qtyEl = document.getElementById('qty-' + itemId);
        let currentQty = parseInt(qtyEl.innerText);
        const newQty = Math.max(1, currentQty + val);
        
        if (newQty === currentQty) return;

        fetch(`/cart/update/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ quantity: newQty })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload(); // Quick way to update totals
            }
        });
    }
</script>
@endsection
