<?php

namespace Database\Seeders;

use App\Models\Ad;
use App\Models\AdAttachment;
use Illuminate\Database\Seeder;

class AdAttachmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ads = Ad::limit(10)->get();
        foreach ($ads as $ad) {
            AdAttachment::create([
                'ad_id' => $ad->id,
                'file_path' => 'attachments/technical_report.pdf',
                'file_name' => 'التقرير الفني للمنتج',
                'file_size' => '5.3MB',
                'file_type' => 'pdf'
            ]);
            
            AdAttachment::create([
                'ad_id' => $ad->id,
                'file_path' => 'attachments/title_deed.pdf',
                'file_name' => 'صك الملكية / شهادة المنشأ',
                'file_size' => '2.1MB',
                'file_type' => 'pdf'
            ]);
        }
    }
}
