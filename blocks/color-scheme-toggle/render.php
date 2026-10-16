<?php
/**
 * 配色切换按钮：月亮 / 太阳图标由 CSS 根据当前配色切换，脚本只负责保存选择。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

$rackora_label = __( 'Toggle color scheme', 'rackora' );
?>
<button <?php echo get_block_wrapper_attributes( array( 'class' => 'icon-button', 'type' => 'button', 'title' => $rackora_label, 'aria-label' => $rackora_label, 'data-theme-toggle' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="theme-toggle__moon"><?php echo rackora_icon( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
	<span class="theme-toggle__sun"><?php echo rackora_icon( 'sun' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
</button>
