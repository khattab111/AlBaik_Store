<?php

namespace Database\Seeders;

use App\Models\NewsletterTemplate;
use Illuminate\Database\Seeder;

class NewsletterTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Welcome Template',
                'category' => NewsletterTemplate::CATEGORY_WELCOME,
                'subject_ar' => 'مرحباً بك في {{store_name}}',
                'subject_en' => 'Welcome to {{store_name}}',
                'preheader_ar' => 'سعيدون بانضمامك إلى نشرتنا البريدية.',
                'preheader_en' => 'We are glad to have you in our newsletter.',
                'content_ar' => '<h2>مرحباً {{subscriber_name}}</h2><p>شكراً لاشتراكك في نشرة {{store_name}}. ستصلك أحدث العروض والمنتجات الجديدة مباشرة إلى بريدك.</p><p><a href="'.url('/products').'" style="background:#f59e0b;color:#111827;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">تصفح المنتجات</a></p>',
                'content_en' => '<h2>Hello {{subscriber_name}}</h2><p>Thank you for subscribing to {{store_name}}. You will receive fresh offers and new products directly in your inbox.</p><p><a href="'.url('/products').'" style="background:#f59e0b;color:#111827;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">Browse products</a></p>',
                'is_default' => true,
            ],
            [
                'name' => 'Weekly Offers Template',
                'category' => NewsletterTemplate::CATEGORY_OFFERS,
                'subject_ar' => 'عروض الأسبوع من {{store_name}}',
                'subject_en' => 'Weekly offers from {{store_name}}',
                'preheader_ar' => 'أفضل العروض المختارة لهذا الأسبوع.',
                'preheader_en' => 'Selected deals for this week.',
                'content_ar' => '<h2>عروض لا تفوّت</h2><p>اخترنا لك أفضل عروض الأسبوع على الهواتف والإكسسوارات.</p><p><a href="'.url('/offers').'" style="background:#f59e0b;color:#111827;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">مشاهدة العروض</a></p>',
                'content_en' => '<h2>Deals worth checking</h2><p>We selected this week’s best offers on phones and accessories.</p><p><a href="'.url('/offers').'" style="background:#f59e0b;color:#111827;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">View offers</a></p>',
            ],
            [
                'name' => 'New Arrivals Template',
                'category' => NewsletterTemplate::CATEGORY_NEW_PRODUCTS,
                'subject_ar' => 'وصل حديثاً إلى {{store_name}}',
                'subject_en' => 'New arrivals at {{store_name}}',
                'preheader_ar' => 'منتجات جديدة بانتظارك.',
                'preheader_en' => 'Fresh products are waiting for you.',
                'content_ar' => '<h2>وصل حديثاً</h2><p>اكتشف أحدث الهواتف والإكسسوارات وقطع الإلكترونيات المتوفرة الآن.</p><p><a href="'.url('/latest-products').'" style="background:#f59e0b;color:#111827;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">تصفح الجديد</a></p>',
                'content_en' => '<h2>New arrivals</h2><p>Discover the latest phones, accessories, and electronics now available.</p><p><a href="'.url('/latest-products').'" style="background:#f59e0b;color:#111827;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">Browse new arrivals</a></p>',
            ],
            [
                'name' => 'Flash Sale Template',
                'category' => NewsletterTemplate::CATEGORY_OFFERS,
                'subject_ar' => 'عرض فلاش محدود من {{store_name}}',
                'subject_en' => 'Limited flash sale from {{store_name}}',
                'preheader_ar' => 'العرض متاح لفترة قصيرة فقط.',
                'preheader_en' => 'Available for a short time only.',
                'content_ar' => '<h2>عرض فلاش</h2><p>استفد من الخصومات المحدودة قبل انتهاء الكمية أو الوقت.</p><p><a href="'.url('/offers').'" style="background:#ef4444;color:#ffffff;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">الحق العرض</a></p>',
                'content_en' => '<h2>Flash sale</h2><p>Catch limited discounts before the quantity or time runs out.</p><p><a href="'.url('/offers').'" style="background:#ef4444;color:#ffffff;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">Shop the deal</a></p>',
            ],
            [
                'name' => 'Wholesale Invitation Template',
                'category' => NewsletterTemplate::CATEGORY_ANNOUNCEMENT,
                'subject_ar' => 'انضم كتاجر جملة إلى {{store_name}}',
                'subject_en' => 'Join {{store_name}} as a wholesale partner',
                'preheader_ar' => 'أسعار وشرائح مخصصة لتجار الجملة.',
                'preheader_en' => 'Dedicated tiers and prices for wholesale partners.',
                'content_ar' => '<h2>هل تبيع الهواتف أو الإكسسوارات؟</h2><p>قدّم طلب الانضمام كتاجر جملة واحصل على شرائح أسعار مناسبة للكميات.</p><p><a href="'.url('/join-us').'" style="background:#111827;color:#ffffff;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">طلب الانضمام</a></p>',
                'content_en' => '<h2>Do you sell phones or accessories?</h2><p>Apply as a wholesale partner and access quantity-based price tiers.</p><p><a href="'.url('/join-us').'" style="background:#111827;color:#ffffff;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">Apply now</a></p>',
            ],
            [
                'name' => 'Order Follow-up Template',
                'category' => NewsletterTemplate::CATEGORY_ANNOUNCEMENT,
                'subject_ar' => 'هل تحتاج مساعدة بعد طلبك؟',
                'subject_en' => 'Need help after your order?',
                'preheader_ar' => 'فريق الدعم جاهز لمساعدتك.',
                'preheader_en' => 'Our support team is ready to help.',
                'content_ar' => '<h2>نحن هنا للمساعدة</h2><p>إذا كان لديك سؤال عن طلب أو شحنة، تواصل معنا وسنساعدك بأسرع وقت.</p><p><a href="'.url('/contact').'" style="background:#f59e0b;color:#111827;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">تواصل معنا</a></p>',
                'content_en' => '<h2>We are here to help</h2><p>If you have a question about an order or shipment, contact us and we will help quickly.</p><p><a href="'.url('/contact').'" style="background:#f59e0b;color:#111827;padding:12px 18px;border-radius:10px;text-decoration:none;font-weight:bold;">Contact us</a></p>',
            ],
        ];

        foreach ($templates as $template) {
            NewsletterTemplate::updateOrCreate(
                ['name' => $template['name']],
                array_merge([
                    'status' => NewsletterTemplate::STATUS_ACTIVE,
                    'design' => [
                        'primary_color' => '#f59e0b',
                    ],
                ], $template),
            );
        }
    }
}
