<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * #comment إنشاء جدول الاجتهادات لحفظ بيانات كل ملف
     */
    public function up(): void
    {
        Schema::create('precedents', function (Blueprint $table) {
            $table->id();
            $table->string('title');                             // #comment اسم الاجتهاد الظاهر للمستخدم
            $table->string('file_path');                         // #comment مسار الملف داخل storage (مثلاً storage/app/public/precedents/...)
            $table->enum('file_type', ['pdf', 'word', 'epub'])->default('pdf'); // #comment نوع الملف (PDF أو Word أو EPUB)
            $table->boolean('allow_download')->default(false);   // #comment اجتهادات = عرض فقط بدون تحميل
            $table->boolean('is_active')->default(true);         // #comment لإخفاء/إظهار الاجتهاد بدون حذفه
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precedents');
    }
};

