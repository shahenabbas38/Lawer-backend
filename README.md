# باك اند تطبيق المحامي (Laravel)

مكان هذا الفولدر **مؤقت** — يمكن نقله لاحقاً.

---

## أوامر أساسية

```bash
php artisan config:clear
php artisan migrate
php artisan serve
```

بعد `php artisan serve` الرابط: **http://127.0.0.1:8000**

---

## الـ Route (المسار)

**الملف:** `routes/api.php`

| المسار الكامل | Method | الوظيفة |
|---------------|--------|---------|
| `/api/verify-payment` | POST | تحقق من الدفع وإنشاء/تحديث المستخدم |

```php
// شرح: أي طلب POST على /api/verify-payment يوجّه إلى الدالة verify في VerifyPaymentController
Route::post('/verify-payment', [VerifyPaymentController::class, 'verify']);
```

- **البادئة:** Laravel يضيف تلقائياً `/api` لجميع المسارات في `routes/api.php` (انظر `bootstrap/app.php`).
- **النتيجة:** الرابط النهائي للتطبيق هو `POST http://عنوان-السيرفر/api/verify-payment`.

---

## الـ Controller (التحكم)

**الملف:** `app/Http/Controllers/Api/VerifyPaymentController.php`

### الدالة الرئيسية: `verify(Request $request)`

| الخطوة | الشرح |
|--------|--------|
| 1. التحقق من المدخلات | يتأكد من وجود `name`, `operation_number`, `device_id` (جميعها مطلوبة). |
| 2. التحقق من الدفع | يستدعي `validatePaymentWithProvider(operation_number)` — حالياً يرجع `true` (تحقق وهمي)، لاحقاً نربطه مع API شام كاش. |
| 3. رفض إذا رقم العملية مربوط بجهاز آخر | إذا وُجد مستخدم آخر لديه نفس `operation_number` لكن `device_id` مختلف → يرد خطأ: «هذا الحساب مربوط بجهاز — لا يمكن استخدامه على أكثر من جهاز.» |
| 4. إنشاء أو تحديث المستخدم | إذا نفس الجهاز (`device_id` موجود) → تحديث الاسم ورقم العملية والاشتراك. إذا جهاز جديد → إنشاء مستخدم جديد مع الاسم و `device_id` و `operation_number` و `is_subscribed = true`. |
| 5. الرد | يرجع JSON: `success`, `message`, وبيانات `user` (id, name, device_id, is_subscribed). |

### الدالة المساعدة: `validatePaymentWithProvider(string $operationNumber): bool`

- **الوظيفة:** التحقق من صحة رقم العملية عند مزود الدفع (شام كاش).
- **حالياً:** ترجع `true` دائماً (للتجربة).
- **لاحقاً:** استدعاء HTTP لـ API شام كاش مع `operation_number` و API key من `.env`.

---

## مثال طلب من التطبيق (Body JSON)

```json
{
  "name": "أحمد",
  "operation_number": "12345",
  "device_id": "device_unique_123"
}
```

---

## مثال رد ناجح (200)

```json
{
  "success": true,
  "message": "تم التحقق من الدفع وتفعيل الاشتراك.",
  "user": {
    "id": 1,
    "name": "أحمد",
    "device_id": "device_unique_123",
    "is_subscribed": true
  }
}
```

## مثال رد خطأ — رقم العملية على جهاز آخر (422)

```json
{
  "success": false,
  "message": "هذا الحساب مربوط بجهاز — لا يمكن استخدامه على أكثر من جهاز."
}
```
