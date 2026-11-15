<?php
/**
 * Design motions: small looping line animations about digital jewelry design
 * (sketching, facets, layers, 3D wireframe, colour, technical drawing, orbit, rendering).
 * They are plain SVG animated with CSS (one uses a canvas), tinted through CSS variables.
 *
 * @package ArezooCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ay_motion_types() {
	return array(
		'sketch'    => 'طراحی با قلم (اسکچ حلقه)',
		'facets'    => 'تراش نگین (خط به خط)',
		'layers'    => 'لایه‌های طراحی دیجیتال',
		'wire'      => 'مدل سه‌بعدی حلقه (وایرفریم)',
		'palette'   => 'رنگ‌گذاری با قلم‌مو',
		'blueprint' => 'نقشهٔ فنی و اندازه‌ها',
		'orbit'     => 'مدار و درخشش',
		'render'    => 'رندر و نورپردازی نگین',
	);
}

/** The stylus used by several motions (tip at 0,0, pointing down-left). */
function ay_stylus_svg( $class = 'ay-pen' ) {
	return '<g class="' . esc_attr( $class ) . '"><g transform="rotate(38)"><path d="M0 0 L-4.2 -13 L4.2 -13 Z" fill="var(--m-line)"/><rect x="-4.2" y="-88" width="8.4" height="75" rx="4.2" fill="var(--m-pen,#F4F2EE)" stroke="var(--m-line)" stroke-width=".8"/><rect x="-4.2" y="-34" width="8.4" height="3" fill="var(--m-line)" opacity=".35"/></g></g>';
}

function ay_motion_svg( $type ) {
	$star = 'M0 -11C1.1 -3.1,3.1 -1.1,11 0C3.1 1.1,1.1 3.1,0 11C-1.1 3.1,-3.1 1.1,-11 0C-3.1 -1.1,-1.1 -3.1,0 -11Z';
	$d    = function ( $path, $i = 0, $extra = '' ) {
		return '<path class="d" pathLength="1" style="--i:' . $i . '" d="' . $path . '" ' . $extra . '/>';
	};
	$open = '<svg class="ay-mo-svg" viewBox="0 0 200 200" fill="none" stroke="var(--m-line)" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">';
	$s    = '';
	switch ( $type ) {
		case 'facets':
			/* brilliant cut seen from above: table, stars, kites and girdle facets, drawn in order, then light travels round the stone */
			$P = function ( $r, $deg ) {
				return round( 100 + cos( deg2rad( $deg ) ) * $r, 1 ) . ' ' . round( 100 + sin( deg2rad( $deg ) ) * $r, 1 );
			};
			$s .= '<g class="spin-r" stroke-width=".7" opacity=".5"><circle cx="100" cy="100" r="88" stroke-dasharray="1 5.9"/>';
			for ( $i = 0; $i < 12; $i++ ) {
				$s .= '<path d="M' . $P( 84, $i * 30 ) . 'L' . $P( 92, $i * 30 ) . '"/>';
			}
			$s .= '</g><g class="spin">';
			for ( $k = 0; $k < 8; $k++ ) {
				$t0 = 22.5 + 45 * $k;
				$s .= '<path class="fl" style="--i:' . $k . '" d="M' . $P( 30, $t0 ) . 'L' . $P( 50, $t0 - 22.5 ) . 'L' . $P( 72, $t0 ) . 'L' . $P( 50, $t0 + 22.5 ) . 'Z" fill="var(--m-accent)" stroke="none"/>';
				$s .= '<path class="fl" style="--i:' . ( $k + .5 ) . '" d="M' . $P( 30, $t0 ) . 'L' . $P( 30, $t0 + 45 ) . 'L' . $P( 50, $t0 + 22.5 ) . 'Z" fill="var(--m-fill)" stroke="none"/>';
			}
			$s  .= $d( 'M172 100A72 72 0 1 1 28 100A72 72 0 1 1 172 100Z', 0 ) . $d( 'M176 100A76 76 0 1 1 24 100A76 76 0 1 1 176 100Z', 0.3, 'opacity=".45" stroke-width=".8"' );
			$oct = 'M';
			for ( $k = 0; $k < 8; $k++ ) {
				$oct .= ( $k ? 'L' : '' ) . $P( 30, 22.5 + 45 * $k );
			}
			$s .= $d( $oct . 'Z', 1 );
			for ( $k = 0; $k < 8; $k++ ) {
				$t0 = 22.5 + 45 * $k;
				$s .= $d( 'M' . $P( 30, $t0 ) . 'L' . $P( 50, $t0 + 22.5 ) . 'L' . $P( 30, $t0 + 45 ), 1.6 + $k * 0.22, 'opacity=".9"' );
				$s .= $d( 'M' . $P( 50, $t0 + 22.5 ) . 'L' . $P( 72, $t0 ) . 'M' . $P( 50, $t0 + 22.5 ) . 'L' . $P( 72, $t0 + 45 ), 3.4 + $k * 0.22, 'opacity=".8"' );
				$s .= $d( 'M' . $P( 50, $t0 + 22.5 ) . 'L' . $P( 72, $t0 + 22.5 ), 5.2 + $k * 0.15, 'opacity=".55" stroke-width=".9"' );
			}
			$s .= '<circle class="wash" cx="100" cy="100" r="30" fill="var(--m-accent)" stroke="none"/></g>';
			$s .= '<g transform="translate(152 44)"><path class="tw" d="' . $star . '" fill="var(--m-accent)" stroke="none"/></g><g transform="translate(54 152) scale(.6)"><path class="tw" style="--i:2" d="' . $star . '" fill="var(--m-line)" stroke="none"/></g><g transform="translate(118 84) scale(.45)"><path class="tw" style="--i:3.4" d="' . $star . '" fill="#fff" stroke="none"/></g>';
			break;

		case 'layers':
			$sheet = 'M100 62L168 100L100 138L32 100Z';
			$s    .= '<g class="ly" style="--i:0"><path d="' . $sheet . '" fill="var(--m-fill)" fill-opacity=".9"/><path d="M100 78L138 100L100 122L62 100Z" stroke-dasharray="3 4"/></g>';
			$s    .= '<g class="ly" style="--i:1"><path d="' . $sheet . '" fill="var(--m-fill)" fill-opacity=".75"/><path d="M100 78L138 100L100 122L62 100Z" fill="var(--m-accent)" fill-opacity=".35"/></g>';
			$s    .= '<g class="ly" style="--i:2"><path d="' . $sheet . '" fill="var(--m-fill)" fill-opacity=".6"/><path d="M100 78L138 100L100 122L62 100ZM100 78L100 122M62 100L138 100M81 89L119 111M119 89L81 111" fill="var(--m-accent)" fill-opacity=".55"/></g>';
			foreach ( array( 'LIGHT', 'COLOUR', 'LINE', 'SKETCH' ) as $li => $lt ) {
				$s .= '<g class="dim" style="--i:' . ( $li * .5 ) . '"><text x="12" y="' . ( 34 + $li * 12 ) . '" stroke="none" fill="var(--m-line)" font-size="7" font-family="Jost,sans-serif" letter-spacing="1.4" opacity=".75">' . $lt . '</text><circle cx="6" cy="' . ( 31.5 + $li * 12 ) . '" r="1.6" fill="var(--m-accent)" stroke="none"/></g>';
			}
			$s    .= '<g class="ly" style="--i:3"><path d="' . $sheet . '" fill="var(--m-fill)" fill-opacity=".35"/><g transform="translate(100 100) scale(.9)"><path class="tw" d="' . $star . '" fill="var(--m-line)" stroke="none"/></g><g transform="translate(126 92) scale(.45)"><path class="tw" style="--i:2" d="' . $star . '" fill="var(--m-accent)" stroke="none"/></g></g>';
			break;

		case 'palette':
			$strokes = array( 'M36 62C70 44,118 70,164 52', 'M36 96C74 80,120 108,164 88', 'M36 130C72 114,122 142,164 124', 'M36 162C76 148,116 172,150 158' );
			$colors  = array( 'var(--m-accent)', 'var(--m-line)', 'var(--m-fill2,var(--m-accent))', 'var(--m-line)' );
			foreach ( $strokes as $i => $p ) {
				$s .= '<path class="d br" pathLength="1" style="--i:' . ( $i * 1.1 ) . '" d="' . $p . '" stroke="' . $colors[ $i ] . '" stroke-width="15" stroke-opacity="' . ( $i % 2 ? '.35' : '.8' ) . '"/>';
			}
			$s .= '<g class="pen-path pp-palette">' . ay_stylus_svg() . '</g>';
			break;

		case 'blueprint':
			$s .= '<g class="grid" stroke-width=".5" opacity=".28">';
			for ( $i = 20; $i <= 180; $i += 20 ) {
				$s .= '<path d="M' . $i . ' 14V186M14 ' . $i . 'H186"/>';
			}
			$s .= '</g>' . $d( 'M152 104A52 52 0 1 1 48 104A52 52 0 1 1 152 104Z', 0 ) . $d( 'M142 104A42 42 0 1 1 58 104A42 42 0 1 1 142 104Z', 0.6 );
			$s .= $d( 'M84 52L90 38L110 38L116 52L100 66Z', 1.2 ) . $d( 'M84 52L116 52M90 38L95 52L100 66L105 52L110 38', 1.6, 'opacity=".8"' );
			$s .= '<path class="dash" d="M100 20V186M26 104H174" stroke-dasharray="6 5" opacity=".55"/>';
			$s .= '<g class="dim" style="--i:0"><path d="M48 172H152M48 166V178M152 166V178"/><text x="100" y="190" text-anchor="middle" stroke="none" fill="var(--m-line)" font-size="10" font-family="Jost,sans-serif" letter-spacing="1">Ø 17.3 mm</text></g>';
			$s .= '<g class="dim" style="--i:1"><path d="M128 38H168M168 38V52"/><text x="150" y="30" text-anchor="middle" stroke="none" fill="var(--m-accent)" font-size="10" font-family="Jost,sans-serif" letter-spacing="1">2.4 ct</text></g>';
			break;

		case 'orbit':
			$s .= '<g transform="rotate(-24 100 100)"><ellipse class="orb" cx="100" cy="100" rx="84" ry="30" stroke-dasharray="5 7"/><g class="orb-dot"><g transform="scale(.9)"><path d="' . $star . '" fill="var(--m-accent)" stroke="none"/></g></g></g>';
			$s .= $d( 'M72 84L84 66L116 66L128 84L100 134Z', 0, 'fill="var(--m-fill)" fill-opacity=".5"' ) . $d( 'M72 84L128 84M84 66L92 84L100 134L108 84L116 66M92 84L100 66L108 84', 0.8, 'opacity=".85"' );
			$s .= '<g transform="translate(100 96)"><path class="tw" d="' . $star . '" fill="var(--m-line)" stroke="none"/></g>';
			break;

		case 'render':
			/* a pear-cut stone rendered tile by tile like a ray-tracer, then a light sweep, sparkles and caustics on the ground */
			$pear = 'M100 22C132 58,150 92,150 118A50 50 0 1 1 50 118C50 92,68 58,100 22Z';
			$s   .= '<defs><clipPath id="ayPear"><path d="' . $pear . '"/></clipPath><linearGradient id="ayPearG" gradientUnits="userSpaceOnUse" x1="50" y1="22" x2="150" y2="168"><stop offset="0" stop-color="#fff" stop-opacity=".95"/><stop offset=".22" stop-color="var(--m-accent)"/><stop offset=".6" stop-color="var(--m-fill)"/><stop offset="1" stop-color="var(--m-line)" stop-opacity=".7"/></linearGradient><radialGradient id="ayPearS" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="var(--m-line)" stop-opacity=".45"/><stop offset="1" stop-color="var(--m-line)" stop-opacity="0"/></radialGradient></defs>';
			$s   .= '<ellipse class="shadow" cx="100" cy="182" rx="58" ry="7" fill="url(#ayPearS)" stroke="none"/>';
			$s   .= '<g class="caus" stroke="var(--m-accent)" stroke-width="1"><path d="M84 176L70 192M100 178L100 196M116 176L130 192M92 177L84 190M108 177L116 190"/></g>';
			$s   .= '<g clip-path="url(#ayPear)">';
			$n    = 0;
			for ( $y = 20; $y < 170; $y += 15 ) {
				$cols = range( 50, 140, 15 );
				if ( ( ( $y - 20 ) / 15 ) % 2 ) {
					$cols = array_reverse( $cols );
				}
				foreach ( $cols as $x ) {
					$s .= '<rect class="tile" style="--i:' . $n++ . '" x="' . $x . '" y="' . $y . '" width="15.4" height="15.4" fill="url(#ayPearG)" stroke="none"/>';
				}
			}
			$s .= '<g class="facet-in" stroke="#fff" stroke-width=".8"><path d="M100 22L100 168M52 110L148 110M70 76L100 128L130 76M52 110L100 168L148 110M70 76L130 76M70 76L52 110M130 76L148 110M100 22L70 76M100 22L130 76M76 148L100 128L124 148" opacity=".55"/></g>';
			$s .= '<rect class="scan" x="40" y="14" width="120" height="3" fill="#fff" fill-opacity=".85" stroke="none"/><rect class="sweep" x="-60" y="0" width="40" height="220" fill="#fff" fill-opacity=".5" stroke="none" transform="rotate(24 100 100)"/></g>';
			$s .= $d( $pear, 0 ) . $d( 'M100 22L100 168M52 110L148 110M70 76L100 128L130 76M52 110L100 168L148 110M70 76L130 76', 0.7, 'opacity=".5" stroke-width=".9"' );
			$s .= '<g class="hud" stroke-width=".8" opacity=".7"><path d="M22 30V20H32M168 20H178V30M178 162V172H168M32 172H22V162"/><text x="24" y="190" stroke="none" fill="var(--m-line)" font-size="8" font-family="Jost,sans-serif" letter-spacing="1.5">RENDER</text><path class="bar" d="M62 187H120" stroke="var(--m-accent)" stroke-width="2" pathLength="1"/></g>';
			$s .= '<g transform="translate(128 60)"><path class="tw" d="' . $star . '" fill="#fff" stroke="none"/></g><g transform="translate(76 96) scale(.55)"><path class="tw" style="--i:2.2" d="' . $star . '" fill="#fff" stroke="none"/></g><g transform="translate(112 138) scale(.4)"><path class="tw" style="--i:3.6" d="' . $star . '" fill="#fff" stroke="none"/></g>';
			break;

		case 'wire':
			return '<canvas class="ay-mo-wire" width="400" height="400" aria-hidden="true"></canvas>';

		case 'sketch':
		default:
			/* a solitaire ring built up like a designer's sheet: guides, band, basket and stone, hatching, notes, then colour */
			$s .= '<g class="grid" stroke-width=".5" opacity=".22"><path d="M100 14V192M30 124H170" stroke-dasharray="2 4"/><circle cx="100" cy="124" r="62" stroke-dasharray="2 5"/><path d="M62 46H138" stroke-dasharray="2 4"/></g>';
			$s .= '<circle class="wash" cx="100" cy="124" r="40" fill="var(--m-accent)" stroke="none"/><path class="wash" style="--i:1" d="M74 62L86 46L114 46L126 62L100 92Z" fill="var(--m-fill)" stroke="none"/>';
			$s .= $d( 'M150 124A50 54 0 1 1 50 124A50 54 0 1 1 150 124Z', 0 ) . $d( 'M140 124A40 44 0 1 1 60 124A40 44 0 1 1 140 124Z', 0.6 );
			$s .= $d( 'M56 100C58 84,68 72,82 68M144 100C142 84,132 72,118 68', 1.1, 'opacity=".6" stroke-width=".9"' );
			$s .= $d( 'M74 62L86 46L114 46L126 62L100 92Z', 1.5 ) . $d( 'M74 62L126 62M86 46L92 62L100 92L108 62L114 46M92 62L100 46L108 62', 2, 'opacity=".85" stroke-width="1"' );
			$s .= $d( 'M84 80L74 60C72 55,76 52,79 55M116 80L126 60C128 55,124 52,121 55M94 86L90 74M106 86L110 74', 2.4, 'opacity=".9"' );
			$s .= $d( 'M82 80C88 86,112 86,118 80', 2.8, 'opacity=".8"' );
			for ( $i = 0; $i < 7; $i++ ) {
				$x  = 56 + $i * 4;
				$s .= $d( 'M' . $x . ' ' . ( 142 + $i * 3 ) . 'L' . ( $x + 9 ) . ' ' . ( 134 + $i * 3 ), 3.1 + $i * 0.12, 'opacity=".5" stroke-width=".8"' );
			}
			for ( $i = 0; $i < 5; $i++ ) {
				$x  = 124 + $i * 4;
				$s .= $d( 'M' . $x . ' ' . ( 168 - $i * 4 ) . 'L' . ( $x + 8 ) . ' ' . ( 160 - $i * 4 ), 3.6 + $i * 0.12, 'opacity=".4" stroke-width=".8"' );
			}
			$s .= '<g class="dim" style="--i:0"><path d="M60 196H140M60 191V201M140 191V201" stroke-width=".8"/><text x="100" y="190" text-anchor="middle" stroke="none" fill="var(--m-line)" font-size="8" font-family="Jost,sans-serif" letter-spacing="1">17.3 mm</text></g>';
			$s .= '<g class="dim" style="--i:1"><path d="M130 50L158 36H176" stroke-width=".8"/><circle cx="130" cy="50" r="1.6" fill="var(--m-accent)" stroke="none"/><text x="160" y="31" stroke="none" fill="var(--m-accent)" font-size="8" font-family="Jost,sans-serif" letter-spacing="1">1.2 ct</text></g>';
			$s .= '<g class="sw" style="--i:0"><circle cx="20" cy="150" r="6" fill="var(--m-accent)" stroke="none"/></g><g class="sw" style="--i:1"><circle cx="20" cy="166" r="6" fill="var(--m-fill)" stroke="none"/></g><g class="sw" style="--i:2"><circle cx="20" cy="182" r="6" fill="var(--m-line)" stroke="none"/></g>';
			$s .= '<g transform="translate(118 44) scale(.7)"><path class="tw" style="--i:4" d="' . $star . '" fill="var(--m-accent)" stroke="none"/></g>';
			$s .= '<g class="pen-path pp-sketch">' . ay_stylus_svg() . '</g>';
			break;
	}
	return $open . $s . '</svg>';
}
