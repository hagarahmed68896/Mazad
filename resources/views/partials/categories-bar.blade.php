@if(!auth()->check() || auth()->user()->account_type !== 'partner')
@php
    $navCategories = \App\Models\Category::whereNull('parent_id')->get();
@endphp

@if($navCategories->count() > 0)
<div class="categories-nav">
    <div class="container">
        <div class="category-pills">
            @foreach($navCategories as $category)
                <a href="{{ route('categories.show', $category->id) }}" 
                   class="category-pill {{ (request('category') == $category->id || (isset($activeCategory) && $activeCategory->id == $category->id)) ? 'active' : '' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif
@endif
