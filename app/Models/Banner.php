<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'image',
        'description',
        'button_text',
        'status',
        'for_user_type',
        'items'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'items' => 'array',
        'status' => 'boolean'
    ];

    protected $appends = ['url'];

    public function getItemsAttribute($value)
    {
        $items = $this->castAttribute('items', $value);
        if (!is_array($items)) {
            return [];
        }

        foreach ($items as &$item) {
            $url = $item['url'] ?? '#';
            $title = $item['title'] ?? $this->title ?? '';
            $desc = $item['description'] ?? $this->description ?? '';

            $target = $this->resolveLogicalTarget($title, $desc);
            
            $item['url'] = $target['url'];
            $item['target_type'] = $target['type'];
            $item['target_id'] = $target['id'];
            $item['search_query'] = $target['query'];
        }

        return $items;
    }

    /**
     * Accessor for a single banner URL fallback.
     */
    public function getUrlAttribute()
    {
        return $this->resolveLogicalUrl($this->title, $this->description);
    }

    /**
     * Helper to resolve logical URL from title & description keywords.
     */
    public function resolveLogicalUrl($title, $desc)
    {
        return $this->resolveLogicalTarget($title, $desc)['url'];
    }

    /**
     * Helper to resolve logical target details for web and mobile clients.
     */
    public function resolveLogicalTarget($title, $desc)
    {
        $text = mb_strtolower($title . ' ' . $desc);

        // Fetch parent categories dynamically from DB with cache
        $categories = \Illuminate\Support\Facades\Cache::remember('parent_categories_list', now()->addDay(), function () {
            return \App\Models\Category::whereNull('parent_id')->select('id', 'name')->get()->toArray();
        });

        // 1. Try dynamic name matching to send them to /search?q=CATEGORY_NAME
        foreach ($categories as $cat) {
            $catName = $cat['name'];
            $cleanName = mb_strtolower($catName);
            
            // Strip "ال" prefix from Arabic category names (e.g. "السيارات" -> "سيارات")
            if (str_starts_with($cleanName, 'ال')) {
                $cleanName = mb_substr($cleanName, 2);
            }

            if (!empty($cleanName) && (mb_strpos($text, $cleanName) !== false || mb_strpos($text, mb_strtolower($catName)) !== false)) {
                return [
                    'url'   => route('search', ['q' => $catName]),
                    'type'  => 'category',
                    'id'    => $cat['id'],
                    'query' => $catName
                ];
            }
        }

        // 2. Try synonyms matching for common categories, mapping to the category name directly
        $synonyms = [
            'السيارات' => ['سيارات', 'سياره', 'سيارة', 'مركبات', 'مركبة', 'دباب', 'دراجات', 'دراجه', 'دراجة', 'شاحنات', 'شاحنة', 'شاحنه', 'كلاسيكية', 'رياضية', 'كلاسيكيه', 'رياضيه', 'الجيوب', 'جيب', 'صالون', 'هاف', 'بيك اب', 'دبل', 'لوري'],
            'العقارات' => ['عقارات', 'عقار', 'فلل', 'شقق', 'فيلا', 'شقة', 'شقه', 'أراضي', 'ارضي', 'أرض', 'ارض', 'مباني', 'مبنى', 'مبني', 'بيت', 'بيوت', 'استثمار', 'السكن', 'سكنية', 'سكنيه', 'تجارية', 'تجاريه', 'عمارة', 'عماره', 'قصور', 'قصر', 'مخطط', 'مخططات'],
            'الأجهزة'  => ['أجهزة', 'اجهزة', 'الأجهزة', 'الاجهزة', 'إلكترونيات', 'الكترونيات', 'جوال', 'جوالات', 'هواتف', 'هاتف', 'لابتوب', 'حاسوب', 'كمبيوتر', 'ساعات', 'ساعة', 'ساعه', 'شاشات', 'شاشة', 'شاشه', 'تلفزيون', 'ذكية', 'الذكية', 'ايباد', 'تابلت', 'كاميرا', 'كاميرات', 'بلايستيشن', 'ألعاب', 'العاب', 'سماعة', 'سماعات', 'اكسسوارات'],
            'أثاث'     => ['أثاث', 'اثاث', 'كنب', 'سجاد', 'غرف', 'غرفة', 'غرفه', 'مفروشات', 'ديكور', 'مجالس', 'مجلس', 'سرير', 'دواليب', 'دولاب', 'مطبخ', 'مطابخ', 'طاولة', 'طاولات', 'كراسي', 'كرسي', 'ستائر', 'ستاره', 'ستارة'],
            'الخدمات'  => ['خدمات', 'خدمة', 'خدمه', 'صيانة', 'صيانه', 'تركيب', 'تنظيف', 'شحن', 'فحص', 'نقل', 'توصيل', 'مقاولات', 'مقاول', 'كهربائي', 'سباك', 'غسيل', 'تصليح', 'ورشة', 'ورشه', 'استشارات', 'استشارة']
        ];

        foreach ($synonyms as $targetCategoryName => $words) {
            $catRecord = collect($categories)->first(function($c) use ($targetCategoryName) {
                return mb_strtolower($c['name']) === mb_strtolower($targetCategoryName);
            });

            if ($catRecord) {
                foreach ($words as $word) {
                    if (mb_strpos($text, $word) !== false) {
                        return [
                            'url'   => route('search', ['q' => $targetCategoryName]),
                            'type'  => 'category',
                            'id'    => $catRecord['id'],
                            'query' => $targetCategoryName
                        ];
                    }
                }
            }
        }

        // 3. Fallback: Search route with title as query string
        if (!empty($title)) {
            return [
                'url'   => route('search', ['q' => $title]),
                'type'  => 'search',
                'id'    => null,
                'query' => $title
            ];
        }

        return [
            'url'   => route('search'),
            'type'  => 'search',
            'id'    => null,
            'query' => ''
        ];
    }
}
