# تحويل EPUB إلى PDF تلقائياً

عند رفع اجتهاد من نوع **EPUB** من لوحة الأدمن، يُحوّل تلقائياً إلى **PDF** في الخلفية (إن كان Calibre مثبتاً)، فيصبح العرض في التطبيق عبر عارض الـ PDF الموحّد.

## متطلبات السيرفر

- تثبيت **Calibre** (يحتوي على الأمر `ebook-convert`):
  - **ويندوز:** [تحميل Calibre](https://calibre-ebook.com/download) ثم إضافة المسار إلى PATH أو تعيينه في `.env`
  - **أوبونتو/دبيان:** `sudo apt install calibre`
  - **ماك:** `brew install calibre`

## الإعداد

في ملف `.env` (اختياري):

```env
# إذا ebook-convert غير في PATH، حدد المسار الكامل:
# ويندوز مثال:
CALIBRE_EBOOK_CONVERT="C:\Program Files\calibre2\ebook-convert.exe"
# لينكس/ماك عادة يكتفي بالأمر بدون مسار:
# CALIBRE_EBOOK_CONVERT=ebook-convert
```

## التفعيل التلقائي

- عند رفع ملف **EPUB** من الأدمن، يُرسل **Job** إلى الطابور لتحويله إلى PDF.
- بعد انتهاء التحويل يُحدَّث سجل الاجتهاد (مسار الملف ونوعه إلى PDF) ويُحذف ملف الـ EPUB.

تأكد من تشغيل الطابور:

```bash
php artisan queue:work
```

أو استخدم **Supervisor** أو **queue worker** على السيرفر.

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
