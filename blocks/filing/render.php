<?php
/**
 * 备案信息：ICP 备案号与公网安备号，填写跳转地址时输出为链接。
 *
 * @package Rackora
 * @var array $attributes 区块属性。
 */

defined( 'ABSPATH' ) || exit;

$rackora_icp_number = trim( (string) $attributes['icpNumber'] );
$rackora_icp_url    = trim( (string) $attributes['icpUrl'] );
$rackora_psb_number = trim( (string) $attributes['psbNumber'] );
$rackora_psb_url    = trim( (string) $attributes['psbUrl'] );

if ( '' === $rackora_icp_number ) {
	$rackora_icp_number = trim( (string) rackora_option( 'icp_number' ) );
	if ( '' === $rackora_icp_url || 'https://beian.miit.gov.cn/' === $rackora_icp_url ) {
		$rackora_icp_url = trim( (string) rackora_option( 'icp_url' ) );
	}
}
if ( '' === $rackora_psb_number ) {
	$rackora_psb_number = trim( (string) rackora_option( 'psb_number' ) );
	if ( '' === $rackora_psb_url ) {
		$rackora_psb_url = trim( (string) rackora_option( 'psb_url' ) );
	}
}

$rackora_entries = array(
	array( $rackora_icp_number, $rackora_icp_url ),
	array( $rackora_psb_number, $rackora_psb_url ),
);
$rackora_items   = array();

foreach ( $rackora_entries as $rackora_entry ) {
	list( $rackora_text, $rackora_url ) = $rackora_entry;
	if ( '' === $rackora_text ) {
		continue;
	}
	$rackora_items[] = '' !== $rackora_url
		? sprintf( '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>', esc_url( $rackora_url ), esc_html( $rackora_text ) )
		: sprintf( '<span>%s</span>', esc_html( $rackora_text ) );
}

if ( ! $rackora_items ) {
	return;
}

printf(
	'<p %1$s>%2$s</p>',
	get_block_wrapper_attributes( array( 'class' => 'footer-compliance' ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	implode( '', $rackora_items ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 各项已转义。
);
