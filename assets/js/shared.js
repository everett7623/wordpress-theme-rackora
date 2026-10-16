/**
 * Rackora 前台共享工具（Script Module）：配置、界面文案、图标与剪贴板。
 */

export const config = readConfig();

function readConfig() {
	try {
		return JSON.parse(document.getElementById("rackora-config")?.textContent || "{}");
	} catch {
		return {};
	}
}

/**
 * 读取由 PHP 翻译后的界面文案。
 *
 * @param {string} key      文案键名。
 * @param {string} fallback 未提供时的英文回退。
 * @returns {string}
 */
export function t(key, fallback) {
	return config.i18n?.[key] || fallback;
}

// Lucide 图标（ISC License）的 SVG 节点，与 inc/icons.php 保持一致。
const ICONS = {
	check: '<path d="M20 6 9 17l-5-5"/>',
	copy: '<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>',
	list: '<path d="M3 12h.01"/><path d="M3 18h.01"/><path d="M3 6h.01"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M8 6h13"/>',
	x: '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
};

/**
 * 返回内联 SVG 图标标记。
 *
 * @param {keyof typeof ICONS} name 图标名。
 * @returns {string}
 */
export function icon(name) {
	return `<svg class="rackora-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">${ICONS[name] || ""}</svg>`;
}

/**
 * 复制文本：优先使用异步剪贴板接口，非安全上下文回退到 execCommand。
 *
 * @param {string} value 要复制的文本。
 * @returns {Promise<void>}
 */
export async function copyText(value) {
	if (navigator.clipboard && window.isSecureContext) {
		await navigator.clipboard.writeText(value);
		return;
	}

	const textarea = document.createElement("textarea");
	textarea.value = value;
	textarea.style.position = "fixed";
	textarea.style.opacity = "0";
	document.body.append(textarea);
	textarea.select();
	const copied = document.execCommand("copy");
	textarea.remove();
	if (!copied) throw new Error("Copy command failed");
}
