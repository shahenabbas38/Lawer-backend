# Postman — تجربة API الباك اند

1. افتح Postman واختر **Import**.
2. اختر الملف **Lex Mate API.postman_collection.json** من هذا المجلد.
3. بعد الاستيراد افتح الـ Collection **Lex Mate API** → تبويب **Variables**.
4. غيّر **base_url** إلى رابط الباك اند:
   - لوكال: `http://localhost:8000` أو `http://127.0.0.1:8000`
   - استضافة: `https://api.yourdomain.com` (بدون / في الآخر).
5. احفظ ثم جرّب الطلبات (قائمة الاجتهادات، تفاصيل ملف، إلخ).
6. لطلب "تفاصيل اجتهاد" أو "حذف اجتهاد" غيّر **precedent_id** إلى رقم اجتهاد موجود.
