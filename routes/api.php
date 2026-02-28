<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VerifyPaymentController;
use App\Http\Controllers\Api\PrecedentController;
use App\Http\Controllers\Api\AdminPrecedentController;

/*
| POST /api/verify-payment — تحقق من الدفع (name, operation_number, device_id)
*/
Route::post('/verify-payment', [VerifyPaymentController::class, 'verify']);

/*
| GET /api/precedents             — قائمة الاجتهادات للمستخدم
| GET /api/precedents/{id}/file   — تفاصيل اجتهاد + رابط الملف
| GET /api/precedents/{id}/view   — خدمة الملف للعرض فقط (inline، بدون تحميل)
*/
Route::get('/precedents', [PrecedentController::class, 'index']);
Route::get('/precedents/{precedent}/file', [PrecedentController::class, 'file']);
Route::get('/precedents/{precedent}/view', [PrecedentController::class, 'view'])->name('api.precedents.view');

/*
| مسارات الأدمن لإدارة الاجتهادات (ربطها لاحقاً بحماية auth للأدمن فقط)
| GET  /api/admin/precedents        — قائمة كاملة
| POST /api/admin/precedents        — إضافة اجتهاد مع رفع ملف
| DELETE /api/admin/precedents/{id} — حذف اجتهاد
*/
Route::prefix('admin')->group(function () {
    Route::get('/precedents', [AdminPrecedentController::class, 'index']);
    Route::post('/precedents', [AdminPrecedentController::class, 'store']);
    Route::delete('/precedents/{precedent}', [AdminPrecedentController::class, 'destroy']);
});
