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
			$s .= '<g class="spin">' . $d( 'M170 100A70 70 0 1 1 30 100A70 70 0 1 1 170 100Z', 0 );
			$pts = array();
			for ( $i = 0; $i < 8; $i++ ) {
				$a     = $i * M_PI / 4 + M_PI / 8;
				$pts[] = array( 100 + cos( $a ) * 34, 100 + sin( $a ) * 34, $a );
			}
			$oct = 'M';
			foreach ( $pts as $k => $p ) {
				$oct .= ( $k ? 'L' : '' ) . round( $p[0], 1 ) . ' ' . round( $p[1], 1 );
			}
			$s .= $d( $oct . 'Z', 1 );
			foreach ( $pts as $k => $p ) {
				$a1 = $p[2] - M_PI / 8;
				$a2 = $p[2] + M_PI / 8;
				$s .= $d( 'M' . round( $p[0], 1 ) . ' ' . round( $p[1], 1 ) . 'L' . round( 100 + cos( $a1 ) * 70, 1 ) . ' ' . round( 100 + sin( $a1 ) * 70, 1 ), 2 + $k * 0.35, 'opacity=".8"' );
				$s .= $d( 'M' . round( $p[0], 1 ) . ' ' . round( $p[1], 1 ) . 'L' . round( 100 + cos( $a2 ) * 70, 1 ) . ' ' . round( 100 + sin( $a2 ) * 70, 1 ), 2.2 + $k * 0.35, 'opacity=".8"' );
			}
			$s .= '<circle class="wash" cx="100" cy="100" r="34" fill="var(--m-accent)" stroke="none"/></g>';
			$s .= '<g transform="translate(150 46)"><path class="tw" d="' . $star . '" fill="var(--m-accent)" stroke="none"/></g><g transform="translate(58 150) scale(.6)"><path class="tw" style="--i:2" d="' . $star . '" fill="var(--m-line)" stroke="none"/></g>';
			break;

		case 'layers':
			$sheet = 'M100 62L168 100L100 138L32 100Z';
			$s    .= '<g class="ly" style="--i:0"><path d="' . $sheet . '" fill="var(--m-fill)" fill-opacity=".9"/><path d="M100 78L138 100L100 122L62 100Z" stroke-dasharray="3 4"/></g>';
			$s    .= '<g class="ly" style="--i:1"><path d="' . $sheet . '" fill="var(--m-fill)" fill-opacity=".75"/><path d="M100 78L138 100L100 122L62 100Z" fill="var(--m-accent)" fill-opacity=".35"/></g>';
			$s    .= '<g class="ly" style="--i:2"><path d="' . $sheet . '" fill="var(--m-fill)" fill-opacity=".6"/><path d="M100 78L138 100L100 122L62 100ZM100 78L100 122M62 100L138 100M81 89L119 111M119 89L81 111" fill="var(--m-accent)" fill-opacity=".55"/></g>';
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
			$pear = 'M100 26C132 62,150 96,150 122A50 50 0 1 1 50 122C50 96,68 62,100 26Z';
			$s   .= '<defs><clipPath id="ayPear"><path d="' . $pear . '"/></clipPath><linearGradient id="ayPearG" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="var(--m-accent)" stop-opacity=".95"/><stop offset=".55" stop-color="var(--m-fill)" stop-opacity=".9"/><stop offset="1" stop-color="var(--m-line)" stop-opacity=".55"/></linearGradient></defs>';
			$s   .= '<g clip-path="url(#ayPear)"><rect class="fillup" x="40" y="20" width="120" height="160" fill="url(#ayPearG)" stroke="none"/><rect class="sweep" x="-60" y="0" width="46" height="220" fill="#fff" fill-opacity=".55" stroke="none" transform="rotate(24 100 100)"/></g>';
			$s   .= $d( $pear, 0 ) . $d( 'M100 26L100 172M62 100L138 100M72 84L100 122L128 84M62 100L100 172L138 100M72 84L128 84', 0.9, 'opacity=".7"' );
			$s   .= '<g transform="translate(132 64)"><path class="tw" d="' . $star . '" fill="#fff" stroke="none"/></g>';
			break;

		case 'wire':
			return '<canvas class="ay-mo-wire" width="400" height="400" aria-hidden="true"></canvas>';

		case 'sketch':
		default:
			$s .= '<circle class="wash" cx="100" cy="128" r="40" fill="var(--m-accent)" stroke="none"/><path class="wash" style="--i:1" d="M78 62L88 46L112 46L122 62L100 90Z" fill="var(--m-fill)" stroke="none"/>';
			$s .= $d( 'M152 126A52 56 0 1 1 48 126A52 56 0 1 1 152 126Z', 0 ) . $d( 'M142 126A42 46 0 1 1 58 126A42 46 0 1 1 142 126Z', 0.7 );
			$s .= $d( 'M78 62L88 46L112 46L122 62L100 90Z', 1.5 ) . $d( 'M78 62L122 62M88 46L93 62L100 90L107 62L112 46M93 62L100 46L107 62', 2, 'opacity=".85"' );
			$s .= $d( 'M82 74L76 60M118 74L124 60', 2.4, 'opacity=".7"' );
			$s .= '<g class="sw" style="--i:0"><rect x="60" y="182" width="22" height="9" rx="4.5" fill="var(--m-accent)" stroke="none"/></g><g class="sw" style="--i:1"><rect x="89" y="182" width="22" height="9" rx="4.5" fill="var(--m-fill)" stroke="none"/></g><g class="sw" style="--i:2"><rect x="118" y="182" width="22" height="9" rx="4.5" fill="var(--m-line)" stroke="none"/></g>';
			$s .= '<g class="pen-path pp-sketch">' . ay_stylus_svg() . '</g>';
			break;
	}
	return $open . $s . '</svg>';
}
