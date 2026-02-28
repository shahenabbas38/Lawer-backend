# متغيرات البيئة المطلوبة على Railway

على Railway لا ترفع ملف `.env` — اضبط القيم من **لوحة المشروع → Service → Variables**.

## المتغيرات الأساسية (ضرورية)

| المتغير    | القيمة | ملاحظة |
|-----------|--------|--------|
| `APP_KEY` | `base64:...` | ولّده محلياً: `php artisan key:generate` ثم انسخ من `.env` |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | |
| `APP_URL` | `https://اسم-مشروعك.up.railway.app` | الرابط الذي يعطيك إياه Railway بعد النشر (بدون / في الآخر) |

## قاعدة البيانات

- **MySQL:** اضبط المتغيرات التالية في Variables (القيم من مزوّد MySQL لديك، مثلاً إضافة قاعدة بيانات أو استضافة خارجية):
  - `DB_CONNECTION=mysql`
  - `DB_HOST=...` (عنوان السيرفر)
  - `DB_PORT=3306`
  - `DB_DATABASE=...`
  - `DB_USERNAME=...`
  - `DB_PASSWORD=...`
- **SQLite (افتراضي):** اترك أو ضع `DB_CONNECTION=sqlite`. بدون Volume قد تُفقد البيانات عند إعادة النشر؛ أضف Volume أو استخدم MySQL/PostgreSQL.
- **PostgreSQL:** أضف **PostgreSQL** من Railway ثم استخدم `DATABASE_URL` أو عرّف `DB_CONNECTION=pgsql` مع `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

## تخزين ملفات الاجتهادات

- `FILESYSTEM_DISK=public` حتى تُخدم الملفات من رابط التطبيق.
- أضف **Volume** واربطه بمجلد التخزين (مثلاً Mount Path: `/app/storage`) حتى لا تضيع الملفات بعد إعادة النشر — انظر `حفظ_الملفات_على_Railway.md`.

## البورت

Railway يضبط `PORT` تلقائياً. الـ Dockerfile يستخدمه في أمر التشغيل؛ لا حاجة لتعريفه يدوياً.

## تحويل EPUB → PDF

الأمر `ebook-convert` (Calibre) مثبت في الـ Dockerfile. لا حاجة لتعريف `CALIBRE_EBOOK_CONVERT` إلا إذا أردت مساراً مختلفاً.

---

**الخلاصة:** عرّف على الأقل `APP_KEY` و `APP_URL` و `APP_ENV=production` و `APP_DEBUG=false`، ثم قاعدة البيانات والتخزين حسب اختيارك (SQLite + Volume أو PostgreSQL، و Volume لـ storage).
