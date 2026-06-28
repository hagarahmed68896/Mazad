@extends('layouts.main')

@section('content')
<!-- Leaflet Assets -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="bg-gray-50 min-h-screen pt-24 pb-20">
    <div class="container px-4 md:px-16 mx-auto">
        <!-- Top Row: Title & Breadcrumbs -->
        <div class="flex flex-col items-start mb-8 gap-3">
            <div class="bg-primary text-white px-4 py-1.5 rounded-full text-xs font-bold flex items-center gap-1.5">
                <a href="{{ route('home') }}" class="hover:underline text-white">الرئيسية</a>
                <span class="text-white opacity-60">&rsaquo;</span>
                <span class="text-white" id="cart-breadcrumb-current">السلة</span>
            </div>
            <div>
                <h1 class="text-4xl font-black text-gray-900" id="cart-page-title">السلة</h1>
            </div>
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
        
        <!-- Steps Progress Bar -->
        <div class="flex justify-center items-center mb-12 max-w-xl mx-auto w-full px-4">
            <div class="flex items-center w-full relative justify-between">
                <!-- Step 1 -->
                <div class="flex flex-col items-center z-10" id="step-1-indicator">
                    <div class="step-circle w-10 h-10 rounded-full bg-white border-2 border-primary text-primary flex items-center justify-center font-bold text-sm shadow-sm transition-all duration-300">
                        1
                    </div>
                    <span class="step-text text-xs font-black text-primary mt-2 transition-all duration-300">السلة</span>
                </div>
                
                <!-- Line 1 -->
                <div class="flex-1 h-0.5 bg-gray-200 mx-4 transition-all duration-300" id="step-line-1"></div>
                
                <!-- Step 2 -->
                <div class="flex flex-col items-center z-10" id="step-2-indicator">
                    <div class="step-circle w-10 h-10 rounded-full bg-white border-2 border-gray-200 text-gray-400 flex items-center justify-center font-bold text-sm shadow-sm transition-all duration-300">
                        2
                    </div>
                    <span class="step-text text-xs font-bold text-gray-400 mt-2 transition-all duration-300">البيانات</span>
                </div>
                
                <!-- Line 2 -->
                <div class="flex-1 h-0.5 bg-gray-200 mx-4 transition-all duration-300" id="step-line-2"></div>
                
                <!-- Step 3 -->
                <div class="flex flex-col items-center z-10" id="step-3-indicator">
                    <div class="step-circle w-10 h-10 rounded-full bg-white border-2 border-gray-200 text-gray-400 flex items-center justify-center font-bold text-sm shadow-sm transition-all duration-300">
                        3
                    </div>
                    <span class="step-text text-xs font-bold text-gray-400 mt-2 transition-all duration-300">الدفع</span>
                </div>
            </div>
        </div>

        @php
            $groupedItems = $cartItems->groupBy(function($item) {
                return $item->ad->user_id;
            });
            $shippingSum = $cartItems->sum(function($item) {
                return (float) ($item->ad->shipping_price ?? 0);
            });
            $subtotal = $cartItems->sum(fn($i) => $i->price * $i->quantity);
            $total = $subtotal + $shippingSum;
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10" id="checkout-grid-container">
            <!-- Items Column (Grouped by Seller) -->
            <div class="lg:col-span-2" id="checkout-main-content-col">
                <!-- Step 1 Content: Cart Items -->
                <div id="checkout-step-1" class="space-y-6">
                    @foreach($groupedItems as $userId => $items)
                    @php
                        $seller = $items->first()->ad->user;
                        $typeLabel = 'فرد';
                        if ($seller && $seller->account_type === 'company') {
                            $typeLabel = 'شركة';
                        } elseif ($seller && $seller->account_type === 'partner') {
                            $typeLabel = 'مندوب';
                        } elseif ($seller && $seller->account_type === 'associate') {
                            $typeLabel = 'شريك';
                        }
                    @endphp

                    <div class="bg-white rounded-[32px] p-6 shadow-sm border border-gray-100">
                        <!-- Seller Header -->
                        <div class="flex items-center gap-3 pb-4 border-b border-gray-50 mb-6">
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold text-lg">
                                {{ mb_substr($seller->name ?? 'A', 0, 1) }}
                            </div>
                            <div>
                                <h4 class="font-black text-gray-900 text-sm">{{ $seller->name ?? 'المعلن' }}</h4>
                                <span class="text-xs text-gray-400 font-bold">{{ $typeLabel }}</span>
                            </div>
                        </div>

                        <!-- Seller's Items -->
                        <div class="space-y-6 divide-y divide-gray-50">
                            @foreach($items as $index => $item)
                                <div class="flex flex-col md:flex-row gap-6 {{ $index > 0 ? 'pt-6' : '' }} group">
                                    <!-- Image -->
                                    <div class="w-32 h-32 rounded-2xl overflow-hidden flex-shrink-0 bg-gray-50 border border-gray-100 shadow-sm relative">
                                        <img src="{{ Str::startsWith($item->ad->image, 'http') ? $item->ad->image : '/' . ltrim($item->ad->image, '/') }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    </div>
                                    
                                    <!-- Info & Actions -->
                                    <div class="flex-1 flex flex-col justify-between">
                                        <div>
                                            <div class="flex justify-between items-start mb-2">
                                                <h3 class="text-lg font-black text-gray-900">{{ $item->ad->title }}</h3>
                                                <div class="text-primary flex items-center gap-1 font-black">
                                                    <span class="text-xl">{{ number_format($item->price) }}</span>
                                                    <x-riyal-icon class="w-5 h-5 text-primary" />
                                                </div>
                                            </div>
                                            <p class="text-gray-400 text-sm line-clamp-2 mb-4 leading-relaxed">{{ $item->ad->description ?? 'لا يوجد وصف للإعلان' }}</p>
                                        </div>
                                        
                                        <div class="flex flex-wrap items-center justify-between gap-4">
                                            <!-- Quantity Selector (only for direct purchase ads with stock > 1) -->
                                            @if($item->ad->type === 'direct_purchase' && $item->ad->quantity > 1)
                                                <div class="flex items-center gap-3 bg-gray-50 rounded-full border border-gray-200 px-3 py-1">
                                                    <button onclick="updateCartQty({{ $item->id }}, -1)" class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-rose-500 rounded-full transition-all">
                                                        <i class="fa-solid fa-minus text-xs"></i>
                                                    </button>
                                                    <span class="font-black text-sm min-w-[20px] text-center" id="qty-{{ $item->id }}">{{ $item->quantity }}</span>
                                                    <button onclick="updateCartQty({{ $item->id }}, 1)" class="w-6 h-6 flex items-center justify-center text-primary hover:text-blue-700 rounded-full transition-all">
                                                        <i class="fa-solid fa-plus text-xs"></i>
                                                    </button>
                                                </div>
                                            @else
                                                <div></div>
                                            @endif
                                            
                                            <!-- Action Links -->
                                            <div class="flex items-center gap-3 text-xs font-bold text-gray-500">
                                                <form id="buy-form-{{ $item->ad_id }}" action="{{ route('orders.store', $item->ad_id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="quantity" value="{{ $item->quantity }}">
                                                    <button type="submit" class="text-primary hover:underline bg-transparent border-none cursor-pointer p-0 font-bold">
                                                        اشتر الآن
                                                    </button>
                                                </form>
                                                <span class="text-gray-200">|</span>
                                                <button onclick="moveToWishlist({{ $item->ad_id }}, {{ $item->id }})" class="hover:text-gray-800 hover:underline bg-transparent border-none cursor-pointer p-0 font-bold">
                                                    اطلع لاحقًا
                                                </button>
                                                <span class="text-gray-200">|</span>
                                                <form action="{{ route('cart.remove', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-500 hover:text-rose-700 hover:underline bg-transparent border-none cursor-pointer p-0 font-bold">
                                                        حذف
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                </div> <!-- End Step 1 -->

                <!-- Step 2 Content: Details Form -->
                <div id="checkout-step-2" class="hidden space-y-6 animate-in fade-in duration-300">
                    <div class="bg-white rounded-[32px] p-8 shadow-sm border border-gray-100">
                        <h3 class="text-2xl font-black text-gray-900 mb-6">بيانات التوصيل والشحن</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">الاسم الكامل</label>
                                <input type="text" id="buyer_name" value="{{ Auth::user()->name }}" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold transition-all text-sm">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">رقم الجوال</label>
                                <input type="text" id="buyer_phone" value="{{ Auth::user()->phone ?? '' }}" placeholder="5XXXXXXXX" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold transition-all text-sm" maxlength="9" inputmode="numeric" pattern="5[0-9]{8}" oninput="let val = this.value.replace(/[^0-9]/g, ''); if(val.startsWith('0')) { val = val.substring(1); } this.value = val.slice(0,9);">
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-gray-700 font-bold mb-3 flex items-center gap-1.5">
                                <i class="fa-solid fa-map-location-dot text-primary"></i> حدد موقع التوصيل على الخريطة
                            </label>
                            <div id="checkoutMap" style="height: 320px; width: 100%; border-radius: 24px; border: 2px solid #F1F5F9; z-index: 10;" class="mb-4"></div>
                            
                            <!-- Hidden inputs for coordinates -->
                            <input type="hidden" id="buyer_latitude" value="{{ Auth::user()->latitude ?? '24.7136' }}">
                            <input type="hidden" id="buyer_longitude" value="{{ Auth::user()->longitude ?? '46.6753' }}">
                            
                            <!-- Address display field (geocoded) -->
                            <div>
                                <label class="block text-gray-700 font-bold mb-2">الموقع المختار</label>
                                <input type="text" id="buyer_address" value="{{ Auth::user()->address ?? '' }}" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold transition-all text-sm" placeholder="سيتم تحديد العنوان تلقائياً عند تحديد الموقع على الخريطة">
                            </div>
                        </div>

                        <div class="mb-8">
                            <label class="block text-gray-700 font-bold mb-2">ملاحظات  </label>
                            <textarea id="order_notes" rows="2" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold transition-all text-sm" placeholder="أي تعليمات خاصة بالطلب أو الشحن..."></textarea>
                        </div>

                        <div class="flex gap-4">
                            <button onclick="goToStep(3)" class="flex-1 bg-primary text-white font-black py-4 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-sm">متابعة للدفع</button>
                            <button onclick="goToStep(1)" class="flex-1 bg-gray-100 text-gray-500 font-bold py-4 rounded-2xl hover:bg-gray-200 transition-all text-sm">العودة للسلة</button>
                        </div>
                    </div>
                </div>

                <!-- Step 3 Content: Payment Selection -->
                <div id="checkout-step-3" class="hidden space-y-6 animate-in fade-in duration-300">
                    <div class="bg-white rounded-[32px] p-8 shadow-sm border border-gray-100 max-w-xl mx-auto">
                        <!-- Summary of address entered in step 2 (subtle review info) -->
                        <div class="bg-blue-50/50 rounded-2xl p-5 mb-6 text-right border border-blue-100/50">
                            <h4 class="font-black text-primary mb-2 text-sm">بيانات الشحن والتوصيل المختارة:</h4>
                            <p class="text-xs text-gray-600 font-bold mb-1" id="review-shipping-name"></p>
                            <p class="text-xs text-gray-600 font-bold mb-1" id="review-shipping-phone"></p>
                            <p class="text-xs text-gray-600 font-bold" id="review-shipping-address"></p>
                        </div>

                        <!-- Card Brand Buttons (mada and Mastercard) -->
                        <div class="flex gap-4 mb-6">
                            <!-- Mastercard option -->
                            <div class="payment-method-card border-2 border-primary bg-blue-50/10 rounded-2xl p-4 cursor-pointer flex-1 flex justify-center items-center h-16 transition-all duration-300" onclick="selectPaymentMethod('card')" id="card-option-master">
                                <img src="{{ asset('images/image 10.png') }}" class="h-8 object-contain" alt="Mastercard">
                            </div>
                            
                            <!-- mada option -->
                            <div class="payment-method-card border-2 border-gray-100 rounded-2xl p-4 cursor-pointer flex-1 flex justify-center items-center h-16 transition-all duration-300" onclick="selectPaymentMethod('mada')" id="card-option-mada">
                                <img src="{{ asset('images/images 2.svg') }}" class="h-6 object-contain" alt="mada">
                            </div>
                        </div>

                        <!-- Payment Fields -->
                        <div class="space-y-5">
                            <div>
                                <label class="block text-gray-700 font-bold mb-2 text-right">اسم البطاقة</label>
                                <input type="text" id="card_name" placeholder="أدخل اسم البطاقة" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right">
                            </div>
                            <div>
                                <label class="block text-gray-700 font-bold mb-2 text-right">رقم البطاقة</label>
                                <input type="text" id="card_number" placeholder="أدخل رقم البطاقة" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" maxlength="19">
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right">CVV</label>
                                    <input type="text" id="card_cvv" placeholder="CVV" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-center" maxlength="4">
                                </div>
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right">تاريخ الانتهاء</label>
                                    <input type="text" id="card_expiry" placeholder="تاريخ الانتهاء (شهر/سنة)" class="w-full bg-gray-50 border border-gray-100 rounded-2xl py-4 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-center">
                                </div>
                            </div>
                        </div>

                        <!-- Summary block inside step 3 card -->
                        <div class="mt-8 pt-6 border-t border-gray-100 space-y-4 text-right">
                            <h4 class="font-black text-gray-900 mb-4 text-base">ملخص الطلب</h4>
                            
                            <div class="flex justify-between items-center text-gray-500 font-bold">
                                <span>عدد المنتجات ({{ $cartItems->sum('quantity') }})</span>
                                <div class="flex items-center gap-1 text-gray-900 font-black">
                                    <span>{{ number_format($subtotal) }}</span>
                                    <x-riyal-icon class="w-4 h-4 text-gray-950" />
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center text-gray-500 font-bold">
                                <span>الشحن</span>
                                <div class="flex items-center gap-1 text-gray-900 font-black">
                                    <span>{{ number_format($shippingSum) }}</span>
                                    <x-riyal-icon class="w-4 h-4 text-gray-950" />
                                </div>
                            </div>
                            
                            <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                                <span class="text-gray-900 font-black text-lg">الإجمالي</span>
                                <div class="flex items-center gap-1 text-primary">
                                    <span class="font-black text-2xl" id="review-total-price">{{ number_format($total) }}</span>
                                    <x-riyal-icon class="w-5 h-5 text-primary" />
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col gap-3 mt-8">
                            <!-- Complete Payment Button -->
                            <button onclick="submitOrderCheckout()" class="w-full bg-primary text-white font-black py-4 rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-200 text-base flex items-center justify-center">
                                إتـمـام الـدفـع
                            </button>
                            <!-- Back Button -->
                            <button onclick="goToStep(2)" class="w-full bg-gray-100 text-gray-500 font-bold py-4 rounded-2xl hover:bg-gray-200 transition-all text-sm">العودة لتعديل البيانات</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Column -->
            <div class="lg:col-span-1" id="checkout-sidebar-col">
                <div class="bg-white rounded-[32px] p-6 shadow-sm border border-gray-100 sticky top-32">
                    <h2 class="text-xl font-black text-gray-900 mb-6">ملخص الطلب</h2>
                    
                    <div class="space-y-4 mb-6">
                        <!-- Items Count & Subtotal -->
                        <div class="flex justify-between items-center text-gray-500 font-bold">
                            <span>عدد المنتجات ({{ $cartItems->sum('quantity') }})</span>
                            <div class="flex items-center gap-1 text-gray-900 font-black">
                                <span>{{ number_format($subtotal) }}</span>
                                <x-riyal-icon class="w-4 h-4 text-gray-950" />
                            </div>
                        </div>
                        
                        <!-- Shipping -->
                        <div class="flex justify-between items-center text-gray-500 font-bold">
                            <span>الشحن</span>
                            <div class="flex items-center gap-1 text-gray-900 font-black">
                                <span>{{ number_format($shippingSum) }}</span>
                                <x-riyal-icon class="w-4 h-4 text-gray-950" />
                            </div>
                        </div>
                        
                        <!-- Total -->
                        <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                            <span class="text-gray-900 font-black text-lg">الإجمالي</span>
                            <div class="flex items-center gap-1 text-primary">
                                <span class="font-black text-2xl">{{ number_format($total) }}</span>
                                <x-riyal-icon class="w-5 h-5 text-primary" />
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3" id="sidebar-actions">
                        <a href="{{ route('home') }}" class="flex-1 border-2 border-primary text-primary hover:bg-primary/5 font-black py-3 rounded-xl transition-all text-center text-sm flex items-center justify-center">
                            متابعة التسوق
                        </a>
                        <button onclick="checkoutCart()" class="flex-1 bg-primary text-white font-black py-3 rounded-xl hover:bg-blue-700 transition-all text-center text-sm shadow-md shadow-blue-100 flex items-center justify-center">
                            الدفع
                        </button>
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
        if (!qtyEl) return;
        let currentQty = parseInt(qtyEl.innerText);
        const newQty = Math.max(1, currentQty + val);
        
        if (newQty === currentQty) return;

        qtyEl.innerText = newQty;

        fetch(`/cart/update/${itemId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quantity: newQty })
        })
        .then(async response => {
            if (response.status === 419) {
                Swal.fire('الجلسة انتهت', 'يرجى تحديث الصفحة والمحاولة مرة أخرى.', 'warning').then(() => {
                    location.reload();
                });
                return null;
            }
            const data = await response.json();
            if (!response.ok) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: data.message || 'عذراً، حدث خطأ ما.',
                    confirmButtonColor: '#1A73E8',
                    confirmButtonText: 'حسناً'
                }).then(() => {
                    location.reload();
                });
                return null;
            }
            return data;
        })
        .then(data => {
            if (data && data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error updating cart:', error);
        });
    }

    function moveToWishlist(adId, itemId) {
        Swal.fire({
            title: 'جاري الحفظ للاطلاع لاحقًا...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch(`/wishlist/toggle/${adId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.status === 419) {
                Swal.fire('الجلسة انتهت', 'يرجى تحديث الصفحة والمحاولة مرة أخرى.', 'warning').then(() => location.reload());
                return null;
            }
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data) return;
            if (data.status === 'success') {
                // Now remove from cart
                const form = document.createElement('form');
                form.action = `/cart/remove/${itemId}`;
                form.method = 'POST';
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            } else {
                Swal.fire('خطأ', 'حدث خطأ أثناء نقل المنتج للمفضلة.', 'error');
            }
        })
        .catch(() => {
            Swal.fire('خطأ', 'حدث خطأ أثناء الاتصال بالخادم.', 'error');
        });
    }

    function checkoutCart() {
        goToStep(2);
    }

    let checkoutMapInstance, checkoutMarkerInstance;
    function initCheckoutMap() {
        if (checkoutMapInstance) return;
        
        let initialLat = parseFloat(document.getElementById('buyer_latitude').value) || 24.7136;
        let initialLng = parseFloat(document.getElementById('buyer_longitude').value) || 46.6753;
        
        checkoutMapInstance = L.map('checkoutMap').setView([initialLat, initialLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(checkoutMapInstance);

        checkoutMarkerInstance = L.marker([initialLat, initialLng], { draggable: true }).addTo(checkoutMapInstance);
        
        // Reverse Geocoding Function
        async function updateCheckoutAddress(lat, lng) {
            const display = document.getElementById('buyer_address');
            display.placeholder = 'جاري جلب العنوان...';
            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ar`);
                const data = await response.json();
                if (data && data.display_name) {
                    display.value = data.display_name;
                }
            } catch (error) {
                console.error('Error fetching address:', error);
                display.placeholder = 'سيتم تحديد العنوان تلقائياً عند تحديد الموقع على الخريطة';
            }
        }

        // Trigger first reverse geocoding if address is empty
        if (!document.getElementById('buyer_address').value) {
            updateCheckoutAddress(initialLat, initialLng);
        }

        checkoutMarkerInstance.on('dragend', function(e) {
            const position = checkoutMarkerInstance.getLatLng();
            document.getElementById('buyer_latitude').value = position.lat;
            document.getElementById('buyer_longitude').value = position.lng;
            updateCheckoutAddress(position.lat, position.lng);
        });

        checkoutMapInstance.on('click', function(e) {
            checkoutMarkerInstance.setLatLng(e.latlng);
            document.getElementById('buyer_latitude').value = e.latlng.lat;
            document.getElementById('buyer_longitude').value = e.latlng.lng;
            updateCheckoutAddress(e.latlng.lat, e.latlng.lng);
        });
    }

    function goToStep(step) {
        const gridContainer = document.getElementById('checkout-grid-container');
        const mainCol = document.getElementById('checkout-main-content-col');
        const sidebarCol = document.getElementById('checkout-sidebar-col');
        const pageTitle = document.getElementById('cart-page-title');
        const breadcrumbCurrent = document.getElementById('cart-breadcrumb-current');

        // Validation when trying to go to step 3 from step 2
        if (step === 3) {
            const name = document.getElementById('buyer_name').value.trim();
            const phone = document.getElementById('buyer_phone').value.trim();
            const address = document.getElementById('buyer_address').value.trim();
            
            if (!name || !phone || !address) {
                Swal.fire({
                    icon: 'warning',
                    title: 'تنبيه',
                    text: 'يرجى ملء جميع الحقول وتحديد موقع التوصيل على الخريطة للمتابعة.'
                });
                return;
            }
            
            // Populate review info in step 3
            document.getElementById('review-shipping-name').innerText = `الاسم: ${name}`;
            document.getElementById('review-shipping-phone').innerText = `الجوال: ${phone}`;
            document.getElementById('review-shipping-address').innerText = `العنوان المختار: ${address}`;
        }

        // Layout adjustments per step
        if (step === 1) {
            // Step 1: Two Columns Layout
            if (gridContainer) {
                gridContainer.className = "grid grid-cols-1 lg:grid-cols-3 gap-10";
            }
            if (mainCol) {
                mainCol.className = "lg:col-span-2";
            }
            if (sidebarCol) {
                sidebarCol.classList.remove('hidden');
            }
            if (pageTitle) pageTitle.innerText = "السلة";
            if (breadcrumbCurrent) breadcrumbCurrent.innerText = "السلة";
        } else {
            // Step 2 & 3: Single Column Layout (Centered)
            if (gridContainer) {
                gridContainer.className = "block";
            }
            if (mainCol) {
                mainCol.className = "max-w-2xl mx-auto w-full";
            }
            if (sidebarCol) {
                sidebarCol.classList.add('hidden');
            }
            
            if (step === 2) {
                if (pageTitle) pageTitle.innerText = "السلة";
                if (breadcrumbCurrent) breadcrumbCurrent.innerText = "السلة";
            } else if (step === 3) {
                if (pageTitle) pageTitle.innerText = "تفاصيل الدفع";
                if (breadcrumbCurrent) breadcrumbCurrent.innerText = "السلة";
            }
        }

        // Hide all steps
        document.getElementById('checkout-step-1').classList.add('hidden');
        document.getElementById('checkout-step-2').classList.add('hidden');
        document.getElementById('checkout-step-3').classList.add('hidden');
        
        // Show current step
        document.getElementById('checkout-step-' + step).classList.remove('hidden');
        
        // Initialize map if step 2 is active
        if (step === 2) {
            setTimeout(() => {
                initCheckoutMap();
                if (checkoutMapInstance) {
                    checkoutMapInstance.invalidateSize();
                }
            }, 200);
        }

        // Update progress bar
        updateProgressBar(step);
        
        // Show/hide sidebar actions
        const sidebarActions = document.getElementById('sidebar-actions');
        if (sidebarActions) {
            if (step === 1) {
                sidebarActions.classList.remove('hidden');
                sidebarActions.classList.add('flex');
            } else {
                sidebarActions.classList.remove('flex');
                sidebarActions.classList.add('hidden');
            }
        }

        // Scroll to top of checkout container
        const indicator = document.getElementById('step-1-indicator');
        if (indicator) {
            window.scrollTo({
                top: indicator.offsetTop - 100,
                behavior: 'smooth'
            });
        }
    }

    function updateProgressBar(step) {
        // Reset all steps to inactive
        for (let i = 1; i <= 3; i++) {
            const ind = document.getElementById(`step-${i}-indicator`);
            if (ind) {
                const circle = ind.querySelector('.step-circle');
                const text = ind.querySelector('.step-text');
                
                circle.className = "step-circle w-10 h-10 rounded-full bg-white border-2 border-gray-200 text-gray-400 flex items-center justify-center font-bold text-sm shadow-sm transition-all duration-300";
                circle.innerText = i;
                text.className = "step-text text-xs font-bold text-gray-400 mt-2 transition-all duration-300";
            }
            
            if (i < 3) {
                const line = document.getElementById(`step-line-${i}`);
                if (line) line.className = "flex-1 h-0.5 bg-gray-200 mx-4 transition-all duration-300";
            }
        }
        
        // Mark completed steps
        for (let i = 1; i < step; i++) {
            const ind = document.getElementById(`step-${i}-indicator`);
            if (ind) {
                const circle = ind.querySelector('.step-circle');
                const text = ind.querySelector('.step-text');
                
                circle.className = "step-circle w-10 h-10 rounded-full bg-primary text-white border-2 border-primary flex items-center justify-center font-bold text-sm shadow-sm transition-all duration-300";
                circle.innerHTML = '<i class="fa-solid fa-check text-xs"></i>';
                text.className = "step-text text-xs font-black text-primary mt-2 transition-all duration-300";
            }
            
            const line = document.getElementById(`step-line-${i}`);
            if (line) line.className = "flex-1 h-0.5 bg-primary mx-4 transition-all duration-300";
        }
        
        // Mark current step as active
        const ind = document.getElementById(`step-${step}-indicator`);
        if (ind) {
            const circle = ind.querySelector('.step-circle');
            const text = ind.querySelector('.step-text');
            
            circle.className = "step-circle w-10 h-10 rounded-full bg-white border-2 border-primary text-primary flex items-center justify-center font-bold text-sm shadow-sm transition-all duration-300";
            circle.innerText = step;
            text.className = "step-text text-xs font-black text-primary mt-2 transition-all duration-300";
        }
    }

    let selectedPayment = 'card';
    function selectPaymentMethod(method) {
        selectedPayment = method;
        
        const cardMaster = document.getElementById('card-option-master');
        const cardMada = document.getElementById('card-option-mada');
        
        if (method === 'card') {
            cardMaster.className = "payment-method-card border-2 border-primary bg-blue-50/10 rounded-2xl p-4 cursor-pointer flex-1 flex justify-center items-center h-16 transition-all duration-300";
            cardMada.className = "payment-method-card border-2 border-gray-100 rounded-2xl p-4 cursor-pointer flex-1 flex justify-center items-center h-16 transition-all duration-300";
        } else {
            cardMada.className = "payment-method-card border-2 border-primary bg-blue-50/10 rounded-2xl p-4 cursor-pointer flex-1 flex justify-center items-center h-16 transition-all duration-300";
            cardMaster.className = "payment-method-card border-2 border-gray-100 rounded-2xl p-4 cursor-pointer flex-1 flex justify-center items-center h-16 transition-all duration-300";
        }
    }

    function submitOrderCheckout() {
        const name = document.getElementById('buyer_name').value.trim();
        const phone = document.getElementById('buyer_phone').value.trim();
        const address = document.getElementById('buyer_address').value.trim();
        const lat = document.getElementById('buyer_latitude').value;
        const lng = document.getElementById('buyer_longitude').value;
        const order_notes = document.getElementById('order_notes').value.trim();
        const payment_method = selectedPayment;

        if (!name || !phone || !address) {
            Swal.fire({
                icon: 'error',
                title: 'عذراً',
                text: 'يرجى ملء جميع الحقول وتحديد الموقع على الخريطة في الخطوة السابقة.'
            });
            goToStep(2);
            return;
        }

        // Validate Card Details
        const cardName = document.getElementById('card_name').value.trim();
        const cardNumber = document.getElementById('card_number').value.trim();
        const cardCvv = document.getElementById('card_cvv').value.trim();
        const cardExpiry = document.getElementById('card_expiry').value.trim();

        if (!cardName || !cardNumber || !cardCvv || !cardExpiry) {
            Swal.fire({
                icon: 'warning',
                title: 'تنبيه',
                text: 'يرجى ملء جميع حقول البطاقة البنكية لإتمام عملية الدفع.'
            });
            return;
        }

        if (cardNumber.replace(/\s/g, '').length < 15) {
            Swal.fire({
                icon: 'warning',
                title: 'رقم البطاقة غير صحيح',
                text: 'يرجى إدخال رقم بطاقة بنكية صالح.'
            });
            return;
        }

        if (cardCvv.length < 3) {
            Swal.fire({
                icon: 'warning',
                title: 'رمز التحقق (CVV) غير صحيح',
                text: 'يرجى إدخال رمز التحقق المكون من 3 أو 4 أرقام.'
            });
            return;
        }

        Swal.fire({
            title: 'جاري إرسال الطلب ومعالجة عملية الدفع...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("{{ route('cart.checkout') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                buyer_name: name,
                buyer_phone: phone,
                buyer_address: address,
                buyer_latitude: lat,
                buyer_longitude: lng,
                payment_method: payment_method,
                order_notes: order_notes
            })
        })
        .then(response => {
            if (response.status === 419) {
                Swal.fire('الجلسة انتهت', 'يرجى تحديث الصفحة وإعادة المحاولة.', 'warning').then(() => location.reload());
                return null;
            }
            if (!response.ok) {
                throw new Error('Network error');
            }
            return response.json();
        })
        .then(data => {
            if (!data) return;
            if (data.success) {
                Swal.fire({
                    title: 'تم الدفع بنجاح',
                    text: 'شكراً لك. تم تأكيد طلبك وسنبدأ في تحضيره وشحنه إليك قريباً.',
                    icon: 'success',
                    confirmButtonText: 'الذهاب الي مشترياتي',
                    confirmButtonColor: '#1A73E8',
                    customClass: {
                        popup: 'rounded-[32px] p-8',
                        confirmButton: 'rounded-2xl px-8 py-4 font-black text-sm bg-primary border-0'
                    }
                }).then(() => {
                    window.location.href = "{{ route('purchases.my') }}";
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'فشل الطلب',
                    text: data.message || 'حدث خطأ ما أثناء إتمام الطلب.'
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'خطأ',
                text: 'حدث خطأ في الاتصال بالخادم، يرجى المحاولة لاحقاً.'
            });
        });
    }

    // Auto-format card fields helper
    document.addEventListener('DOMContentLoaded', function() {
        const cardNumInput = document.getElementById('card_number');
        if (cardNumInput) {
            cardNumInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                let formatted = '';
                for (let i = 0; i < value.length; i++) {
                    if (i > 0 && i % 4 === 0) {
                        formatted += ' ';
                    }
                    formatted += value[i];
                }
                e.target.value = formatted.substring(0, 19);
            });
        }

        const cardExpiryInput = document.getElementById('card_expiry');
        if (cardExpiryInput) {
            cardExpiryInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                if (value.length > 2) {
                    e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
                } else {
                    e.target.value = value;
                }
            });
        }
    });
</script>
@endsection
