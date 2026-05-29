# بيت جدي - Laravel + MySQL

نظام POS احترافي لمقهى مبني بـ Laravel، مناسب للعمل على استضافة تدعم PHP/MySQL مثل Hostinger.

## المميزات

- تسجيل دخول بالبريد الإلكتروني أو اسم المستخدم مع تشفير تلقائي لكلمات المرور.
- صلاحيات: مدير وكاشير.
- إدارة الطاولات كشبكة مع أقسام داخلي / خارجي / كبار الشخصيات.
- حالات الطاولة بالألوان: متاحة أخضر، مشغولة أحمر، محجوزة أصفر، قيد الحساب أزرق.
- فتح جلسة لكل طاولة مع رقم فاتورة تلقائي وتخزين الموظف ووقت الفتح/الإغلاق والمجاميع.
- إضافة/تعديل/حذف الطلبات داخل الجلسة.
- إدارة الأصناف حسب التصنيف: شيشة / أكل / مشروبات.
- خصم وإكرامية اختيارية وإغلاق الجلسة مع إعادة الطاولة إلى متاحة.
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

- المدير: `admin@example.com` أو `admin` / كلمة المرور: `admin123`
- الكاشير: `cashier@example.com` أو `cashier` / كلمة المرور: `cashier123`

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
php artisan migrate --force
php artisan db:seed --force
php artisan optimize:clear
php artisan optimize
```

> مهم جداً: لا تستخدم `php artisan migrate:fresh` أو `php artisan migrate:fresh --seed` على السيرفر الحقيقي، لأن هذا الأمر يحذف كل الجداول والمستخدمين والفواتير ثم يعيد إنشاءها.

اجعل Document Root يشير إلى مجلد `public`.

## تحديث المشروع على Hostinger عبر SSH

```bash
cd /path/to/project
git pull origin cursor/laravel-cafe-pos-8336
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize
```

إذا كان السيرفر لا يحتوي Git، ارفع الملفات الجديدة من جهازك ثم شغل أوامر Composer و Artisan السابقة.
