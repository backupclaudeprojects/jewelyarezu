# 02 — ویجت‌های المنتور با تنظیمات بسیار عمیق

## کلاس پایه (همهٔ ویجت‌ها از آن ارث می‌برند)
```php
namespace Brand\Widgets;
use Elementor\Controls_Manager as CM;
abstract class Base extends \Elementor\Widget_Base {
  public function get_categories() { return [ 'brand' ]; }
  public function get_style_depends(): array { return [ 'brand' ]; }
  public function get_script_depends(): array { return [ 'brand' ]; }
  protected function is_dynamic_content(): bool { return true; } // ویجت‌های کوئری‌دار کش نشوند

  protected function sec( $id, $label, $style = false, $cond = null ) { $a = [ 'label' => $label, 'tab' => $style ? CM::TAB_STYLE : CM::TAB_CONTENT ]; if ( $cond ) $a['condition'] = $cond; $this->start_controls_section( $id, $a ); }
  protected function done() { $this->end_controls_section(); }
  protected function txt( $id, $l, $d = '', $x = [] ) { $this->add_control( $id, array_merge( [ 'label' => $l, 'type' => CM::TEXT, 'default' => $d, 'label_block' => true ], $x ) ); }
  protected function sw( $id, $l, $d = 'yes', $x = [] ) { $this->add_control( $id, array_merge( [ 'label' => $l, 'type' => CM::SWITCHER, 'return_value' => 'yes', 'default' => $d ], $x ) ); }
  protected function sel( $id, $l, $opts, $d, $x = [] ) { $this->add_control( $id, array_merge( [ 'label' => $l, 'type' => CM::SELECT, 'options' => $opts, 'default' => $d ], $x ) ); }
  /** اسلایدر که فقط یک متغیر CSS می‌نویسد؛ $resp = ریسپانسیو */
  protected function var_slider( $id, $l, $var, $min, $max, $unit = 'px', $d = null, $resp = true, $x = [] ) { /* selectors: {{WRAPPER}} => "$var: {{SIZE}}{{UNIT}};" */ }
  protected function var_color( $id, $l, $var, $x = [] ) { /* selectors: {{WRAPPER}} => "$var: {{VALUE}};" */ }
  protected function typo( $id, $l, $selector ) { $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [ 'name' => $id, 'label' => $l, 'selector' => '{{WRAPPER}} ' . $selector ] ); }
  // border(), shadow(), filter(), dims() (padding/margin/radius responsive) به همین شکل
  protected function icon_fields( \Elementor\Repeater $r ) { /* icon(select داخلی) + icon_lib(CM::ICONS) + icon_img(MEDIA) */ }
  protected function row_icon( $row ) { /* اولویت: تصویر آپلودی (mask) → کتابخانهٔ المنتور (Icons_Manager::render_icon) → داخلی */ }
}
```
نتیجه: یک ویجت ۴۰ کنترلی در ۸۰ خط خوانا جا می‌شود و همهٔ ویجت‌ها یکدست‌اند.

## دو نوع کنترل، دو روش اعمال
1. **ظاهر عددی/رنگی → `selectors` روی متغیر CSS.** CSS ویجت فقط `var(--pfx-x, پیش‌فرض)` می‌خواند.
   پیش‌فرض واقعی در CSS است، نه در کنترل. ⇒ ذخیره‌نشدن پیش‌فرض‌ها مشکلی ندارد و تم‌ها هم می‌توانند
   متغیر را بازنویسی کنند.
2. **حالت/چیدمان/روشن‌خاموش → `prefix_class`.** کلاس `pfx-mode-feature` روی wrapper؛ CSS و JS هر
   دو از کلاس می‌خوانند. برای ریسپانسیو: `'prefix_class' => 'pfx-cols%s-'` ⇒ `pfx-cols-tablet-2`.
   ⚠️ `prefix_class` و `selectors` در ویرایشگر بدون رندر مجدد اعمال می‌شوند (سریع)؛ کنترل‌هایی که HTML
   را تغییر می‌دهند `render_type => 'template'` می‌گیرند.

## چیدمان تب‌ها (همه ویجت‌ها یک‌شکل)
**Content:** محتوا → منبع داده → چیدمان → حالت‌ها → جلوه‌ها (هر کدام کلید) → رفتار موبایل.
**Style:** کارت/باکس → تصویر → عنوان → متن → دکمه → آیکن‌ها → حالت hover → جلوه‌ها (رنگ هاله، درخشش...).
**Advanced:** قابلیت‌های سراسری خود افزونه (`03`).

برای هر بخش متنی: تایپوگرافی، رنگ، رنگ hover، فاصله، تراز (ریسپانسیو)، حداکثر عرض، کوتاه‌کردن به N
خط (`-webkit-line-clamp` با متغیر)، پنهان در دستگاه.

## منبع داده (هر ویجت فهرستی)
```
source: all | terms(تاکسونومی A) | terms(تاکسونومی B) | manual(انتخاب آیتم‌ها) | query
query:  orderby (date/title/menu_order/rand), order, count, offset, exclude current, only with image
manual: SELECT2 چندتایی با گزینه‌های get_posts (ترتیب انتخاب حفظ شود)
```
+ **ریپیتر بازنویسی** (`over`): `target` (آیتم یا دسته) + پنهان، عنوان، برچسب بالایی، متن، مشخصات
(خطوط `عنوان | مقدار`)، تصویر، متن دکمه، لینک. کاربر می‌تواند متن هر آیتم را فقط در همین ویجت عوض
کند بی‌آنکه محتوای اصلی تغییر کند. این دقیقاً چیزی بود که خواسته شد.

## حالت‌های چندگانهٔ یک ویجت
یک ویجت با `card_mode` (`prefix_class pfx-mode-`) می‌تواند دو یا چند طراحی کاملاً متفاوت داشته باشد
(مثلاً «حالت ۱: گالری چندتصویری» و «حالت ۲: یک تصویر + توضیح کوتاه + مشخصات + دکمه»). بخش‌های
کنترل هر حالت با `condition` نمایش داده شوند. حالت قدیمی همیشه پیش‌فرض بماند.

برای حالت «تصویر + متن» این کنترل‌ها خواسته شد: سمت تصویر (چپ/راست/بالا/یک‌درمیان)، عرض تصویر،
fit (cover/contain)، فاصلهٔ داخلی تصویر، **شکل قاب در موبایل** (افقی/عمودی/مربع/هم‌شکل تصویر +
نسبت + عرض + ارتفاع)، هاله، برچسب بالایی، متن (کوتاه/بلند/هیچ + تعداد کلمه)، مشخصات (تعداد، سبک،
تعداد در موبایل)، جای دکمه، تراز عمودی/افقی، حداکثر عرض متن، line-clamp، لینک شدن تصویر.

## کارت‌ها و جلوه‌های تعاملی (ماژول مشترک)
`card_fx_controls($prefix)` در Base: کج‌شدن سه‌بعدی (tilt، شدت)، عمق لایه‌ها، درخشش دنبال‌کنندهٔ
نشانگر (glare، رنگ، شدت)، هالهٔ پشت کارت (`halo_switch`)، سایه‌های hover. همه با کلید و
`prefix_class`، و یک handler عمومی JS که `pointermove` را با `requestAnimationFrame` به متغیرهای
`--rx --ry --mx --my` تبدیل می‌کند. روی لمسی غیرفعال یا ملایم.

## رنگ اختصاصی هر آیتم دیتابیسی
- متاباکس «رنگ‌های این آیتم در سایت» با پیش‌نمایش زنده (هاله، درخشش hover، خط، پس‌زمینهٔ آبرنگی).
- `pfx_item_vars($id)` ⇒ `style="--pfx-halo:#..;--pfx-glow:#.."` روی کارت. CSS: `var(--pfx-halo, var(--pfx-halo-global))`.
- تنظیم سراسری (option) برای پیش‌فرض و «خاموش کردن همه»، و کلید `w_halo` در هر ویجت. سه سطح:
  سراسری ← هر آیتم ← هر ویجت.

## آیکن‌ها در ریپیتر
```php
$r->add_control( 'icon',     [ 'type' => CM::SELECT, 'options' => pfx_icon_options(), 'default' => 'link' ] );
$r->add_control( 'icon_lib', [ 'type' => CM::ICONS, 'label' => 'یا از کتابخانه' ] );
$r->add_control( 'icon_img', [ 'type' => CM::MEDIA, 'label' => 'یا تصویر / SVG خودتان' ] );
$r->add_control( 'hide_mobile', [ 'type' => CM::SWITCHER ] ); $r->add_control( 'hide_desk', [ 'type' => CM::SWITCHER ] );
```

## رندر
- `render()` سمت سرور؛ `content_template()` را ننویس مگر لازم باشد (دو منبع حقیقت = باگ). با
  `render_type => 'template'` المنتور ویجت را AJAX رندر می‌کند.
- همهٔ خروجی escape: `esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`.
- تنظیمات لازم JS را در `data-pfx='{"..."}'` (با `wp_json_encode` + `esc_attr`) بگذار، نه در `<script>`.
- لینک‌ها از کنترل URL المنتور: `$this->add_link_attributes( 'btn', $s['link'] )` (target/nofollow/custom attrs).
- `wp_get_attachment_image()` با `sizes` درست و `loading="lazy"` جز تصویر بالای صفحه (`fetchpriority="high"`).
- برای art direction (عکس جدا برای دسکتاپ/تبلت/موبایل) کنترل MEDIA جدا برای هر دستگاه + انتخاب بر اساس **نسبت ابعاد پنجره** نه فقط عرض (نگاه کن 04/08).

## JS ویجت‌ها (یک فایل، یک الگو)
```js
const init = (root) => { if (root._pfx) return; root._pfx = true; /* ... */ };
const scan = (ctx = document) => ctx.querySelectorAll('.pfx-hero').forEach(init);
document.addEventListener('DOMContentLoaded', () => scan());
window.addEventListener('elementor/frontend/init', () => {
  elementorFrontend.hooks.addAction('frontend/element_ready/global', ($el) => scan($el[0]));
});
```
- در ویرایشگر المنتور، DOM ویجت با هر تغییر جایگزین می‌شود ⇒ init باید idempotent باشد و
  منابع قبلی (rAF، observer، WebGL context، React root) پاک شوند. اگر عنصر مانده ولی تنظیمات عوض
  شده، **امضای تنظیمات** را مقایسه کن (`el._sig !== sig` ⇒ پاک‌سازی و اجرای دوباره).
- `elementorFrontend.isEditMode()` برای رفتار متفاوت در ویرایشگر (مثلاً پرده/انیمیشن ورود را رد کن).

## تله‌ها (همه واقعاً پیش آمد)
- شناسهٔ کنترل تکراری با پرو (`sticky`) ⇒ کنترل در پنل ناپدید شد. همیشه پیشوند.
- پیش‌فرض کنترل موجود را عوض کردی ⇒ همهٔ سایت‌های قبلی بی‌صدا عوض شدند. ممنوع.
- `condition` روی کنترلی که مقدارش ذخیره نشده (چون پیش‌فرض بود) درست کار می‌کند — ولی اگر بعداً
  پیش‌فرض را عوض کنی condition هم عوض می‌شود.
- کلاس عمومی `.elementor-element:not(.x)` کانتینرها را هم می‌گیرد؛ انتخابگرها را به
  `[class*="elementor-widget-pfx-"]` محدود کن.
- هاله/درخشش با `position:absolute` داخل wrapperی که `overflow:hidden` دارد بریده می‌شود؛ لایهٔ جدا بساز.
