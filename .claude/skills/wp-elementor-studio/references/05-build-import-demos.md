# 05 — ساخت خودکار سایت، دموهای یک‌کلیکی، تم رنگی، هدر/فوتر

## ایده: «ایمپورت» = ساختن JSON المنتور با کد
به‌جای فایل XML یا Template Kit، صفحه‌ها در PHP به شکل آرایهٔ عناصر المنتور ساخته و در
`_elementor_data` ذخیره می‌شوند. نتیجه در ویرایشگر کاملاً عادی و قابل ویرایش است، به نسخهٔ افزونه
گره می‌خورد، به سرور بیرونی نیاز ندارد، و می‌شود هر بخش را جدا جایگزین کرد.

## سازنده‌ها (builder.php)
```php
function pfx_uid() { return substr( md5( uniqid( '', true ) . wp_rand() ), 0, 7 ); }
function pfx_c( $settings = [], $children = [], $inner = false ) { // کانتینر flexbox
  return [ 'id' => pfx_uid(), 'elType' => 'container', 'isInner' => $inner, 'settings' => $settings, 'elements' => $children ]; }
function pfx_w( $type, $settings = [] ) {                           // ویجت
  return [ 'id' => pfx_uid(), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => [] ]; }
function pfx_px( $s, $u = 'px' ) { return [ 'unit' => $u, 'size' => $s, 'sizes' => [] ]; }
function pfx_box( $t, $r = null, $b = null, $l = null, $u = 'px' ) { /* dimensions با isLinked */ }
function pfx_g( $role ) { return 'globals/colors?id=' . $role; }     // اتصال به رنگ سراسری کیت
// ترکیبی‌ها: pfx_band() (بخش تمام‌عرض با padding ریسپانسیو)، pfx_row()، pfx_col()، pfx_heading()، pfx_text()، pfx_button()

function pfx_save_layout( $post_id, array $els, $type = 'wp-page' ) {
  update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
  update_post_meta( $post_id, '_elementor_template_type', $type );
  update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
  update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( array_values( $els ) ) ) ); // wp_slash ضروری
  if ( 'wp-page' === $type ) update_post_meta( $post_id, '_wp_page_template', 'elementor_header_footer' );
  pfx_flush_layout_cache( $post_id );
}
function pfx_flush_layout_cache( $id ) {
  delete_post_meta( $id, '_elementor_css' ); delete_post_meta( $id, '_elementor_element_cache' ); delete_post_meta( $id, '_elementor_page_assets' );
  \Elementor\Core\Files\CSS\Post::create( $id )->delete();
}
function pfx_walk_layout( array &$els, callable $fn ) { /* بازگشتی، $fn( &$el ) ⇒ true اگر تغییر داد */ }
```
نکته‌ها: تنظیمات ریسپانسیو با پسوند `_tablet` / `_mobile` (مثلاً `padding_mobile`). برای جایگزینی
یک بخش، کانتینر بالایی `_element_id` (CSS ID) دارد ⇒ `pfx_find_section()`.

## ساخت سایت در یک کلیک (setup.php)
ترتیب:
1. پیوند یکتا `/%postname%/`، `elementor_experiment-container=active`، غیرفعال کردن color/typography
   schemes، پشتیبانی CPT در المنتور، تنظیمات پیش‌فرض افزونه (`add_option` فقط اگر نبود).
2. **کیت المنتور:** رنگ‌ها و فونت‌های سراسری، عرض محتوا، breakpoints در `_elementor_page_settings`
   پست کیت فعال (`elementor_active_kit`).
3. آیتم‌های نمونه (اختیاری) با تصاویر داخل بسته ⇒ کتابخانهٔ رسانه (`media_handle_sideload` از کپی محلی)،
   یک بار (با شناسهٔ پیگیری در meta تا تکرار نشود).
4. صفحه‌ها (خانه، آرشیو، درباره، تماس، ...) + `show_on_front`/`page_on_front` + منو.
5. «قطعه‌ها»: هدر، فوتر، قالب تک‌آیتم، آرشیو — به‌شکل پست‌های `elementor_library` که parts.php چاپ می‌کند.
6. آیکن سایت، `brand_built = time()`، گزارش به زبان کاربر.

- **سایت تازه** (`!brand_built` و صفحه‌های کم و خانهٔ وبلاگی) ⇒ در اولین `admin_init` خودکار ساخته
  شود (با transient قفل). **سایت دارای محتوا** ⇒ هیچ کاری نکن تا کاربر دکمه را بزند.
- گزینهٔ «ساخت دوباره (force)» فقط با پشتیبان از هر چیدمان.

## دموها (demos.php)
- برای هر **ناحیه** (هدر، هیرو، آرشیو، تک‌آیتم، درباره، سؤالات، فوتر، ...) چند دمو. هر دمو یک تابع
  که عناصر می‌سازد (`pfx_demo_hero($v)`).
- صفحهٔ مدیریت «دموها»: کارت پیش‌نمایش (اسکرین‌شات) + «اعمال» + «بازگردانی».
- `pfx_apply_demo($area,$n)`: مقصد (صفحه + CSS ID بخش یا کل قالب) ⇒ پشتیبان ⇒ جایگزینی فقط آن بخش
  ⇒ ذخیره ⇒ ثبت `{n, hash(md5 داده), time}` ⇒ `files_manager->clear_cache()`.
- `pfx_demo_edited($area)`: hash فعلی ≠ hash ثبت‌شده ⇒ «این بخش را دستی ویرایش کرده‌اید؛ اعمال دمو
  آن را جایگزین می‌کند (نسخهٔ فعلی پشتیبان گرفته می‌شود)» — هشدار قبل از اعمال.
- `pfx_undo_demo()` آخرین پشتیبان (تا ۵) را برمی‌گرداند.
- دموها همان ویجت‌ها با تنظیمات متفاوت‌اند ⇒ هر چه در دمو هست در ویرایشگر قابل تغییر است.

## تم‌های رنگی (palettes.php)
- پالت‌های برند × خانواده‌ها (بر اساس رنگ پس‌زمینه) × ترکیب نقش‌ها (لهجهٔ رنگی × سبک دکمه × سطوح)
  ⇒ ده‌ها تم، همه فقط از رنگ‌های خود پالت و ترکیب‌هایشان (`pfx_mix()`).
- اعمال تم **فقط رنگ‌های سراسری کیت** را عوض می‌کند (system_colors/custom_colors با شناسه‌های نقش
  ثابت: bg, ink, muted, accent, band, ...). عناصری که با `globals/colors?id=` ساخته شده‌اند تغییر
  می‌کنند؛ رنگی که کاربر دستی روی یک عنصر گذاشته دست نمی‌خورد.
- پیش‌نمایش مینیاتوری هر تم در مدیریت، و نام فارسی هر رنگ.

## هدر/فوتر/قالب‌ها با المنتور رایگان (parts.php)
```php
function pfx_render_part( $slot ) {
  $pro = in_array( $slot, [ 'header', 'footer', 'archive' ], true ) ? $slot : 'single';
  if ( pfx_pro_covers( $pro ) ) return (bool) elementor_theme_do_location( $pro ); // پرو شرط دارد ⇒ پرو
  $id = pfx_part_id( $slot );                       // پست elementor_library انتخاب‌شده در تنظیمات
  $html = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id, true );
  // محافظ بازگشت بی‌نهایت (static $busy)، و اگر خالی بود false ⇒ فال‌بک قالب
}
```
قالب تک‌آیتم: ویجت‌های «فیلد» (عنوان، گالری، مشخصات، مسیر breadcrumb، توضیح، ناوبری قبلی/بعدی،
آیتم‌های مرتبط) که از `get_the_ID()` می‌خوانند ⇒ یک قالب برای همهٔ آیتم‌ها.
