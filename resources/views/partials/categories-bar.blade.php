@php
    $navCategories = \App\Models\Category::whereNull('parent_id')->get();
@endphp

@if($navCategories->count() > 0)
<div class="categories-nav">
    <div class="container">
        <div class="category-pills">
            @foreach($navCategories as $category)
                <a href="{{ route('search', ['category' => $category->id]) }}" 
                   class="category-pill {{ request('category') == $category->id ? 'active' : '' }}">
                    {{ $category->name }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif
