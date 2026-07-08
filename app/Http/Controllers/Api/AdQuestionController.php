<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdQuestionController extends Controller
{
    /**
     * Ask a question on an ad.
     */
    public function store(Request $request, $adId)
    {
        $request->validate([
            'question' => 'required|string|min:5|max:1000',
        ]);

        $ad = Ad::findOrFail($adId);

        // Prevent ad owner from asking a question on their own ad
        if ($ad->user_id === auth('sanctum')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك طرح سؤال على إعلانك الخاص.'
            ], 400);
        }

        $question = AdQuestion::create([
            'ad_id' => $ad->id,
            'user_id' => auth('sanctum')->id(),
            'question' => $request->question,
            'is_visible' => false, // Hidden by default until owner replies/approves
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال سؤالك بنجاح. سيقوم المعلن بالرد عليك في أقرب وقت.',
            'data' => $question
        ], 201);
    }

    /**
     * Reply to a question on your ad.
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'answer' => 'required|string|max:1000',
        ]);

        $question = AdQuestion::findOrFail($id);
        
        // Ensure current user is the owner of the ad
        if ($question->ad->user_id !== auth('sanctum')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالرد على هذا السؤال.'
            ], 403);
        }

        $question->update([
            'answer' => $request->answer,
            'answered_at' => now(),
            'is_visible' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الرد على السؤال بنجاح.',
            'data' => $question
        ]);
    }

    /**
     * Toggle visibility (show/hide) of a question.
     */
    public function toggleVisibility($id)
    {
        $question = AdQuestion::findOrFail($id);

        // Ensure current user is the owner of the ad
        if ($question->ad->user_id !== auth('sanctum')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل حالة هذا السؤال.'
            ], 403);
        }

        $question->is_visible = !$question->is_visible;
        $question->save();

        $status = $question->is_visible ? 'منشور' : 'مخفي';

        return response()->json([
            'success' => true,
            'is_visible' => $question->is_visible,
            'message' => "تم تعديل حالة ظهور السؤال بنجاح إلى: {$status}.",
            'data' => $question
        ]);
    }
}
