<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * Standardized JSON Response Helper
     */
    protected function apiResponse($success, $message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * List all services belonging to the authenticated partner
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Service::where('user_id', $user->id);

        // Status Filter (active, pending, rejected)
        if ($request->filled('status') && in_array($request->status, ['active', 'pending', 'rejected'])) {
            $query->where('status', $request->status);
        }

        // Search Filter (by title or description)
        if ($request->filled('q')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        // Category Filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Sorting
        $sort = $request->get('sort', 'newest');
        if ($sort === 'price_high') {
            $query->orderBy('price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('price', 'asc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $services = $query->with('category')->get()->map(function($service) {
            return [
                'id' => $service->id,
                'category_id' => $service->category_id,
                'category_name' => $service->category ? $service->category->name : null,
                'title' => $service->title,
                'description' => $service->description,
                'price' => (float)$service->price,
                'image' => $service->image ? (\Illuminate\Support\Str::startsWith($service->image, 'http') ? $service->image : url('/' . ltrim($service->image, '/'))) : null,
                'phone' => $service->phone,
                'email' => $service->email,
                'status' => $service->status,
                'created_at' => $service->created_at,
            ];
        });

        return $this->apiResponse(true, 'قائمة خدماتي', $services);
    }

    /**
     * Add a new service
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'image_url' => 'nullable|url',
            'category_id' => 'required|exists:categories,id',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ], [
            'title.required' => 'عنوان الخدمة مطلوب.',
            'category_id.required' => 'التصنيف مطلوب.',
            'price.required' => 'سعر الخدمة مطلوب.',
            'price.numeric' => 'يجب أن يكون السعر قيمة رقمية.',
            'image.image' => 'يجب إدخال ملف صورة صالح للخدمة.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'بيانات غير مكتملة أو خاطئة', $validator->errors(), 422);
        }

        $user = $request->user();
        
        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/services');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $filename);
            $imagePath = '/uploads/services/' . $filename;
        } elseif ($request->filled('image_url')) {
            $url = $request->image_url;
            try {
                $imageContent = @file_get_contents($url);
                if ($imageContent) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($imageContent);
                    $mimeMap = [
                        'image/jpeg' => 'jpg',
                        'image/jpg'  => 'jpg',
                        'image/png'  => 'png',
                        'image/gif'  => 'gif',
                        'image/webp' => 'webp',
                        'image/svg+xml' => 'svg',
                        'image/bmp'  => 'bmp',
                        'image/x-icon' => 'ico',
                        'image/tiff' => 'tiff',
                    ];
                    $extension = $mimeMap[$mimeType] ?? (pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
                    $filename = time() . '_url_' . uniqid() . '.' . $extension;
                    $destinationPath = public_path('uploads/services');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    @file_put_contents($destinationPath . '/' . $filename, $imageContent);
                    $imagePath = '/uploads/services/' . $filename;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        $service = Service::create([
            'user_id' => $user->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
            'phone' => $request->phone ?: $user->phone,
            'email' => $request->email ?: $user->email,
        ]);

        return $this->apiResponse(true, 'تم إضافة الخدمة بنجاح، وهي قيد المراجعة حالياً من قبل الإدارة', [
            'id' => $service->id,
            'category_id' => $service->category_id,
            'title' => $service->title,
            'description' => $service->description,
            'price' => (float)$service->price,
            'image' => $service->image ? url($service->image) : null,
            'phone' => $service->phone,
            'email' => $service->email,
            'created_at' => $service->created_at,
        ], 201);
    }

    /**
     * Edit an existing service
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $service = Service::where('user_id', $user->id)->find($id);

        if (!$service) {
            return $this->apiResponse(false, 'الخدمة غير موجودة أو غير مصرح لك بتعديلها', null, 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'image_url' => 'nullable|url',
            'category_id' => 'nullable|exists:categories,id',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
        ], [
            'title.required' => 'عنوان الخدمة مطلوب.',
            'price.required' => 'سعر الخدمة مطلوب.',
            'price.numeric' => 'يجب أن يكون السعر قيمة رقمية.',
            'image.image' => 'يجب إدخال ملف صورة صالح للخدمة.',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة.',
        ]);

        if ($validator->fails()) {
            return $this->apiResponse(false, 'فشل التحقق من البيانات', $validator->errors(), 422);
        }

        $imagePath = $service->getRawOriginal('image');
        if ($request->hasFile('image')) {
            // Delete old file if exists
            if ($imagePath && file_exists(public_path($imagePath))) {
                @unlink(public_path($imagePath));
            }

            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/services');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }
            $file->move($destinationPath, $filename);
            $imagePath = '/uploads/services/' . $filename;
        } elseif ($request->filled('image_url')) {
            $url = $request->image_url;
            try {
                $imageContent = @file_get_contents($url);
                if ($imageContent) {
                    // Delete old file if exists
                    if ($imagePath && file_exists(public_path($imagePath))) {
                        @unlink(public_path($imagePath));
                    }
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mimeType = $finfo->buffer($imageContent);
                    $mimeMap = [
                        'image/jpeg' => 'jpg',
                        'image/jpg'  => 'jpg',
                        'image/png'  => 'png',
                        'image/gif'  => 'gif',
                        'image/webp' => 'webp',
                        'image/svg+xml' => 'svg',
                        'image/bmp'  => 'bmp',
                        'image/x-icon' => 'ico',
                        'image/tiff' => 'tiff',
                    ];
                    $extension = $mimeMap[$mimeType] ?? (pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
                    $filename = time() . '_url_edit_' . uniqid() . '.' . $extension;
                    $destinationPath = public_path('uploads/services');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0777, true);
                    }
                    @file_put_contents($destinationPath . '/' . $filename, $imageContent);
                    $imagePath = '/uploads/services/' . $filename;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        $service->update([
            'title' => $request->title ?? $service->title,
            'description' => $request->has('description') ? $request->description : $service->description,
            'price' => $request->price ?? $service->price,
            'image' => $imagePath,
            'category_id' => $request->category_id ?? $service->category_id,
            'phone' => $request->has('phone') ? $request->phone : $service->phone,
            'email' => $request->has('email') ? $request->email : $service->email,
            'status' => 'pending',
        ]);

        return $this->apiResponse(true, 'تم تحديث الخدمة بنجاح، وهي قيد المراجعة حالياً من قبل الإدارة', [
            'id' => $service->id,
            'category_id' => $service->category_id,
            'title' => $service->title,
            'description' => $service->description,
            'price' => (float)$service->price,
            'image' => $service->image ? url($service->image) : null,
            'phone' => $service->phone,
            'email' => $service->email,
            'created_at' => $service->created_at,
        ]);
    }

    /**
     * Delete a service
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $service = Service::where('user_id', $user->id)->find($id);

        if (!$service) {
            return $this->apiResponse(false, 'الخدمة غير موجودة أو غير مصرح لك بحذفها', null, 403);
        }

        // Delete associated image file
        $imagePath = $service->getRawOriginal('image');
        if ($imagePath && file_exists(public_path($imagePath))) {
            @unlink(public_path($imagePath));
        }

        $service->delete();

        return $this->apiResponse(true, 'تم حذف الخدمة بنجاح');
    }
}
