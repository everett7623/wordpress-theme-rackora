/**
 * Rackora 全站脚本（Script Module）：配色切换、返回顶部与站点运行天数。
 */

import { t } from "rackora/shared";

function currentScheme() {
	const configured = document.documentElement.dataset.colorScheme;
	if (configured === "light" || configured === "dark") return configured;
	return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}

function initThemeToggle() {
	const toggles = document.querySelectorAll("[data-theme-toggle]");
	if (!toggles.length) return;

	const update = () => {
		const label =
			currentScheme() === "dark"
				? t("switchToLight", "Switch to light mode")
				: t("switchToDark", "Switch to dark mode");
		for (const toggle of toggles) {
			toggle.setAttribute("aria-label", label);
			toggle.title = label;
		}
	};

	for (const toggle of toggles) {
		toggle.addEventListener("click", () => {
			const next = currentScheme() === "dark" ? "light" : "dark";
			document.documentElement.dataset.colorScheme = next;
			try {
				localStorage.setItem("rackora-color-scheme", next);
			} catch {
				// 隐私模式下存储不可用，本页的切换依然生效。
			}
			update();
		});
	}

	// 跟随系统配色时，系统切换明暗后同步按钮提示文字。
	window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", update);
	update();
}

function initBackToTop() {
	const button = document.querySelector("[data-back-to-top]");
	if (!button) return;

	const update = () => button.classList.toggle("is-visible", window.scrollY > 600);
	window.addEventListener("scroll", update, { passive: true });
	button.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));
	update();
}

// 运行天数由访客浏览器按当前日期计算，开启页面缓存时也不会停在缓存生成的那一天。
function initSiteUptime() {
	for (const target of document.querySelectorAll("[data-site-launch]")) {
		const launch = new Date(`${target.dataset.siteLaunch}T00:00:00`);
		const elapsed = Date.now() - launch.getTime();
		if (!Number.isFinite(elapsed) || elapsed < 0) {
			target.parentElement?.remove();
			continue;
		}
		// 紧凑统计列较窄，只显示数字，单位由标签「运行天数 / Days」说明。
		target.textContent = String(Math.floor(elapsed / 86_400_000) + 1);
	}
}

initThemeToggle();
initBackToTop();
initSiteUptime();
