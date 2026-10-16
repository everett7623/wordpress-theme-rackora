/**
 * 代码块（Script Module）：语言标签、复制按钮与按需加载的 Highlight.js 高亮。
 */

import { copyText, icon, t } from "rackora/shared";

const LANGUAGE_ALIASES = {
	shell: "bash",
	sh: "bash",
	zsh: "bash",
	yml: "yaml",
	ts: "typescript",
	js: "javascript",
	html: "xml",
	text: "plaintext",
	py: "python",
	golang: "go",
	rs: "rust",
	conf: "nginx",
};

function languageName(code) {
	const className = Array.from(code.classList).find((item) => item.startsWith("language-") || item.startsWith("lang-"));
	const raw = className ? className.replace(/^lang(uage)?-/, "") : "text";
	return LANGUAGE_ALIASES[raw] || raw;
}

function setButtonState(button, iconName, text) {
	button.innerHTML = `${icon(iconName)}<span></span>`;
	button.querySelector("span").textContent = text;
}

function buildToolbar(code, language) {
	const toolbar = document.createElement("div");
	toolbar.className = "code-toolbar";

	const label = document.createElement("span");
	label.textContent = language;

	const button = document.createElement("button");
	button.type = "button";
	button.className = "code-copy";
	button.setAttribute("aria-label", t("copyCode", "Copy code"));
	setButtonState(button, "copy", t("copy", "Copy"));

	let timer = 0;
	button.addEventListener("click", async () => {
		window.clearTimeout(timer);
		try {
			await copyText(code.textContent || "");
			setButtonState(button, "check", t("copied", "Copied"));
		} catch {
			setButtonState(button, "x", t("copyFailed", "Copy failed"));
		}
		timer = window.setTimeout(() => setButtonState(button, "copy", t("copy", "Copy")), 1600);
	});

	toolbar.append(label, button);
	return toolbar;
}

/**
 * 为正文中的代码块加上工具栏，并在存在已知语言时加载高亮库。
 *
 * @param {HTMLElement} content 正文容器。
 */
export async function initCodeBlocks(content) {
	const blocks = [];
	for (const pre of content.querySelectorAll("pre")) {
		const code = pre.querySelector("code");
		if (!code || pre.parentElement?.classList.contains("code-block")) continue;

		const language = languageName(code);
		const wrapper = document.createElement("div");
		wrapper.className = "code-block";
		pre.before(wrapper);
		wrapper.append(buildToolbar(code, language), pre);
		blocks.push([code, language]);
	}

	const highlightable = blocks.filter(([, language]) => language !== "plaintext");
	if (!highlightable.length) return;

	try {
		const { default: hljs } = await import("rackora/highlight");
		for (const [code, language] of highlightable) {
			if (!hljs.getLanguage(language)) continue;
			code.classList.add(`language-${language}`);
			hljs.highlightElement(code);
		}
	} catch {
		// 高亮库加载失败时保留纯文本代码块，不影响阅读。
	}
}
