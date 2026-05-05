@props(['paginator'])

<div class="flex items-center justify-between border-t border-slate-100 pt-6 mt-2">
    <div class="flex items-center gap-2 text-sm text-[#6F6F6F] font-bold">
        <span>الصفوف لكل صفحة:</span>
        <select class="border-transparent bg-transparent outline-none cursor-pointer focus:ring-0">
            <option>10</option>
            <option>25</option>
            <option>50</option>
        </select>
    </div>
    
    <div class="flex items-center gap-4 text-sm font-bold text-[#6F6F6F]">
        <span>{{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} من {{ $paginator->total() }}</span>
        <div class="flex items-center gap-2">
            <a href="{{ $paginator->previousPageUrl() }}" 
               class="p-1.5 px-3 bg-[#1A73E8] text-white rounded-lg hover:bg-blue-700 transition-all {{ $paginator->onFirstPage() ? 'opacity-50 pointer-events-none' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7-7"></path></svg>
            </a>
            <span class="px-3 min-w-[3rem] text-center">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
            <a href="{{ $paginator->nextPageUrl() }}" 
               class="p-1.5 px-3 bg-[#E5E7EB] text-[#6F6F6F] rounded-lg hover:bg-slate-300 transition-all {{ !$paginator->hasMorePages() ? 'opacity-50 pointer-events-none' : '' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
        </div>
    </div>
</div>
