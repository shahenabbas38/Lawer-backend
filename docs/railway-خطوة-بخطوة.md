# نشر الباك اند على Railway — خطوة بخطوة بالترتيب

## المرحلة 1: التحضير على جهازك (قبل الرفع)

### الخطوة 1 — توليد APP_KEY
1. افتح مجلد الباك اند `lawyer_backend` في الطرفية.
2. نفّذ:
   ```bash
   php artisan key:generate
   ```
3. افتح ملف `.env` وانسخ قيمة `APP_KEY` (مثل `base64:xxxxxxxx...`). ستحتاجها في Railway.

### الخطوة 2 — التأكد من المشروع جاهز للنشر
- تأكد أن لديك `Dockerfile` في جذر المشروع (موجود).
- تأكد أن التعديلات محفوظة ومرسلة إلى Git إذا كنت تربط Railway بـ GitHub.

---

## المرحلة 2: إنشاء المشروع على Railway

### الخطوة 3 — الدخول إلى Railway وربط المشروع
1. ادخل إلى [railway.app](https://railway.app) وسجّل دخولك.
2. اضغط **New Project**.
3. اختر **Deploy from GitHub repo** (أو **Deploy from local** إذا ترفع يدوياً).
4. اختر المستودع `lawyer_backend` (أو ارفع المجلد).
5. Railway سيكتشف الـ **Dockerfile** ويبني الصورة تلقائياً.

### الخطوة 4 — الحصول على رابط التطبيق (Domain)
1. بعد انتهاء البناء والنشر، ادخل إلى **Service** الخاص بالباك اند.
2. من تبويب **Settings** ابحث عن **Networking** أو **Public Networking**.
3. اضغط **Generate Domain** (أو **Add Domain**) لإنشاء رابط عام.
4. انسخ الرابط (مثل `https://lawyer-backend-production.up.railway.app`) — **بدون** `/` في النهاية. هذا هو **APP_URL**.

---

## المرحلة 3: ضبط متغيرات البيئة (Variables)

### الخطوة 5 — فتح Variables
1. داخل نفس الـ **Service** في Railway.
2. ادخل إلى تبويب **Variables** (أو **Environment**).

### الخطوة 6 — إضافة المتغيرات بالترتيب

اضف المتغيرات التالية واحدة واحدة (الاسم والقيمة):

| # | اسم المتغير | القيمة |
|---|-------------|--------|
| 1 | `APP_KEY` | القيمة التي نسختها من `.env` (مثل `base64:xxx...`) |
| 2 | `APP_URL` | رابط الـ Domain الذي نسخته (مثل `https://lawer-backend-production.up.railway.app`) |
| 3 | `APP_ENV` | `production` |
| 4 | `APP_DEBUG` | `false` |
| 5 | `FILESYSTEM_DISK` | `public` |

احفظ (Save). Railway سيعيد النشر تلقائياً بعد تغيير المتغيرات.

---

## المرحلة 4: قاعدة البيانات والتخزين (اختياري لكن موصى به)

### الخطوة 7 — قاعدة البيانات

- **إذا استخدمت MySQL:**  
  في **Variables** للـ Service الباك اند أضف (بالقيم اللي يعطيك إياها مزوّد MySQL، مثلاً من Railway أو PlanetScale أو استضافة أخرى):

  | المتغير        | مثال / وصف |
  |----------------|------------|
  | `DB_CONNECTION` | `mysql` |
  | `DB_HOST`      | عنوان السيرفر (مثل `xxx.railway.internal` أو `mysql.example.com`) |
  | `DB_PORT`      | `3306` (عادة) |
  | `DB_DATABASE`  | اسم قاعدة البيانات |
  | `DB_USERNAME`  | اسم المستخدم |
  | `DB_PASSWORD`  | كلمة المرور |

  بعد الحفظ يعيد Railway النشر؛ تأكد أن الـ migrations تنفذ (الـ Dockerfile يشغّل `php artisan migrate --force`).

- **إذا استخدمت PostgreSQL:**  
  1. من المشروع في Railway: **New** → **Database** → **PostgreSQL**.  
  2. انسخ `DATABASE_URL` من Variables الخاصة بالـ Database.  
  3. في **Service** الباك اند أضف المتغير `DATABASE_URL` بنفس القيمة (أو عرّف `DB_CONNECTION=pgsql` مع `DB_HOST`, `DB_DATABASE`, إلخ).

- **إذا استخدمت SQLite:**  
  لا تحتاج إعداد إضافي؛ التطبيق يستخدم SQLite. انتبه: بدون Volume قد تُفقد البيانات عند إعادة النشر (انظر الخطوة 8 لربط Volume للتخزين).

### الخطوة 8 — تخزين ملفات الاجتهادات (Volume)
حتى لا تضيع الملفات المرفوعة بعد إعادة النشر:
1. في **Service** الباك اند ادخل **Settings** أو **Volumes**.
2. اضغط **Add Volume** (أو **Mount Volume**).
3. حدد **Mount Path** مثل: `/app/storage`
4. احفظ واعمل **Redeploy** للـ Service.

(تفاصيل أكثر في الملف `حفظ_الملفات_على_Railway.md`.)

---

## المرحلة 5: ربط التطبيق (Flutter) بالباك اند

### الخطوة 9 — تحديث رابط الباك في التطبيق
1. افتح مشروع Flutter `lawyer_app`.
2. افتح الملف:  
   `lib/core/config/api_base_url_io.dart`
3. غيّر قيمة `kProductionApiUrl` لتطابق رابط Railway بالضبط، مثلاً:
   ```dart
   const String? kProductionApiUrl = 'https://lawer-backend-production.up.railway.app';
   ```
   (استبدل بالرابط الذي نسخته من Railway.)
4. احفظ الملف.

---

## المرحلة 6: التأكد أن كل شيء يعمل

### الخطوة 10 — فحص الرابط من المتصفح
- افتح في المتصفح: `https://رابط-مشروعك.up.railway.app`
- إذا ظهرت صفحة Laravel أو رسالة من السيرفر (حتى لو 404 لصفحة الجذر) فالرابط يعمل.

### الخطوة 11 — تجربة الـ API
- جرّب من التطبيق أو من Postman:
  - `GET https://رابط-مشروعك.up.railway.app/api/precedents`
- يجب أن يرجع JSON (قائمة فارغة أو فيها اجتهادات).

### الخطوة 12 — تشغيل التطبيق على الموبايل
- شغّل التطبيق Flutter على جهاز أو إيموليتر.
- ادخل إلى قسم الاجتهادات وتأكد أن القائمة تُحمّل من الباك (أو تظهر "قريباً" إذا لا توجد بيانات).
- إذا رفعت اجتهاداً من لوحة الأدمن، تأكد أن المستخدم يفتح الملف ويشاهده بشكل صحيح.

---

## ملخص الترتيب

| الترتيب | ماذا تفعل |
|--------|------------|
| 1 | توليد `APP_KEY` محلياً ونسخه |
| 2 | التأكد من وجود Dockerfile وربط Git إن لزم |
| 3 | إنشاء مشروع على Railway وربط المستودع |
| 4 | Generate Domain ونسخ الرابط (APP_URL) |
| 5 | إضافة Variables: APP_KEY, APP_URL, APP_ENV, APP_DEBUG, FILESYSTEM_DISK |
| 6 | (اختياري) إضافة PostgreSQL و/أو Volume للتخزين |
| 7 | تحديث `kProductionApiUrl` في تطبيق Flutter |
| 8 | فتح الرابط من المتصفح وتجربة API ثم التطبيق |

إذا واجهت خطأ في خطوة معينة، راجع `railway-env-variables.md` و`حفظ_الملفات_على_Railway.md`.
