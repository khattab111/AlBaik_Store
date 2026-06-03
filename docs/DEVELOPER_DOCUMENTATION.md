# دليل المطور لمتجر البيك

هذا الدليل مخصص للمبرمج والمطور الذي سيكمل العمل على مشروع Laravel + Filament + Livewire. يشرح بنية المشروع، آلية عمل الأنظمة الأساسية، ونقاط الحذر عند تعديل الحسابات الحساسة.

آخر تحديث: 2026-06-02

## التقنيات

- Laravel.
- Filament لإدارة لوحة التحكم.
- Blade وVite لواجهة المتجر.
- Livewire مستخدم ضمن Filament.
- MySQL كقاعدة بيانات رئيسية.
- Storage public للصور والملفات العامة.

## هيكل مهم

```text
app/Models                 Eloquent models والعلاقات
app/Services               منطق الأسعار، الشحن، العروض، الكاش
app/Actions/Orders         إنشاء الطلب من السلة
app/Http/Controllers       Controllers للمتجر والـ API
app/Http/Requests          Validation لطلبات الواجهة والـ API
app/Filament               موارد لوحة الإدارة
database/migrations        الجداول
database/seeders           بيانات التشغيل والتجربة
resources/views            Blade views للمتجر والإدارة
resources/css/app.css      تصميم الواجهة
routes/web.php             Web routes
routes/api.php             API routes
docs                       التوثيق
```

## قواعد تطوير عامة

- لا تعتمد على السعر القادم من الواجهة.
- لا تعتمد على تكلفة الشحن القادمة من الواجهة.
- لا تعتمد على خصومات JavaScript.
- كل إنشاء طلب يجب أن يعيد الحساب من Backend.
- أي تعديل على `settings`, `banners`, `products`, `categories`, `brands`, `offers` يجب أن يراعي تفريغ cache.
- عند تعديل صور Filament استخدم `disk('public')`.

## التخزين والصور

الملفات العامة تحفظ في:

```text
storage/app/public
```

وتعرض من:

```text
/storage/...
```

إعداد disk العام:

```php
'url' => env('PUBLIC_STORAGE_URL', '/storage')
```

هذا مهم لأن Filament قد يولد روابط الصور من Storage facade. الرابط النسبي يمنع مشكلة اختلاف `localhost` و`127.0.0.1` أو اختلاف المنفذ.

موارد Filament التي ترفع أو تعرض صوراً يجب أن تستخدم:

```php
->disk('public')
->visibility('public')
```

مثال:

```php
Forms\Components\FileUpload::make('path')
    ->image()
    ->disk('public')
    ->directory('products')
    ->visibility('public');
```

## الموديلات والعلاقات

النظام يعتمد على Eloquent relations بين:

- `Product` و`ProductImage`.
- `Product` و`ProductVariant`.
- `Product` و`Brand`.
- `Product` و`Category`.
- `Cart` و`CartItem`.
- `CartItem` و`Product` أو `FlashOffer`.
- `FlashOffer` و`FlashOfferItem`.
- `Order` و`OrderItem`.
- `Order` و`Payment`.
- `City` و`ShippingRate`.
- `ShippingCarrier` و`ShippingRate`.
- `User` و`UserAddress`.

عند إضافة عمود جديد:

1. أضفه في migration.
2. أضفه في model fillable إذا كان يستخدم mass assignment.
3. أضف casts إن كان JSON أو boolean أو date.
4. راجع seeders.
5. راجع Filament Resource.
6. راجع الـ validation.

## السلة

ملفات مهمة:

```text
app/Http/Controllers/CartController.php
app/Repositories/CartRepository.php
app/Services/GuestCartService.php
app/Services/OfferCartService.php
app/Services/FlashOfferService.php
```

أنواع عناصر السلة:

- `product`: منتج عادي.
- `offer`: عرض كامل.

قاعدة مهمة: العرض لا يفكك إلى منتجات مستقلة في السلة. يحفظ كعنصر واحد ويحمل `components_snapshot`.

عند تطوير السلة:

- لا تفترض أن كل `CartItem` يحتوي `product_id`.
- افحص `item_type` قبل الوصول إلى `product`.
- تعديل كمية العرض يجب أن يكون على العرض نفسه.
- حذف مكونات داخلية من العرض ممنوع منطقياً.

## العروض

ملفات مهمة:

```text
app/Models/FlashOffer.php
app/Models/FlashOfferItem.php
app/Services/FlashOfferService.php
app/Services/OfferCartService.php
app/Presenters/FlashOfferPresenter.php
app/Http/Controllers/OfferController.php
```

أنواع العروض:

- `percentage_discount`
- `fixed_amount_discount`
- `fixed_price_quantity`
- `bundle_fixed_price`
- `buy_x_get_y`
- `free_shipping_product`
- `cart_free_shipping`

قواعد التحقق:

- `status = active`.
- `starts_at` ليس في المستقبل.
- `ends_at` ليس في الماضي.
- `sold_quantity < max_quantity` إذا كان الحد موجوداً.
- توفر مخزون المنتجات الداخلية للحزم وBuy X Get Y.
- خصم مخزون المنتجات المجانية أيضاً.

عند شراء عرض:

- ينشأ `OrderItem` من نوع `offer`.
- يحفظ Snapshot لمكونات العرض.
- تخصم مخزونات المنتجات الداخلية بعد نجاح الطلب.

## الشحن

ملفات مهمة:

```text
app/Services/ShippingService.php
app/Models/City.php
app/Models/ShippingCarrier.php
app/Models/ShippingRate.php
app/Filament/Resources/CityResource.php
app/Filament/Resources/ShippingCarrierResource.php
app/Filament/Resources/ShippingRateResource.php
```

آلية الشحن:

1. المدينة تأتي من جدول `cities`.
2. الشركات المتاحة تأتي من `shipping_rates` للمدينة.
3. Backend يتحقق أن الشركة تغطي المدينة.
4. يحسب الوزن القابل للفوترة.
5. يستثني المنتجات التي لا تتطلب شحناً.
6. يستثني المنتجات ذات الشحن المجاني من الوزن القابل للفوترة.
7. يميز بين شحن مجاني لمنتج وشحن مجاني لكامل السلة.
8. يحفظ Snapshot للشحن داخل الطلب.

معادلة السعر:

```text
base_cost + (chargeable_weight * cost_per_kg) + remote_area_fee
```

## الطلبات

الملف المركزي:

```text
app/Actions/Orders/CreateOrderFromCart.php
```

يجب أن يبقى إنشاء الطلب داخل:

```php
DB::transaction(...)
```

خطواته الأساسية:

1. تحميل السلة وعناصرها.
2. التحقق من وجود عناصر.
3. التحقق من طريقة الدفع.
4. حل العنوان: محفوظ أو جديد.
5. التحقق من مخزون المنتجات والعروض.
6. إعادة حساب الأسعار.
7. حساب الكوبون.
8. حساب الشحن.
9. إنشاء الطلب.
10. إنشاء عناصر الطلب.
11. إنشاء الدفع.
12. خصم المخزون.
13. حذف عناصر السلة.
14. إطلاق `OrderPlaced`.

نقطة حذر: إذا أضيف دفع إلكتروني لاحقاً، يجب عدم خصم المخزون نهائياً إلا بعد نجاح الدفع أو اعتماد آلية reservation واضحة.

## العناوين

ملفات مهمة:

```text
app/Models/UserAddress.php
app/Http/Requests/Storefront/AddressRequest.php
app/Http/Requests/Storefront/CheckoutRequest.php
app/Http/Controllers/Storefront/Account/AddressController.php
```

قواعد مهمة:

- العنوان المحفوظ يجب أن يعود للمستخدم الحالي.
- المدينة يجب أن تكون فعالة.
- العنوان الجديد يمكن حفظه اختيارياً.
- الطلب يحفظ Snapshot للعنوان حتى لو عدله العميل لاحقاً.

## التسعير

ملفات مهمة:

```text
app/Services/ProductPricingService.php
app/Data/ProductPriceData.php
```

التسعير يعتمد على:

- سعر التجزئة.
- شرائح الجملة.
- العروض النشطة.
- نوع المستخدم.
- الكمية.

أي تعديل جديد على التسعير يجب أن يغطي:

- بطاقة المنتج.
- صفحة المنتج.
- السلة.
- checkout.
- إنشاء الطلب.
- الاختبارات.

## SEO

حقول المنتج:

```text
seo_title
seo_description
```

هذه الحقول مترجمة داخل JSON، لذلك يجب التعامل معها حسب اللغة:

```php
seo_title->ar
seo_title->en
seo_description->ar
seo_description->en
```

إرشادات للمطور:

- لا تولد SEO Title تلقائياً فوق قيمة أدخلها الأدمن.
- استخدم fallback من اسم المنتج عند غياب `seo_title`.
- استخدم fallback من `short_description` عند غياب `seo_description`.
- تأكد أن Blade يضع القيم في:

```blade
@section('title', $product->seo_title ?: $product->name)
@section('meta_description', $product->seo_description ?: $product->short_description)
```

إرشادات للمحتوى:

- SEO Title يفضل أن يكون 50-60 حرفاً.
- SEO Description يفضل أن يكون 140-160 حرفاً.
- يجب أن تكون القيم مختلفة بين اللغات.
- تجنب حشو الكلمات المفتاحية.

## الواجهة

ملفات مهمة:

```text
resources/views/layouts/app.blade.php
resources/views/home.blade.php
resources/views/products/index.blade.php
resources/views/products/show.blade.php
resources/views/offers/index.blade.php
resources/views/offers/show.blade.php
resources/views/cart/index.blade.php
resources/views/checkout/index.blade.php
resources/css/app.css
resources/js/app.js
```

تحديثات حالية:

- لا يوجد شريط أسود علوي.
- التنقل يستخدم `request()->routeIs(...)`.
- البراندات تظهر قبل التصنيفات في الرئيسية.
- كرت البراند محسن ويستخدم أبعاد ثابتة للشعار.
- النصوص الطويلة تستخدم `store-safe-text`.

قواعد تصميم:

- اختبر RTL وLTR.
- اختبر الموبايل والتابلت والديسكتوب.
- لا تجعل النصوص الطويلة تكسر البطاقة.
- لا تجعل أزرار checkout مخفية على الهاتف.

## الكاش

ملفات مهمة:

```text
app/Services/StorefrontCacheService.php
app/Models/Product.php
app/Models/Brand.php
app/Models/Category.php
app/Models/FlashOffer.php
```

يجب تفريغ كاش الواجهة عند تعديل:

- settings.
- banners.
- products.
- categories.
- brands.
- offers.

أوامر مفيدة:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Filament

موارد مهمة:

```text
app/Filament/Resources/ProductResource.php
app/Filament/Resources/BrandResource.php
app/Filament/Resources/BannerResource.php
app/Filament/Resources/HeroSlideResource.php
app/Filament/Resources/PaymentMethodResource.php
app/Filament/Resources/PaymentResource.php
app/Filament/Resources/ShippingCarrierResource.php
```

قواعد:

- استخدم `disk('public')` للصور.
- استخدم `visibility('public')` للملفات العامة.
- لا تعرض مفاتيح API كنص عادي.
- الترجمة متعددة اللغات تستخدم `BuildsTranslatableForms`.

## الاختبارات

أوامر التحقق:

```bash
php artisan migrate:fresh --seed
php artisan test
php artisan route:list
npm run build
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

ملاحظة: إذا فشلت الاختبارات بسبب اتصال MySQL داخل sandbox، شغلها في بيئة تستطيع الوصول إلى قاعدة البيانات المحلية.

## Checklist قبل التسليم

- هل `php artisan test` ينجح؟
- هل `npm run build` ينجح؟
- هل checkout يعيد حساب الأسعار من Backend؟
- هل الشحن يتحقق من المدينة والشركة من Backend؟
- هل العروض تحفظ كـ offer item عند الشراء؟
- هل صور Filament تظهر من `/storage/...`؟
- هل النصوص تظهر جيداً في العربية والإنجليزية؟
- هل الكاش يفرغ عند تعديل بيانات الواجهة؟
