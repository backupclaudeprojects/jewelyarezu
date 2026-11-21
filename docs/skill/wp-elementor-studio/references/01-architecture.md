# 01 — معماری قالب + افزونه

## چرا دو بسته؟
- **قالب** فقط پوسته است: `style.css` (هدر قالب)، `functions.php` (theme supports، enqueue
  سبک، اعلان «افزونه را فعال کن»)، `header.php` / `footer.php` / `index.php` / `single-{cpt}.php` /
  `taxonomy-*.php` / `404.php` و `template-parts/*-fallback.php`.
- **افزونه** همهٔ منطق: نوع محتوا، ویجت‌ها، تنظیمات، دموها، ساخت سایت. اگر صاحب سایت روزی قالب
  را عوض کند، محتوا و ویجت‌ها از بین نمی‌روند.
- قالب هر ناحیه را این‌طور چاپ می‌کند:
  ```php
  function theme_part( $slot ) { return function_exists( 'pfx_render_part' ) ? pfx_render_part( $slot ) : false; }
  // header.php
  if ( ! theme_part( 'header' ) ) { get_template_part( 'template-parts/header-fallback' ); }
  ```

## پیشوند و نام‌گذاری
- یک پیشوند کوتاه برای همه‌چیز: توابع `pfx_`، کلاس CSS `pfx-`، متغیر CSS `--pfx-`، option ها
  `brand_*`، متا `_pfx_*`، namespace PHP `Brand\`، دستهٔ ویجت `brand`، نام ویجت `pfx-hero`.
- شناسهٔ کنترل‌های المنتور هم پیشوند/اختصاصی: `pr_pin`، `wg_pin`، `w_halo`. **هرگز** نام‌های
  عمومی مثل `sticky`، `motion_fx`، `animation`، `_position` — المنتور پرو همین شناسه‌ها را دارد و
  کنترل تو پنهان یا بازنویسی می‌شود.

## فایل اصلی افزونه
```php
/* Plugin Name: Brand Core · Version: 1.0.0 · Requires PHP: 7.4 · Text Domain: brand */
define( 'PFX_VERSION', '1.0.0' );
define( 'PFX_DIR', plugin_dir_path( __FILE__ ) );
define( 'PFX_URL', plugin_dir_url( __FILE__ ) );
require PFX_DIR . 'includes/helpers.php'; // ...بقیه
add_action( 'plugins_loaded', 'pfx_maybe_upgrade' );          // فقط چیزهای افزودنی، نگاه کن 06
add_action( 'elementor/loaded', function () { require PFX_DIR . 'includes/elementor/loader.php'; } );
```
همیشه بررسی کن المنتور فعال است (`did_action('elementor/loaded')`) و بدون آن سایت نشکند (فال‌بک قالب).

## loader المنتور
```php
add_action( 'elementor/elements/categories_registered', fn( $m ) => $m->add_category( 'brand', [ 'title' => 'برند', 'icon' => 'eicon-star' ] ) );
add_action( 'elementor/widgets/register', function ( $m ) {
  require_once __DIR__ . '/base.php';
  foreach ( glob( __DIR__ . '/widgets/*.php' ) as $f ) require_once $f;
  foreach ( [ 'Hero', 'Grid', 'Stack', /*...*/ ] as $c ) { $fq = "\\Brand\\Widgets\\$c"; $m->register( new $fq() ); }
} );
add_action( 'wp_enqueue_scripts', 'pfx_register_assets' );           // register + enqueue
add_action( 'elementor/editor/after_enqueue_styles', /* editor.css: آیکن ویجت‌ها، راهنما */ );
add_action( 'elementor/frontend/after_register_scripts', 'pfx_register_assets' ); // تا get_script_depends کار کند
Extensions::init(); Abilities::init();                                // قابلیت‌های تب پیشرفته
```
- ثبت assets با `PFX_VERSION` به‌عنوان ver تا هر نسخه کش را بشکند.
- فونت‌ها woff2 محلی با `@font-face` و `font-display:swap`؛ زیرمجموعهٔ زبانی (latin/arabic).
- در صورت امکان یک فایل CSS و یک JS برای همهٔ ویجت‌ها (کمتر درخواست، مدیریت ساده‌تر)، و اسکریپت‌های
  سنگین (WebGL، کتابخانه) جدا و فقط وابستگی ویجتی که لازمش دارد.

## داده‌ها
| نوع | جای ذخیره | نکته |
|---|---|---|
| تنظیمات سراسری | `get_option('brand_settings')` آرایه | `pfx_opt($k)` با `wp_parse_args` روی `pfx_default_settings()` — کلید تازه خودبه‌خود پیش‌فرض می‌گیرد |
| فهرست‌های سراسری (شبکه‌های اجتماعی، لینک‌ها) | option جدا، آرایهٔ ردیف‌ها | ویرایشگر مرتب‌شونده در مدیریت؛ فال‌بک به فیلدهای قدیمی قبل از اولین ذخیره |
| داده‌های هر آیتم (رنگ، جلوه، مشخصات) | post meta، JSON یک‌کلیدی مثل `_pfx_look` | تابع `pfx_item_look($id)` با پیش‌فرض‌ها؛ خروجی = متغیرهای CSS inline روی کارت |
| طبقه‌بندی | تاکسونومی‌ها | کلید سراسری «استفاده از X» که X را همه‌جا پنهان کند بی‌آنکه داده پاک شود |
| حالت دموها | option `brand_demos` | `{area: {n, hash, time}}` |
| پشتیبان چیدمان | post meta `_pfx_backups` | حداکثر ۵ نسخه |

## ابزارهای کمکی مهم
- `pfx_icons()` — آرایهٔ SVG درون‌خطی آیکن‌های پرکاربرد (ایمیل، اینستاگرام، تلگرام، واتساپ،
  لینکدین، پینترست، بیهنس، دریبل، X، فیسبوک، یوتیوب، تیک‌تاک، تردز، ویمئو، آرت‌استیشن، اتسی، کره،
  لینک، تلفن، قلب، کیف، قلم، چت، دانلود...) + `currentColor` تا رنگ‌پذیر باشند.
- `pfx_social_rows()` / `pfx_social_icon($row,$size)` — منبع واحد شبکه‌ها برای هدر، فوتر، داک،
  تماس. آیکن سفارشی تصویری با `mask-image` تا رنگ تم بگیرد.
- `pfx_query_items($args)` — یک کوئری مرکزی برای همهٔ ویجت‌های فهرستی (دسته، انتخاب دستی، ترتیب،
  تعداد، آفست، حذف تکراری). کلیدهای سراسری (مثل خاموش بودن یک تاکسونومی) فقط همین‌جا اعمال شوند.
- `pfx_item_cover_id($id)` — تصویر شاخص، وگرنه اولین تصویر گالری. (باگ واقعی: آیتم‌هایی که فقط
  گالری داشتند هیچ‌جا تصویر نداشتند.)

## مدیریت (wp-admin)
یک منوی برند با زیرمنوها: **راه‌اندازی** (ساخت سایت، گزارش)، **تم‌های رنگی**، **دموها**،
**دسته‌بندی‌ها**، **تنظیمات** (تب‌ها: تماس و شبکه‌ها، کاغذدیواری/پس‌زمینه، آیتم‌ها و جلوه‌ها)،
**پرده و افکت‌ها**. فرم‌ها با nonce و `current_user_can`، sanitize هر فیلد، color picker وردپرس
(در یک `div` بپیچ، نه `label` — نگاه کن 09). در متاباکس آیتم‌ها پیش‌نمایش زندهٔ رنگ‌ها بگذار.

## بسته‌سازی
```bash
ver=$(grep -m1 "Version:" plugin/plugin.php | sed 's/.*Version:[[:space:]]*//')
(cd wordpress && zip -q -r -X ../dist/brand-core-$ver.zip brand-core -x '*.DS_Store')
(cd wordpress && zip -q -r -X ../dist/brand-theme-$ver.zip brand -x '*.DS_Store')
```
پوشهٔ داخل zip باید همان slug باشد تا وردپرس آن را «به‌روزرسانی» تشخیص دهد، نه افزونهٔ دوم.
