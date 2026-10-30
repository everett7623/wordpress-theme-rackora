/**
 * 文章目录（Script Module）：为标题补锚点按钮，滚动时高亮当前章节，
 * 窄屏下提供底部目录抽屉。优先使用服务端已输出的目录链接。
 */

import { icon, t } from "rackora/shared";

function slugify(value) {
	return (
		value
			.trim()
			.toLowerCase()
			.replace(/[^\p{Letter}\p{Number}\s-]/gu, "")
			.replace(/[\s_]+/g, "-")
			.replace(/-+/g, "-")
			.replace(/^-|-$/g, "") || "section"
	);
}

function ensureUniqueId(base, usedIds, element) {
	let id = base;
	let counter = 2;
	for (;;) {
		const existing = document.getElementById(id);
		if (!usedIds.has(id) && (!existing || existing === element)) break;
		id = `${base}-${counter}`;
		counter += 1;
	}
	usedIds.add(id);
	return id;
}

function setActiveLink(targetId) {
	for (const link of document.querySelectorAll("[data-toc] a, [data-mobile-toc] nav a")) {
		const active = link.getAttribute("href") === `#${targetId}`;
		link.classList.toggle("is-active", active);
		if (active) link.setAttribute("aria-current", "location");
		else link.removeAttribute("aria-current");
	}
}

function initMobileToc(section, navigation) {
	if (document.querySelector("[data-mobile-toc]")) return;

	const toggle = document.createElement("button");
	toggle.type = "button";
	toggle.className = "mobile-toc-toggle";
	toggle.dataset.mobileToc = "toggle";
	toggle.setAttribute("aria-expanded", "false");
	toggle.setAttribute("aria-controls", "mobile-toc-panel");
	toggle.innerHTML = `${icon("list")}<span></span>`;
	toggle.querySelector("span").textContent = t("contents", "Contents");

	const panel = document.createElement("div");
	panel.id = "mobile-toc-panel";
	panel.className = "mobile-toc-panel";
	panel.hidden = true;
	panel.dataset.mobileToc = "panel";
	panel.innerHTML = `<div class="mobile-toc-panel__sheet"><header><strong></strong><button type="button" class="icon-button" data-mobile-toc-close>${icon("x")}</button></header><nav></nav></div>`;
	panel.querySelector("strong").textContent = t("onThisPage", "On this page");
	panel.querySelector("[data-mobile-toc-close]").setAttribute("aria-label", t("close", "Close"));
	panel.querySelector("nav").append(...Array.from(navigation.children, (node) => node.cloneNode(true)));

	const close = () => {
		panel.hidden = true;
		document.body.classList.remove("toc-open");
		toggle.setAttribute("aria-expanded", "false");
	};

	toggle.addEventListener("click", () => {
		const open = panel.hidden;
		panel.hidden = !open;
		document.body.classList.toggle("toc-open", open);
		toggle.setAttribute("aria-expanded", String(open));
	});
	panel.querySelector("[data-mobile-toc-close]").addEventListener("click", close);
	panel.addEventListener("click", (event) => {
		if (event.target === panel || event.target.closest("a")) close();
	});
	document.addEventListener("keydown", (event) => {
		if (event.key === "Escape" && !panel.hidden) close();
	});

	(section.closest(".article-sidebar") || section).after(toggle);
	document.body.append(panel);
}

/**
 * 生成或增强目录。少于两个标题时保持目录隐藏。
 *
 * @param {HTMLElement} content 正文容器。
 */
export function initTableOfContents(content) {
	const section = document.querySelector("[data-toc-container]");
	const navigation = section?.querySelector("[data-toc]");
	const headings = Array.from(content.querySelectorAll("h2, h3"));
	if (headings.length < 2) return;

	const serverToc = Boolean(navigation && navigation.children.length);
	const usedIds = new Set();

	for (const heading of headings) {
		const label = heading.textContent?.trim() || t("section", "Section");
		heading.id = ensureUniqueId(heading.id || slugify(label), usedIds, heading);

		if (!heading.querySelector(":scope > .heading-anchor")) {
			const anchor = document.createElement("a");
			anchor.className = "heading-anchor";
			anchor.href = `#${heading.id}`;
			anchor.textContent = "#";
			anchor.setAttribute("aria-label", t("linkTo", "Link to %s").replace("%s", label));
			heading.prepend(anchor);
		}

		if (navigation && !serverToc) {
			const link = document.createElement("a");
			link.href = `#${heading.id}`;
			link.dataset.level = heading.tagName.slice(1);
			link.textContent = label;
			navigation.append(link);
		}
	}

	if (!section || !navigation) return;
	section.hidden = false;
	initMobileToc(section, navigation);

	let scheduled = false;
	const readHeaderHeight = () =>
		Number.parseFloat(getComputedStyle(document.documentElement).getPropertyValue("--rack-header-height")) || 62;
	let header = readHeaderHeight();

	const update = () => {
		scheduled = false;
		const marker = Math.max(header + 24, Math.min(window.innerHeight * 0.48, 520));
		let active = headings[0];
		for (const heading of headings) {
			if (heading.getBoundingClientRect().top > marker) break;
			active = heading;
		}
		if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2) {
			active = headings.at(-1) || active;
		}
		setActiveLink(active.id);
	};
	const schedule = () => {
		if (scheduled) return;
		scheduled = true;
		window.requestAnimationFrame(update);
	};
	const onResize = () => {
		header = readHeaderHeight();
		schedule();
	};

	schedule();
	window.addEventListener("scroll", schedule, { passive: true });
	window.addEventListener("resize", onResize);
	window.addEventListener("hashchange", schedule);
}
