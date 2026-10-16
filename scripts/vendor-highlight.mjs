// 打包 Highlight.js 核心与主题支持的语言，输出单个压缩的 ES 模块到 assets/vendor。
// 升级 highlight.js 或调整语言列表后运行 `pnpm vendor`，产物随仓库提交，主题运行时无需构建。
import { readFileSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

import { build } from "esbuild";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const languages = [
  "bash",
  "css",
  "dockerfile",
  "go",
  "ini",
  "java",
  "javascript",
  "json",
  "nginx",
  "plaintext",
  "python",
  "rust",
  "scss",
  "sql",
  "typescript",
  "xml",
  "yaml",
];
const { version } = JSON.parse(
  readFileSync(path.join(root, "node_modules/highlight.js/package.json"), "utf8"),
);

const entry = [
  'import hljs from "highlight.js/lib/core";',
  ...languages.map((name, index) => `import l${index} from "highlight.js/lib/languages/${name}";`),
  ...languages.map((name, index) => `hljs.registerLanguage("${name}", l${index});`),
  "export default hljs;",
].join("\n");

await build({
  stdin: { contents: entry, resolveDir: root, sourcefile: "highlight-entry.js" },
  bundle: true,
  format: "esm",
  minify: true,
  target: "es2020",
  legalComments: "none",
  banner: { js: `/*! highlight.js v${version} | BSD-3-Clause | https://github.com/highlightjs/highlight.js */` },
  outfile: path.join(root, "assets/vendor/highlight.min.js"),
});

console.log(`Bundled highlight.js ${version} with ${languages.length} languages.`);
