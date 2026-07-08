<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'content', 'is_read', 'type', 'attachment'];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Check if the message content contains contact information (phone numbers, emails, links, social keywords).
     *
     * @param string|null $content
     * @return bool
     */
    public static function hasContactInfo($content)
    {
        if (empty($content)) {
            return false;
        }

        // Convert Arabic/Eastern digits to English digits
        $arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $englishDigits = ['0','1','2','3','4','5','6','7','8','9'];
        $normalized = str_replace($arabicDigits, $englishDigits, $content);

        // 1. Check for email addresses
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $normalized)) {
            return true;
        }

        // 2. Check for URLs / Links (with Whitelist for safe domains)
        if (preg_match_all('/([a-zA-Z0-9-]+\.[a-zA-Z]{2,6}(?:\.[a-zA-Z]{2,6})?)/', $normalized, $matches)) {
            $safeDomains = [
                'figma.com',
                'google.com',
                'youtube.com',
                'youtu.be',
                'behance.net',
                'github.com',
                'zoom.us',
                'microsoft.com',
                'dropbox.com',
                'pinterest.com',
                'mazad.com',
                'mazad-admin.com',
                'kiwi.ws'
            ];

            foreach ($matches[1] as $match) {
                $domain = strtolower($match);
                $isSafe = false;
                foreach ($safeDomains as $safe) {
                    if ($domain === $safe || str_ends_with($domain, '.' . $safe)) {
                        $isSafe = true;
                        break;
                    }
                }
                if (!$isSafe) {
                    return true;
                }
            }
        }

        // Remove any URLs from the text before doing the phone number check to avoid false positives from URL hashes/parameters!
        $textForPhoneCheck = preg_replace('/https?:\/\/[^\s]+/i', '', $normalized);
        $textForPhoneCheck = preg_replace('/www\.[^\s]+/i', '', $textForPhoneCheck);

        // 3. Check for phone numbers (8 or more digits when stripped of non-digit characters)
        $digitsOnly = preg_replace('/[^\d]/', '', $textForPhoneCheck);
        if (strlen($digitsOnly) >= 8) {
            return true;
        }

        // 4. Check for social media keywords or contact phrases
        $keywords = [
            'واتساب', 'واتس', 'وتساب', 'وتس', 'whatsapp', 'whats',
            'سناب', 'snapchat', 'snap',
            'انستا', 'انستجرام', 'انستغرام', 'instagram', 'insta',
            'فيسبوك', 'فيس بوك', 'facebook',
            'تليجرام', 'تيليجرام', 'telegram',
            'رقمي', 'جوالي', 'تليفون', 'تلفون', 'موبايل', 'هاتف',
            'إيميل', 'ايميل', 'بريد الكتروني', 'بريد إلكتروني'
        ];

        foreach ($keywords as $keyword) {
            if (mb_stripos($normalized, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }
}
