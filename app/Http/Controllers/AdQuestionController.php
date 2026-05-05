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

        AdQuestion::create([
            'ad_id' => $ad->id,
            'user_id' => Auth::id(),
            'question' => $request->question,
        ]);

        return back()->with('success', 'تم إرسال سؤالك بنجاح. سيقوم المعلن بالرد عليك في أقرب وقت.');
    }
}
