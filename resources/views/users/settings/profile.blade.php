@extends('layouts.main')

@section('title', 'ملفي الشخصي | مزاد')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    /* Force SweetAlert to show above modal overlay */
    .swal2-container {
        z-index: 10000 !important;
    }

    /* Add custom active styles for sidebar links */
    .tab-link.active {
        background-color: #EBF3FC;
        color: #1A73E8;
        font-weight: 800;
        border-radius: 0.5rem;
    }
    .tab-content {
        display: none;
        animation: fadeIn 0.3s ease-in-out;
    }
    .tab-content.active {
        display: block;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .file-upload-box {
        border: 2px dashed #E2E8F0;
        transition: all 0.3s ease;
    }
    .file-upload-box:hover, .file-upload-box.dragover {
        border-color: #1A73E8;
        background-color: #F8FAFC;
    }

    #profileMapEl {
        z-index: 1;
    }

    .interest-item {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid #F1F5F9;
        border-radius: 20px;
        padding: 1.25rem 1rem;
        position: relative;
        background: #F8FAFC;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .interest-item:hover {
        transform: translateY(-2px);
        border-color: #CBD5E1;
    }
    .interest-item.selected {
        border-color: #1A73E8 !important;
        background-color: #F0F7FF !important;
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(26, 115, 232, 0.1);
    }
    .interest-item.selected .check-badge {
        opacity: 1 !important;
    }
    .interest-item.selected span {
        color: #1A73E8 !important;
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="container mx-auto px-4 lg:px-12">
        
        <!-- Breadcrumb & Header -->
        <div class="flex items-center gap-2 text-sm font-bold text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-primary transition-colors">الرئيسية</a>
            <i class="fa-solid fa-chevron-left text-xs"></i>
            <span class="text-white bg-primary px-3 py-1 rounded-full text-xs">ملفي الشخصي</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            
            <!-- Sidebar -->
            <div class="w-full lg:w-1/4">
                <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col items-center">
                    
                    <!-- Avatar Upload Area -->
                    <div class="relative w-36 h-36 mb-4">
                        <img src="{{ Auth::user()->avatar ?? asset('images/default-avatar.png') }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover rounded-full border-4 border-white shadow-md" id="sidebar-avatar-preview">
                        <button type="button" onclick="openAvatarModal()" class="absolute bottom-1 right-1 w-10 h-10 bg-primary text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg hover:bg-blue-700 transition-colors border-2 border-white">
                            <i class="fa-solid fa-pen text-sm"></i>
                        </button>
                    </div>
                    
                    <h3 class="text-xl font-black text-gray-900 mb-1">{{ Auth::user()->name }}</h3>
                    <p class="text-gray-500 text-sm font-bold mb-1">
                        @if(Auth::user()->account_type == 'company') 
                            <span class="flex items-center gap-1 justify-center"><i class="fa-solid fa-building text-primary/60 text-xs"></i> شركة</span>
                        @elseif(Auth::user()->account_type == 'representative') 
                            <span class="flex items-center gap-1 justify-center"><i class="fa-solid fa-id-badge text-primary/60 text-xs"></i> مندوب</span>
                        @elseif(Auth::user()->account_type == 'partner') 
                            <span class="flex items-center gap-1 justify-center"><i class="fa-solid fa-handshake text-primary/60 text-xs"></i> شريك</span>
                        @else 
                            <span class="flex items-center gap-1 justify-center"><i class="fa-solid fa-user text-primary/60 text-xs"></i> فرد</span>
                        @endif
                    </p>
                    @if(Auth::user()->parent_id)
                        @php $myCompany = Auth::user()->company()->first(); @endphp
                        @if($myCompany)
                        <div class="mb-5 mt-1 px-3 py-2 rounded-xl text-center" style="background:#EBF3FD;">
                            <p class="text-xs text-gray-400 font-bold mb-0.5">تابع لشركة</p>
                            <a href="{{ route('users.profile', $myCompany->id) }}" class="font-black hover:underline" style="color:#1A73E8; font-size:0.82rem;">
                                <i class="fa-solid fa-building text-[10px] ml-1"></i>
                                {{ $myCompany->company_name ?? $myCompany->name }}
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="mb-5"></div>
                    @endif

                    <div class="w-full space-y-2 text-right">
                        <button onclick="switchTab('profile')" id="tab-btn-profile" class="tab-link active w-full text-right px-4 py-3 text-gray-600 font-bold hover:bg-gray-50 hover:text-primary rounded-lg transition-all flex items-center justify-between">
                            <span>بيانات الحساب</span>
                        </button>
                        <button onclick="switchTab('password')" id="tab-btn-password" class="tab-link w-full text-right px-4 py-3 text-gray-600 font-bold hover:bg-gray-50 hover:text-primary rounded-lg transition-all flex items-center justify-between">
                            <span>كلمة المرور</span>
                        </button>
                        <button onclick="switchTab('bank')" id="tab-btn-bank" class="tab-link w-full text-right px-4 py-3 text-gray-600 font-bold hover:bg-gray-50 hover:text-primary rounded-lg transition-all flex items-center justify-between">
                            <span>حسابي البنكي</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="w-full lg:w-3/4">
                <div class="bg-white rounded-3xl p-8 shadow-sm border border-gray-100 min-h-[600px]">
                    
                    <!-- TAB 1: Profile -->
                    <div id="tab-profile" class="tab-content active">
                        <h2 class="text-2xl font-black text-gray-900 mb-6 text-right">بيانات الحساب</h2>
                        
                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">الاسم</label>
                                    <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}" placeholder="أدخل الاسم الذي سيظهر لمستخدمي البرنامج" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" required>
                                    @error('name') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">رقم الجوال</label>
                                    <div class="relative flex items-center">
                                        <div class="absolute left-0 top-0 bottom-0 px-4 bg-gray-50 border-r border-gray-200 flex items-center justify-center rounded-l-2xl font-bold text-gray-500" dir="ltr">
                                            +966
                                        </div>
                                        <input type="text" name="phone" id="phone-input" value="{{ old('phone', Auth::user()->phone) }}" placeholder="5XXXXXXXX" class="w-full bg-white border border-gray-200 rounded-2xl py-3 pl-20 pr-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm" dir="ltr" maxlength="9" inputmode="numeric" pattern="5[0-9]{8}" oninput="let val = this.value.replace(/[^0-9]/g, ''); if(val.startsWith('0')) { val = val.substring(1); } this.value = val.slice(0,9);" required>
                                    </div>
                                    @error('phone') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">البريد الإلكتروني</label>
                                    <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}" placeholder="أدخل البريد الإلكتروني" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" required>
                                    @error('email') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">الموقع</label>
                                    <div class="relative">
                                        <input type="text" name="address" id="address-input" value="{{ old('address', Auth::user()->address) }}" placeholder="حدد الموقع" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 pl-12 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right">
                                        <input type="hidden" name="latitude" id="lat-input" value="{{ Auth::user()->latitude }}">
                                        <input type="hidden" name="longitude" id="lng-input" value="{{ Auth::user()->longitude }}">
                                        <button type="button" onclick="openMapModal()" class="absolute left-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-primary hover:bg-blue-700 transition-colors rounded-xl flex items-center justify-center text-white">
                                            <i class="fa-solid fa-map-location-dot text-xs"></i>
                                        </button>
                                    </div>
                                    @error('address') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">نبذة تعريفية</label>
                                    <textarea name="bio" rows="3" placeholder="أدخل نبذة تعريفية عنك..." class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right resize-none">{{ old('bio', Auth::user()->bio) }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">صورة الغلاف</label>
                                    <input type="file" id="cover-upload" name="cover" class="hidden" accept="image/png,image/jpeg,image/jpg,image/webp" onchange="previewCover(this)">
                                    <div onclick="document.getElementById('cover-upload').click()" class="file-upload-box cursor-pointer rounded-[32px] p-10 min-h-[200px] flex flex-col items-center justify-center text-center relative overflow-hidden">
                                        <div id="cover-preview-wrap" class="{{ Auth::user()->cover ? '' : 'hidden' }} w-full h-full absolute inset-0">
                                            <img id="cover-preview-img" src="{{ Auth::user()->cover ? asset(Auth::user()->cover) : '' }}" class="w-full h-full object-cover">
                                            <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                                <span class="text-white font-bold text-sm">انقر لتغيير الصورة</span>
                                            </div>
                                        </div>
                                        <div id="cover-placeholder" class="{{ Auth::user()->cover ? 'hidden' : 'flex' }} flex-col items-center">
                                            <div class="w-16 h-16 bg-blue-50 text-primary rounded-[20px] flex items-center justify-center text-3xl mb-4">
                                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                            </div>
                                            <p class="text-gray-500 text-base font-bold mb-3">صورة الغلاف / اسحب الملف هنا</p>
                                            <button type="button" class="border-2 border-primary text-primary hover:bg-primary hover:text-white px-8 py-2.5 rounded-xl text-sm font-bold transition-colors pointer-events-none">
                                                تصفح الملفات
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex justify-start gap-3 pt-4">
                                    <button type="submit" class="bg-primary hover:bg-blue-700 text-white font-black py-3 px-10 rounded-xl transition-all text-sm shadow-md shadow-blue-100">
                                        حفظ
                                    </button>
                                    <a href="{{ route('home') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-black py-3 px-8 rounded-xl transition-all text-sm">
                                        إلغاء
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: Password -->
                    <div id="tab-password" class="tab-content">
                        <h2 class="text-2xl font-black text-gray-900 mb-6 text-right">كلمة المرور</h2>
                        
                        <form action="{{ route('profile.password.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">كلمة المرور الحالية</label>
                                    <div class="relative">
                                        <input type="password" name="current_password" placeholder="أدخل كلمة المرور الحالية" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" required>
                                        <button type="button" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePassword(this)">
                                            <i class="fa-regular fa-eye-slash"></i>
                                        </button>
                                    </div>
                                    @error('current_password') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">كلمة المرور الجديدة</label>
                                    <div class="relative">
                                        <input type="password" name="password" placeholder="أدخل كلمة المرور الجديدة" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" required minlength="8">
                                        <button type="button" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePassword(this)">
                                            <i class="fa-regular fa-eye-slash"></i>
                                        </button>
                                    </div>
                                    @error('password') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">تأكيد كلمة المرور</label>
                                    <div class="relative">
                                        <input type="password" name="password_confirmation" placeholder="أعد إدخال كلمة المرور" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" required minlength="8">
                                        <button type="button" class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" onclick="togglePassword(this)">
                                            <i class="fa-regular fa-eye-slash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex justify-start gap-3 pt-6">
                                    <button type="submit" class="bg-primary hover:bg-blue-700 text-white font-black py-3 px-10 rounded-xl transition-all text-sm shadow-md shadow-blue-100">
                                        حفظ
                                    </button>
                                    <button type="button" onclick="switchTab('profile')" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-black py-3 px-8 rounded-xl transition-all text-sm">
                                        إلغاء
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 3: Bank -->
                    <div id="tab-bank" class="tab-content">
                        <h2 class="text-2xl font-black text-gray-900 mb-6 text-right">حسابي البنكي</h2>
                        
                        <form action="{{ route('profile.bank.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-5">
                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">اسم البنك</label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', Auth::user()->bank_name) }}" placeholder="أدخل اسم البنك" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right">
                                    @error('bank_name') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">اسم الحساب</label>
                                    <input type="text" name="bank_account_name" value="{{ old('bank_account_name', Auth::user()->bank_account_name) }}" placeholder="أدخل اسم الحساب" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right">
                                    @error('bank_account_name') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">رقم الحساب</label>
                                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number', Auth::user()->bank_account_number) }}" placeholder="أدخل رقم الحساب (أرقام فقط)" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" dir="ltr" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    @error('bank_account_number') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">رقم الآيبان (IBAN)</label>
                                    <input type="text" name="bank_iban" value="{{ old('bank_iban', Auth::user()->bank_iban) }}" placeholder="SAXXXXXXXXXXXXXXXXXXXXXX" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" dir="ltr">
                                    @error('bank_iban') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">سويفت كود (Swift Code)</label>
                                    <input type="text" name="bank_swift_code" value="{{ old('bank_swift_code', Auth::user()->bank_swift_code) }}" placeholder="أدخل السويفت كود للبنك" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right" dir="ltr" oninput="this.value = this.value.toUpperCase();">
                                    @error('bank_swift_code') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-gray-700 font-bold mb-2 text-right text-sm">عنوان البنك / الفرع</label>
                                    <input type="text" name="bank_address" value="{{ old('bank_address', Auth::user()->bank_address) }}" placeholder="أدخل عنوان البنك أو اسم الفرع" class="w-full bg-white border border-gray-200 rounded-2xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary font-bold text-sm text-right">
                                    @error('bank_address') <span class="text-red-500 text-xs font-bold mt-1 block text-right">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex justify-start gap-3 pt-6">
                                    <button type="submit" class="bg-primary hover:bg-blue-700 text-white font-black py-3 px-10 rounded-xl transition-all text-sm shadow-md shadow-blue-100">
                                        حفظ
                                    </button>
                                    <button type="button" onclick="switchTab('profile')" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-black py-3 px-8 rounded-xl transition-all text-sm">
                                        إلغاء
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Map Modal -->
<div id="mapModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm" dir="rtl">
    <div class="bg-white w-full max-w-2xl rounded-[28px] overflow-hidden shadow-2xl">
        <div class="flex justify-between items-center p-6 border-b border-gray-100">
            <h3 class="text-xl font-black text-gray-900">تحديد الموقع على الخريطة</h3>
            <button onclick="closeMapModal()" class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full bg-gray-50">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <p class="text-gray-500 text-sm font-bold px-6 pt-4 pb-2 text-right">انقر على الخريطة أو اسحب الدبوس لتحديد موقعك</p>
        <div style="position: relative; width: 100%; height: 380px; background: #e5e7eb;">
            <div id="profileMapEl" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; z-index: 1;"></div>
        </div>
        <div class="p-5 flex gap-3 justify-between items-center">
            <button type="button" onclick="useMyLocation()" class="flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-5 rounded-xl text-sm transition-all">
                <i class="fa-solid fa-location-crosshairs text-primary"></i>
                موقعي الحالي
            </button>
            <button type="button" onclick="confirmMapLocation()" class="bg-primary hover:bg-blue-700 text-white font-black py-3 px-10 rounded-xl text-sm shadow-md shadow-blue-100 transition-all">
                تأكيد الموقع
            </button>
        </div>
    </div>
</div>

<!-- Avatar Upload Modal -->
<div id="avatarModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4 bg-black/50 backdrop-blur-sm transition-all">
    <div class="bg-white w-full max-w-md rounded-[28px] overflow-hidden shadow-2xl animate-in fade-in zoom-in duration-300" dir="rtl">
        <div class="flex justify-between items-center p-6 border-b border-gray-100">
            <h3 class="text-xl font-black text-gray-900">تحديث الصورة الشخصية</h3>
            <button onclick="closeAvatarModal()" class="text-gray-400 hover:text-red-500 transition-colors w-8 h-8 flex items-center justify-center rounded-full bg-gray-50">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="p-6">
            <div class="flex flex-col items-center">
                <div class="relative w-48 h-48 mb-6">
                    <img src="{{ Auth::user()->avatar ?? asset('images/default-avatar.png') }}" id="modal-avatar-preview" class="w-full h-full object-cover rounded-full border-4 border-gray-100 shadow-md">
                    <label for="modal-avatar-input" class="absolute bottom-3 right-3 w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg hover:bg-blue-700 transition-colors border-4 border-white">
                        <i class="fa-solid fa-camera text-xl"></i>
                    </label>
                </div>
                <input type="file" id="modal-avatar-input" class="hidden" accept="image/png, image/jpeg, image/jpg, image/webp" onchange="previewModalAvatar(this)">
                
                <p class="text-gray-500 text-sm mb-6 text-center">اختر صورة شخصية لتظهر في حسابك. الصيغ المدعومة: JPG, PNG.</p>
                
                <div class="flex gap-3 w-full">
                    <button type="button" onclick="uploadAvatar()" id="save-avatar-btn" class="flex-1 bg-primary text-white font-black py-3 rounded-xl hover:bg-blue-700 transition-all text-sm shadow-md shadow-blue-100">
                        حفظ الصورة
                    </button>
                    <button type="button" onclick="closeAvatarModal()" class="flex-1 bg-gray-100 text-gray-600 font-black py-3 rounded-xl hover:bg-gray-200 transition-all text-sm">
                        إلغاء
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    function switchTab(tabId) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(el => {
            el.classList.remove('active');
        });
        // Remove active from links
        document.querySelectorAll('.tab-link').forEach(el => {
            el.classList.remove('active');
        });

        // Show target tab
        document.getElementById('tab-' + tabId).classList.add('active');
        document.getElementById('tab-btn-' + tabId).classList.add('active');
    }

    function togglePassword(btn) {
        const input = btn.previousElementSibling;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            icon.classList.add('text-primary');
        } else {
            input.type = 'password';
            icon.classList.add('fa-eye-slash');
            icon.classList.remove('fa-eye');
            icon.classList.remove('text-primary');
        }
    }

    function openAvatarModal() {
        const modal = document.getElementById('avatarModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function previewCover(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('cover-preview-img').src = e.target.result;
                document.getElementById('cover-preview-wrap').classList.remove('hidden');
                document.getElementById('cover-placeholder').classList.add('hidden');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ---- MAP MODAL ----
    let profileMap = null;
    let profileMarker = null;

    function openMapModal() {
        const modal = document.getElementById('mapModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Ensure the map container is visible before init
        const mapEl = document.getElementById('profileMapEl');
        mapEl.style.height = '380px';

        setTimeout(() => {
            if (!profileMap) {
                const defaultLat = parseFloat(document.getElementById('lat-input').value) || 24.7136;
                const defaultLng = parseFloat(document.getElementById('lng-input').value) || 46.6753;

                profileMap = L.map('profileMapEl', { zoomControl: true }).setView([defaultLat, defaultLng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap',
                    maxZoom: 19
                }).addTo(profileMap);

                profileMarker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(profileMap);

                profileMarker.on('dragend', function() {
                    const pos = profileMarker.getLatLng();
                    document.getElementById('lat-input').value = pos.lat.toFixed(6);
                    document.getElementById('lng-input').value = pos.lng.toFixed(6);
                    fetchAddress(pos.lat, pos.lng);
                });

                profileMap.on('click', function(e) {
                    profileMarker.setLatLng(e.latlng);
                    document.getElementById('lat-input').value = e.latlng.lat.toFixed(6);
                    document.getElementById('lng-input').value = e.latlng.lng.toFixed(6);
                    fetchAddress(e.latlng.lat, e.latlng.lng);
                });
            }

            // Always call invalidateSize after modal opens
            profileMap.invalidateSize();
        }, 400);
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
        document.getElementById('mapModal').classList.remove('flex');
    }

    function confirmMapLocation() {
        closeMapModal();
    }

    function fetchAddress(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&accept-language=ar`)
            .then(r => r.json())
            .then(data => {
                if (data && data.address) {
                    const addr = data.address;
                    let city = addr.city || addr.town || addr.village || addr.state || "";
                    city = city.replace(/محافظة |منطقة |مدينة /g, '').trim();
                    
                    let neighborhood = addr.suburb || addr.neighbourhood || addr.quarter || "";
                    neighborhood = neighborhood.replace(/حي /g, '').trim();
                    
                    let shortAddress = "";
                    if (neighborhood && city) {
                        shortAddress = neighborhood + "، " + city;
                    } else {
                        shortAddress = city || neighborhood || data.display_name;
                    }
                    document.getElementById('address-input').value = shortAddress;
                } else if (data && data.display_name) {
                    document.getElementById('address-input').value = data.display_name;
                }
            })
            .catch(() => {});
    }

    function useMyLocation() {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition(pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            profileMap.setView([lat, lng], 15);
            profileMarker.setLatLng([lat, lng]);
            document.getElementById('lat-input').value = lat.toFixed(6);
            document.getElementById('lng-input').value = lng.toFixed(6);
            fetchAddress(lat, lng);
        });
    }

    function closeAvatarModal() {
        const modal = document.getElementById('avatarModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        // Reset preview to original
        document.getElementById('modal-avatar-preview').src = document.getElementById('sidebar-avatar-preview').src;
        document.getElementById('modal-avatar-input').value = "";
    }

    function previewModalAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('modal-avatar-preview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function uploadAvatar() {
        const input = document.getElementById('modal-avatar-input');
        if (!input.files || !input.files[0]) {
            Swal.fire('تنبيه', 'يرجى اختيار صورة أولاً', 'info');
            return;
        }

        const formData = new FormData();
        formData.append('avatar', input.files[0]);

        const btn = document.getElementById('save-avatar-btn');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري الحفظ...';
        btn.disabled = true;

        fetch("{{ route('profile.avatar.update') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if(response.status === 419) {
                throw new Error('session_expired');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update images on the page
                document.getElementById('sidebar-avatar-preview').src = data.avatar_url;
                
                // Update navbar avatar if exists
                const navAvatar = document.querySelector('.user-avatar img');
                if (navAvatar) navAvatar.src = data.avatar_url;

                closeAvatarModal();
                Swal.fire({
                    icon: 'success',
                    title: 'نجاح',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: { popup: 'rounded-[32px]' }
                });
            } else {
                Swal.fire('خطأ', data.message || 'حدث خطأ أثناء الرفع', 'error');
            }
        })
        .catch(error => {
            if (error.message === 'session_expired') {
                Swal.fire('الجلسة انتهت', 'يرجى تحديث الصفحة والمحاولة مرة أخرى.', 'warning').then(() => location.reload());
            } else {
                Swal.fire('خطأ', 'فشل الاتصال بالخادم، يرجى المحاولة لاحقاً', 'error');
            }
        })
        .finally(() => {
            btn.innerHTML = 'حفظ الصورة';
            btn.disabled = false;
        });
    }

    // Check if there are errors and switch to the correct tab automatically
    @if($errors->has('current_password') || $errors->has('password'))
        switchTab('password');
    @endif
    @if($errors->has('bank_name') || $errors->has('bank_account_name') || $errors->has('bank_account_number') || $errors->has('bank_iban') || $errors->has('bank_address') || $errors->has('bank_swift_code'))
        switchTab('bank');
    @endif
</script>
@endpush
