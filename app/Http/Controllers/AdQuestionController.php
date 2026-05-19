<?php

namespace App\Http\Controllers;

use App\Models\Ad;
use App\Models\AdQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdQuestionController extends Controller
{
    public function store(Request $request, $adId)
    {
        $request->validate([
            'question' => 'required|string|min:5|max:1000',
        ]);

        $ad = Ad::findOrFail($adId);

        // Prevent ad owner from asking a question on their own ad
        if ($ad->user_id === Auth::id()) {
            return back()->with('error', 'لا يمكنك طرح سؤال على إعلانك الخاص.');
        }

        AdQuestion::create([
            'ad_id' => $ad->id,
            'user_id' => Auth::id(),
            'question' => $request->question,
            'is_visible' => false, // مخفي حتى يقوم المعلن بنشره
        ]);

        return back()->with('success', 'تم إرسال سؤالك بنجاح. سيقوم المعلن بالرد عليك في أقرب وقت.');
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'answer' => 'required|string|max:1000',
        ]);

        $question = AdQuestion::findOrFail($id);
        
        // Ensure the current user is the owner of the ad
        if ($question->ad->user_id !== Auth::id()) {
            abort(403, 'غير مصرح لك بالرد على هذا السؤال.');
        }

        $question->update([
            'answer' => $request->answer,
            'answered_at' => now(),
        ]);

        return back()->with('success', 'تم الرد على السؤال بنجاح.');
    }

    public function toggleVisibility($id)
    {
        $question = AdQuestion::findOrFail($id);

        // Ensure the current user is the owner of the ad
        if ($question->ad->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'غير مصرح لك بتعديل حالة هذا السؤال.'], 403);
        }

        $question->is_visible = !$question->is_visible;
        $question->save();

        $status = $question->is_visible ? 'منشور' : 'مخفي';

        return response()->json([
            'success' => true,
            'is_visible' => $question->is_visible,
            'message' => "تم تعديل حالة ظهور السؤال بنجاح إلى: {$status}."
        ]);
    }
}
