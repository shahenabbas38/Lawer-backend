# تحويل EPUB إلى PDF تلقائياً

عند رفع اجتهاد من نوع **EPUB** من لوحة الأدمن، يُحوّل تلقائياً إلى **PDF** في الخلفية (إن كان Calibre مثبتاً)، فيصبح العرض في التطبيق عبر عارض الـ PDF الموحّد (عرض فقط، بدون تحميل/نسخ/لقطة شاشة حسب الإعداد).

## على الاستضافة (Railway) مع Dockerfile

الباك اند يتضمن **Dockerfile** يثبت **PHP + Calibre**، فيصبح التحويل التلقائي يعمل بعد الرفع.

1. **تفعيل البناء من Dockerfile على Railway:** في مشروع الـ Backend اختر استخدام **Dockerfile** كمصدر للبناء (بدل Nixpacks إن وُجد).
2. **متغير بيئة مهم:** في إعدادات الخدمة أضف:
   ```env
   QUEUE_CONNECTION=sync
   ```
   حتى يُنفَّذ تحويل EPUB→PDF **فوراً** أثناء طلب الرفع (بدون حاجة لعامل طابور منفصل). قد يستغرق الطلب دقيقة أو دقيقتين لملف كبير.
3. **التخزين المستمر:** لئلا تُفقد الملفات بعد إعادة التشغيل، اربط **Volume** بمجلد التخزين (مثلاً `storage/app/public`) كما هو موضّح في `docs/حفظ_الملفات_على_Railway.md`.
4. **Start Command** يمكن أن يبقى كما هو، مثلاً:
   ```bash
   php artisan migrate --force && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=$PORT
   ```

## متطلبات السيرفر (تشغيل محلي أو بدون Docker)

- تثبيت **Calibre** (يحتوي على الأمر `ebook-convert`):
  - **ويندوز:** [تحميل Calibre](https://calibre-ebook.com/download) ثم إضافة المسار إلى PATH أو تعيينه في `.env`
  - **أوبونتو/دبيان:** `sudo apt install calibre`
  - **ماك:** `brew install calibre`

## الإعداد (.env اختياري)

```env
# إذا ebook-convert غير في PATH، حدد المسار الكامل:
# ويندوز مثال:
CALIBRE_EBOOK_CONVERT="C:\Program Files\calibre2\ebook-convert.exe"
# لينكس/ماك عادة يكتفي بالأمر بدون مسار:
# CALIBRE_EBOOK_CONVERT=ebook-convert
```

## التفعيل التلقائي

- عند رفع ملف **EPUB** من الأدمن، يُرسل **Job** لتحويله إلى PDF.
- مع `QUEUE_CONNECTION=sync` (الموصى به على Railway بدون worker) يُنفَّذ التحويل في نفس طلب الرفع.
- بعد انتهاء التحويل يُحدَّث سجل الاجتهاد (مسار الملف ونوعه إلى PDF) ويُحذف ملف الـ EPUB.

إذا استخدمت طابوراً منفصلاً (مثلاً `database` أو `redis`)، شغّل العامل:

```bash
php artisan queue:work
```

## أوامر يدوية

تحويل كل الاجتهادات من نوع EPUB إلى PDF (إرسالها للطابور):

```bash
php artisan precedents:convert-epub-to-pdf
```

تحويل اجتهاد واحد بالمعرّف:

```bash
php artisan precedents:convert-epub-to-pdf --id=5
```

تشغيل التحويل فوراً بدون طابور (للتجربة):

```bash
php artisan precedents:convert-epub-to-pdf --sync
php artisan precedents:convert-epub-to-pdf --id=5 --sync
```

## إذا لم يُثبت Calibre

- الاجتهاد يبقى من نوع **EPUB** ويُعرض في التطبيق عبر ويب فيو (كما هو حالياً).
- لن يُحذف الملف ولن يتغيّر نوع الاجتهاد.

## السجلات

- عند النجاح أو الفشل يُسجّل في `storage/logs/laravel.log`.
- عند فشل التحويل يُترك الاجتهاد على حاله (EPUB).
