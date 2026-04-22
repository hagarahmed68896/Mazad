@extends('layouts.admin')

@section('title', 'إعدادات الإعلانات')

@section('content')
<div class="max-w-4xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-6 duration-1000 pb-10">
    
    <!-- Title -->
    <div>
        <h2 class="text-[32px] font-black text-[#1C1C1C]">إعدادات الإعلانات</h2>
    </div>

    <form action="{{ route('admin.settings.ads.update') }}" method="POST" class="space-y-8" 
          x-data="{ 
            percentages: {{ isset($settings['bidding_alert_percentages']) ? $settings['bidding_alert_percentages'] : '[]' }},
            newPercentage: '',
            addPercentage() {
                if (this.newPercentage.trim()) {
                    this.percentages.push(this.newPercentage);
                    this.newPercentage = '';
                }
            },
            removePercentage(index) {
                this.percentages.splice(index, 1);
            }
          }">
        @csrf
        <!-- Single hidden input to send percentages as JSON string -->
        <input type="hidden" name="bidding_alert_percentages" x-bind:value="JSON.stringify(percentages)">
        
        <div class="bg-white rounded-[2.5rem] p-10 shadow-[0_20px_70px_-20px_rgba(0,0,0,0.05)] border border-slate-50 space-y-10">
            
            <!-- Bidding Alert Percentages -->
            <div class="space-y-6">
                <label class="text-sm font-black text-slate-900 pr-2 block text-right">نسب تنبيه المزايدة</label>
                
                <!-- Add New Percentage Row -->
                <div class="flex items-center gap-4">
              
                    
                    <div class="flex-1 relative">
                        <input type="text" x-model="newPercentage" placeholder="أدخل نسبة تنبيه المزايدة"
                               class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl pr-4 pl-16 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] transition-all text-right">
                        <div class="absolute inset-y-0 left-0 px-6 flex items-center border-l border-slate-200 bg-slate-100/50 rounded-l-2xl text-slate-400 font-black text-sm">
                            %
                        </div>
                    </div>
                    <button type="button" @click="addPercentage()" class="h-14 px-8 bg-[#1A73E8] text-white rounded-2xl font-black text-sm shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all shrink-0">
                        أضف
                    </button>
                </div>

                <!-- Existing Percentages List -->
                <div class="flex flex-wrap gap-4">
                    <template x-for="(perc, index) in percentages" :key="index">
                        <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 px-4 py-3 rounded-xl animate-in fade-in zoom-in">
                            <span class="text-sm font-black text-[#1A73E8]" x-text="perc + '%'"></span>
                            <button type="button" @click="removePercentage(index)" class="text-rose-400 hover:text-rose-500 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Negotiation Attempts -->
            <div class="space-y-3 text-right">
                <label class="text-sm font-black text-slate-900 pr-2">عدد محاولات التفاوض للمستخدم</label>
                <input type="number" name="negotiation_attempts" value="{{ $settings['negotiation_attempts'] ?? '' }}" placeholder="أدخل عدد محاولات التفاوض للمستخدم"
                       class="w-full mt-2 h-14 bg-slate-50 border border-slate-200 rounded-2xl px-6 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] transition-all text-right">
            </div>

            <!-- Max Ad Duration -->
            <div class="space-y-3 text-right mt-4">
                <label class="text-sm font-black text-slate-900 pr-2">الحد الأقصى لمدة عرض الإعلان</label>
                <div class="relative mt-2">
                    <input type="number" name="max_ad_duration" value="{{ $settings['max_ad_duration'] ?? '' }}" placeholder="أدخل الحد الأقصى لمدة عرض الإعلان"
                           class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl pr-4 pl-20 text-sm font-bold text-[#1C1C1C] outline-none focus:border-[#1A73E8] transition-all text-right">
                    <div class="absolute inset-y-0 left-0 px-6 flex items-center border-l border-slate-200 bg-slate-100/50 rounded-l-2xl text-slate-400 font-black text-sm">
                        أيام
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 text-[15px]">
                <a href="{{ route('admin.dashboard') }}" class="px-8 py-4 bg-slate-100 text-[#6F6F6F] rounded-2xl font-black hover:bg-slate-200 transition-all text-center">
                    إلغاء
                </a>
                <button type="submit" class="px-8 py-4 bg-[#1A73E8] text-white rounded-2xl font-black shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                    حفظ
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
