<?php
/**
 * 站点统计：访问、文章、评论、分类与运行天数（天数由前台脚本按访客时间计算，兼容页面缓存）。
 *
 * @package Rackora
 * @var array $attributes 区块属性。
 */

defined( 'ABSPATH' ) || exit;

$rackora_launch = trim( (string) $attributes['launchDate'] );
$rackora_stats  = array();

if ( rackora_option( 'views_enabled' ) ) {
	$rackora_stats[ __( 'Visits', 'rackora' ) ] = rackora_total_views();
}
$rackora_stats[ __( 'Posts', 'rackora' ) ]      = (int) wp_count_posts( 'post' )->publish;
$rackora_stats[ __( 'Comments', 'rackora' ) ]   = (int) wp_count_comments()->approved;
$rackora_stats[ __( 'Categories', 'rackora' ) ] = (int) wp_count_terms( array( 'taxonomy' => 'category' ) );
?>
<dl <?php echo get_block_wrapper_attributes( array( 'class' => 'profile-stats' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php foreach ( $rackora_stats as $rackora_label => $rackora_value ) : ?>
		<div>
			<dt><?php echo esc_html( $rackora_label ); ?></dt>
			<dd><?php echo esc_html( number_format_i18n( $rackora_value ) ); ?></dd>
		</div>
	<?php endforeach; ?>
	<?php if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $rackora_launch ) ) : ?>
		<div>
			<dt><?php echo esc_html_x( 'Days', 'site stats: days since launch', 'rackora' ); ?></dt>
			<dd data-site-launch="<?php echo esc_attr( $rackora_launch ); ?>">--</dd>
		</div>
	<?php endif; ?>
</dl>
