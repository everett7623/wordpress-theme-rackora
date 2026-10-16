/**
 * Rackora 文章页脚本（Script Module）：目录、代码块、表格、推广标记、图片灯箱、
 * 阅读进度、复制文章链接与阅读计数。
 */

import { config, copyText, icon, t } from "rackora/shared";
import { initCodeBlocks } from "rackora/post-code";
import { initTableOfContents } from "rackora/post-toc";

const PRODUCT_KEYWORDS = ["cpu", "内存", "ram", "硬盘", "存储", "流量", "带宽", "价格", "售价", "购买", "price", "storage", "bandwidth", "buy"];

function normalizeHeader(value) {
	return value.trim().toLowerCase().replace(/\s+/g, "");
}

function initTables(content) {
	for (const table of content.querySelectorAll("table")) {
		const headers = Array.from(table.querySelectorAll("thead th, tr:first-child th"));
		const names = headers.map((header) => normalizeHeader(header.textContent || ""));
		const matches = names.filter((name) => PRODUCT_KEYWORDS.some((keyword) => name.includes(keyword)));
		if (matches.length >= 2) table.classList.add("product-table");

		for (const body of table.tBodies) {
			for (const row of Array.from(body.rows)) {
				Array.from(row.cells).forEach((cell, index) => {
					const header = names[index];
					if (!header) return;
					if (/价格|售价|price/.test(header)) cell.dataset.column = "price";
					if (/购买|链接|buy/.test(header)) cell.dataset.column = "buy";
				});
			}
		}

		// 区块编辑器的表格外层 figure 已负责横向滚动，只为经典内容中的表格补一层容器。
		if (table.closest(".wp-block-table, .table-scroll")) continue;
		const wrapper = document.createElement("div");
		wrapper.className = "table-scroll";
		wrapper.tabIndex = 0;
		wrapper.setAttribute("role", "region");
		wrapper.setAttribute(
			"aria-label",
			table.classList.contains("product-table") ? t("productTable", "Product comparison table") : t("dataTable", "Data table"),
		);
		table.before(wrapper);
		wrapper.append(table);
	}
}

function initAffiliateMarkup(content) {
	for (const link of content.querySelectorAll('a[rel~="sponsored"]')) {
		link.dataset.sponsoredLabel = t("sponsored", "Sponsored");
	}
	for (const quote of content.querySelectorAll("blockquote")) {
		if (/affiliate|佣金|推广链接|合作链接/i.test(quote.textContent || "")) {
			quote.classList.add("affiliate-disclosure");
		}
	}
}

// 核心图片区块自带「点击放大」，这里只处理经典内容或 HTML 中的图片。
function initLightbox(content) {
	const images = Array.from(content.querySelectorAll("img")).filter(
		(img) => !img.closest("a, .wp-block-image, .wp-lightbox-container"),
	);
	if (!images.length) return;

	const dialog = document.createElement("dialog");
	dialog.className = "image-lightbox";
	dialog.innerHTML = `<button type="button" class="image-lightbox__close">${icon("x")}</button><img alt="" />`;
	dialog.querySelector("button").setAttribute("aria-label", t("close", "Close"));
	const preview = dialog.querySelector("img");
	dialog.querySelector("button").addEventListener("click", () => dialog.close());
	dialog.addEventListener("click", (event) => {
		if (event.target === dialog) dialog.close();
	});
	document.body.append(dialog);

	for (const img of images) {
		img.classList.add("is-zoomable");
		img.addEventListener("click", () => {
			preview.src = img.currentSrc || img.src;
			preview.alt = img.alt || "";
			dialog.showModal();
		});
	}
}

function initReadingProgress() {
	const progress = document.querySelector("[data-reading-progress]");
	if (!progress) return;

	let scheduled = false;
	const update = () => {
		scheduled = false;
		const max = document.documentElement.scrollHeight - window.innerHeight;
		const ratio = max > 0 ? Math.min(1, Math.max(0, window.scrollY / max)) : 0;
		// transform 只触发合成，不像修改 width 那样每次滚动都引起布局。
		progress.style.transform = `scaleX(${ratio})`;
	};
	const schedule = () => {
		if (scheduled) return;
		scheduled = true;
		window.requestAnimationFrame(update);
	};
	window.addEventListener("scroll", schedule, { passive: true });
	window.addEventListener("resize", schedule);
	update();
}

// 复制文章链接：按钮切换为对勾 / 叉号并由 live region 播报，1.6 秒后恢复；连续点击会先清掉上一次计时。
function initShareLink() {
	for (const button of document.querySelectorAll("[data-share-link]")) {
		const status = button.closest("[data-share-bar]")?.querySelector("[data-share-status]");
		const defaultLabel = button.getAttribute("aria-label") || "";
		let timer = 0;

		button.addEventListener("click", async () => {
			let copied = true;
			try {
				await copyText(new URL(button.dataset.shareUrl || window.location.href, window.location.origin).href);
			} catch {
				copied = false;
			}
			const message = copied ? t("linkCopied", "Link copied") : t("copyFailed", "Copy failed");
			window.clearTimeout(timer);
			button.classList.toggle("is-copied", copied);
			button.classList.toggle("is-failed", !copied);
			button.setAttribute("aria-label", message);
			button.title = message;
			if (status) status.textContent = message;

			timer = window.setTimeout(() => {
				button.classList.remove("is-copied", "is-failed");
				button.setAttribute("aria-label", defaultLabel);
				button.title = defaultLabel;
				if (status) status.textContent = "";
			}, 1600);
		});
	}
}

// 同一浏览器会话内每篇内容只计一次阅读；keepalive 保证离开页面时请求仍能送达。
function trackView() {
	if (!config.trackViews || !config.postId || !config.restUrl) return;

	const key = `rackora-viewed-${config.postId}`;
	try {
		if (sessionStorage.getItem(key)) return;
		sessionStorage.setItem(key, "1");
	} catch {
		// 会话存储不可用时仍然计数，由服务端规则过滤。
	}

	const headers = config.nonce ? { "X-WP-Nonce": config.nonce } : {};
	fetch(`${config.restUrl}${config.postId}`, { method: "POST", headers, keepalive: true, credentials: "same-origin" }).catch(() => {});
}

const content = document.querySelector(".article-content");
if (content) {
	initTableOfContents(content);
	initTables(content);
	initAffiliateMarkup(content);
	initLightbox(content);
	initCodeBlocks(content);
}
initReadingProgress();
initShareLink();
trackView();
