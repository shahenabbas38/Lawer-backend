<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Precedent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
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
        $disk = Storage::disk('public');
        $fileAvailable = $disk->exists($precedent->file_path);
        // للعرض فقط: نعطي رابط يخدم الملف بـ Content-Disposition: inline حتى لا يبدأ التحميل
        $url = $precedent->allow_download
            ? $disk->url($precedent->file_path)
            : url()->route('api.precedents.view', ['precedent' => $precedent->id]);

        return response()->json([
            'id'              => $precedent->id,
            'title'           => $precedent->title,
            'file_type'       => $precedent->file_type,
            'pdf_url'         => $url,
            'allow_download'  => $precedent->allow_download,
            'file_available'  => $fileAvailable,
        ]);
    }

    /**
     * #comment خدمة الملف للعرض فقط (inline) — يمنع التحميل التلقائي في المتصفح/الويب فيو
     */
    public function view(Precedent $precedent): Response
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($precedent->file_path)) {
            abort(404);
        }
        $path = $disk->path($precedent->file_path);
        $mime = match (strtolower($precedent->file_type ?? '')) {
            'pdf'   => 'application/pdf',
            'epub'  => 'application/epub+zip',
            'word'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            default => 'application/octet-stream',
        };

        return response()->file($path, [
            'Content-Type'        => $mime,
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
        ]);
    }
}

