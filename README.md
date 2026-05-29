# Cafe POS - Laravel + MySQL

نظام POS احترافي لمقهى مبني بـ Laravel، مناسب للعمل على استضافة تدعم PHP/MySQL مثل Hostinger.

## المميزات

- تسجيل دخول باسم مستخدم وكلمة مرور مع تشفير تلقائي لكلمات المرور.
- صلاحيات: `admin` و `cashier`.
- إدارة الطاولات كـ Grid مع أقسام Indoor / Outdoor / VIP.
- حالات الطاولة بالألوان: free أخضر، busy أحمر، reserved أصفر، billing أزرق.
- فتح جلسة لكل طاولة مع رقم فاتورة تلقائي وتخزين الموظف ووقت الفتح/الإغلاق والمجاميع.
- إضافة/تعديل/حذف الطلبات داخل الجلسة.
- إدارة المنيو حسب التصنيف: shisha / food / drink.
- خصم و tip وإغلاق الجلسة مع إعادة الطاولة إلى free.
- تقرير يومي: إجمالي المبيعات، عدد الفواتير، أكثر الأصناف مبيعاً، وسجل العمليات.
- تحديثات مباشرة خفيفة باستخدام polling للطاولات والطلبات، مناسبة للاستضافات المشتركة.
- فاتورة قابلة للطباعة من المتصفح.

## التشغيل المحلي

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

ثم افتح: `http://127.0.0.1:8000`

## حسابات تجريبية بعد seed

- Admin: `admin` / `admin123`
- Cashier: `cashier` / `cashier123`

## إعداد MySQL على Hostinger

حدّث ملف `.env` بالقيم الخاصة بقاعدة البيانات:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

ثم شغل migrations والبيانات الأولية من SSH أو Terminal المتاح في الاستضافة:

```bash
php artisan migrate --seed --force
php artisan optimize
```

اجعل Document Root يشير إلى مجلد `public`.
