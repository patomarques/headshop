<?php if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @var array  $breadcrumb
 * @var string $wrap_before
 * @var string $wrap_after
 * @var string $delimiter
 * @var string $before
 * @var string $after
 */

if ( empty( $breadcrumb ) ) return;

$home_svg  = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>';
$home_href = home_url( '/' );

$crumbs = array_values( array_filter( $breadcrumb, function ( $c ) use ( $home_href ) {
    return rtrim( $c[1] ?? '', '/' ) !== rtrim( $home_href, '/' );
} ) );

echo $wrap_before;

echo '<a href="' . esc_url( $home_href ) . '" aria-label="' . esc_attr__( 'Home', 'woocommerce' ) . '">' . $home_svg . '</a>';

foreach ( $crumbs as $i => $crumb ) :
    $is_last = ( $i === count( $crumbs ) - 1 );
    echo $delimiter;
    if ( ! empty( $crumb[1] ) && ! $is_last ) {
        echo '<a href="' . esc_url( $crumb[1] ) . '">' . esc_html( $crumb[0] ) . '</a>';
    } else {
        echo '<span>' . esc_html( $crumb[0] ) . '</span>';
    }
endforeach;

echo $wrap_after;
