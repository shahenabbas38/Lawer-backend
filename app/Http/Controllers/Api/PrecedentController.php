<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Precedent;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class PrecedentController extends Controller
{
    /**
     * #comment إرجاع قائمة الاجتهادات للمستخدم (أسماء فقط)
     */
    public function index(): JsonResponse
    {
        $precedents = Precedent::where('is_active', true)
            ->orderBy('title')
            ->get()
            ->map(function (Precedent $precedent) {
                return [
                    'id'             => $precedent->id,
                    'title'          => $precedent->title,
                    'file_type'      => $precedent->file_type,
                    'allow_download' => $precedent->allow_download,
                ];
            });

        return response()->json($precedents);
    }

    /**
     * #comment إرجاع رابط الملف ليستخدمه تطبيق Flutter داخل عارض الـ PDF
     */
    public function file(Precedent $precedent): JsonResponse
    {
        // #comment URL عام لقراءة الملف من التخزين (بدون فرض التحميل)
        $url = Storage::disk('public')->url($precedent->file_path);

        return response()->json([
            'id'             => $precedent->id,
            'title'          => $precedent->title,
            'file_type'      => $precedent->file_type,
            'pdf_url'        => $url,
            'allow_download' => $precedent->allow_download,
        ]);
    }
}

