<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ConvertEpubToPdfJob;
use App\Models\Precedent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPrecedentController extends Controller
{
    /**
     * #comment إرجاع قائمة كل الاجتهادات للأدمن (لشاشة إدارة الاجتهادات)
     */
    public function index(): JsonResponse
    {
        $precedents = Precedent::orderByDesc('created_at')->get();

        return response()->json($precedents);
    }

    /**
     * #comment إنشاء اجتهاد جديد مع رفع ملفه من لوحة الأدمن
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file'  => 'required|file|max:20480', // #comment حجم أقصى 20MB
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        // #comment نتحقق من الامتداد يدوياً لأن mimes:epub قد يفشل (Laravel يتحقق من MIME type وليس الامتداد)
        $allowed = ['pdf', 'doc', 'docx', 'epub'];
        if (!in_array($ext, $allowed, true)) {
            return response()->json([
                'message' => 'نوع الملف غير مدعوم. المسموح: pdf, doc, docx, epub.',
                'errors'  => ['file' => ['نوع الملف غير مدعوم. المسموح: pdf, doc, docx, epub.']],
            ], 422);
        }

        $data = ['title' => $request->input('title'), 'file' => $file];
        // #comment نحدد نوع الملف بناءً على الامتداد
        if ($ext === 'pdf') {
            $fileType = 'pdf';
        } elseif (in_array($ext, ['doc', 'docx'], true)) {
            $fileType = 'word';
        } else {
            $fileType = 'epub';
        }

        // #comment نخزن الملف في قرص public داخل مجلد precedents
        $path = $file->store('precedents', 'public');

        $precedent = Precedent::create([
            'title'          => $data['title'],
            'file_path'      => $path,
            'file_type'      => $fileType,
            'allow_download' => false,
            'is_active'      => true,
        ]);

        // تحويل فوري من EPUB إلى PDF عند الرفع — المستخدم يرى PDF فقط (عرض + بحث، بدون نسخ/تحميل/لقطة شاشة)
        if ($fileType === 'epub') {
            (new ConvertEpubToPdfJob($precedent))->handle();
            $precedent->refresh();
            if ($precedent->file_type !== 'pdf') {
                Storage::disk('public')->delete($precedent->file_path);
                $precedent->delete();
                return response()->json([
                    'message' => 'فشل تحويل EPUB إلى PDF. تأكد من تثبيت Calibre (ebook-convert) على السيرفر.',
                    'errors'  => ['file' => ['فشل التحويل إلى PDF.']],
                ], 422);
            }
        }

        return response()->json([
            'success'   => true,
            'message'   => 'تم إضافة الاجتهاد بنجاح.',
            'precedent' => $precedent,
        ], 201);
    }

    /**
     * #comment حذف اجتهاد مع حذف الملف من التخزين
     */
    public function destroy(Precedent $precedent): JsonResponse
    {
        Storage::disk('public')->delete($precedent->file_path);
        $precedent->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الاجتهاد.',
        ]);
    }
}

