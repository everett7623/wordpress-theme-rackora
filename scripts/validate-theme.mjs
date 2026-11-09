// 主题结构检查：版本一致、JSON 有效、区块与模式引用完整、翻译完整、PHP 语法正确、无调试残留。
import { execFileSync } from "node:child_process";
import { existsSync, readdirSync, readFileSync, statSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const errors = [];
const read = (file) => readFileSync(path.join(root, file), "utf8");
const list = (dir, ext) =>
  existsSync(path.join(root, dir))
    ? readdirSync(path.join(root, dir)).filter((name) => name.endsWith(ext)).map((name) => `${dir}/${name}`)
    : [];

function walk(dir, ext, found = []) {
  for (const name of readdirSync(path.join(root, dir))) {
    if (["node_modules", ".git", "dist"].includes(name)) continue;
    const rel = dir === "." ? name : `${dir}/${name}`;
    if (statSync(path.join(root, rel)).isDirectory()) walk(rel, ext, found);
    else if (rel.endsWith(ext)) found.push(rel);
  }
  return found;
}

// 1. 版本号：style.css 与 package.json 保持一致
const version = read("style.css").match(/^Version:\s*(.+)$/m)?.[1].trim();
const packageVersion = JSON.parse(read("package.json")).version;
if (!version) errors.push("style.css 缺少 Version 头信息");
else if (version !== packageVersion) errors.push(`版本不一致：style.css ${version} / package.json ${packageVersion}`);

// 2. theme.json 与 block.json 可解析，区块元数据完整
const theme = JSON.parse(read("theme.json"));
const blockNames = new Set();
for (const dir of readdirSync(path.join(root, "blocks"))) {
  const file = `blocks/${dir}/block.json`;
  if (!existsSync(path.join(root, file))) continue;
  const block = JSON.parse(read(file));
  blockNames.add(block.name);
  if (block.name !== `rackora/${dir}`) errors.push(`${file}: name 应为 rackora/${dir}`);
  if (block.textdomain !== "rackora") errors.push(`${file}: textdomain 应为 rackora`);
  for (const key of ["render", "style"]) {
    if (block[key]?.startsWith("file:") && !existsSync(path.join(root, "blocks", dir, block[key].slice(5)))) {
      errors.push(`${file}: 找不到 ${key} 文件 ${block[key]}`);
    }
  }
}

// 3. 自定义模板与模板部件都有对应文件
for (const template of theme.customTemplates || []) {
  if (!existsSync(path.join(root, "templates", `${template.name}.html`))) errors.push(`缺少模板 templates/${template.name}.html`);
}
for (const part of theme.templateParts || []) {
  if (!existsSync(path.join(root, "parts", `${part.name}.html`))) errors.push(`缺少模板部件 parts/${part.name}.html`);
}

// 4. 模板、部件与模式中引用的 rackora 区块和模式都存在
const patternSlugs = new Set(
  list("patterns", ".php").map((file) => read(file).match(/^\s*\*\s*Slug:\s*(\S+)/m)?.[1]).filter(Boolean),
);
for (const file of [...list("templates", ".html"), ...list("parts", ".html"), ...list("patterns", ".php")]) {
  const source = read(file);
  for (const [, name] of source.matchAll(/<!-- wp:(rackora\/[a-z0-9-]+)/g)) {
    if (!blockNames.has(name)) errors.push(`${file}: 引用了不存在的区块 ${name}`);
  }
  for (const [, slug] of source.matchAll(/<!-- wp:pattern \{"slug":"([^"]+)"/g)) {
    if (!patternSlugs.has(slug)) errors.push(`${file}: 引用了不存在的模式 ${slug}`);
  }
}

// 5. zh_CN 翻译没有遗漏，且 po / mo / PHP 词条保持一致
const po = read("languages/zh_CN.po");
const untranslated = [...po.matchAll(/\nmsgid "(.+)"\n(?:msgid_plural ".*"\n)?msgstr(?:\[0\])? ""\n/g)].map((m) => m[1]);
if (untranslated.length) errors.push(`zh_CN.po 有 ${untranslated.length} 条未翻译：${untranslated.slice(0, 3).join(" | ")}`);

const unescapePo = (s) => s.replace(/\\n/g, "\n").replace(/\\r/g, "\r").replace(/\\t/g, "\t").replace(/\\"/g, '"').replace(/\\\\/g, "\\");
const poMap = new Map();
for (const block of po.split(/\r?\n\r?\n+/)) {
  let ctx = null, id = null, str = null, field = null;
  for (const raw of block.split(/\r?\n/)) {
    const line = raw.trim();
    if (!line || line.startsWith("#")) continue;
    let m;
    if ((m = line.match(/^msgctxt\s+"(.*)"$/))) { ctx = unescapePo(m[1]); field = "ctx"; }
    else if ((m = line.match(/^msgid\s+"(.*)"$/))) { id = unescapePo(m[1]); field = "id"; }
    else if (line.startsWith("msgid_plural ")) { field = "plural"; }
    else if ((m = line.match(/^msgstr(?:\[0\])?\s+"(.*)"$/))) { str = unescapePo(m[1]); field = "str"; }
    else if ((m = line.match(/^"(.*)"$/))) {
      const val = unescapePo(m[1]);
      if (field === "ctx") ctx += val;
      else if (field === "id") id += val;
      else if (field === "str") str += val;
    }
  }
  if (id !== null && str !== null) poMap.set(ctx !== null ? `${ctx}\x04${id}` : id, str);
}

const moBuf = readFileSync(path.join(root, "languages/zh_CN.mo"));
const moCount = moBuf.readUInt32LE(8);
const moOrigOff = moBuf.readUInt32LE(12);
const moTransOff = moBuf.readUInt32LE(16);
const moMap = new Map();
for (let i = 0; i < moCount; i++) {
  const oLen = moBuf.readUInt32LE(moOrigOff + i * 8);
  const oPos = moBuf.readUInt32LE(moOrigOff + i * 8 + 4);
  const tLen = moBuf.readUInt32LE(moTransOff + i * 8);
  const tPos = moBuf.readUInt32LE(moTransOff + i * 8 + 4);
  moMap.set(moBuf.subarray(oPos, oPos + oLen).toString("utf8"), moBuf.subarray(tPos, tPos + tLen).toString("utf8"));
}
for (const [k, v] of poMap) {
  if (moMap.get(k) !== v) {
    errors.push(`zh_CN.mo 与 zh_CN.po 不一致：${JSON.stringify(k)}`);
    break;
  }
}

for (const file of walk(".", ".php")) {
  if (file.startsWith("languages/")) continue;
  const src = read(file);
  for (const m of src.matchAll(/\b(?:__|esc_html__|esc_html_e|esc_attr__|esc_attr_e|_e)\(\s*'((?:\\'|[^'])+)'\s*,\s*'rackora'\s*\)/g)) {
    const key = m[1].replace(/\\'/g, "'");
    if (!poMap.has(key)) errors.push(`${file}: 词条未收录到 zh_CN.po：${key}`);
  }
  for (const m of src.matchAll(/\b(?:_x|esc_html_x|esc_attr_x)\(\s*'((?:\\'|[^'])+)'\s*,\s*'((?:\\'|[^'])+)'\s*,\s*'rackora'\s*\)/g)) {
    const key = `${m[2].replace(/\\'/g, "'")}\x04${m[1].replace(/\\'/g, "'")}`;
    if (!poMap.has(key)) errors.push(`${file}: 上下文词条未收录到 zh_CN.po：${key}`);
  }
  for (const m of src.matchAll(/\b_n\(\s*'((?:\\'|[^'])+)'\s*,\s*'((?:\\'|[^'])+)'\s*,[^,]+,\s*'rackora'\s*\)/g)) {
    const key = m[1].replace(/\\'/g, "'");
    if (!poMap.has(key)) errors.push(`${file}: 复数词条未收录到 zh_CN.po：${key}`);
  }
}

// 6. 前端脚本不留调试输出，单文件不超过 300 行有效代码
for (const file of list("assets/js", ".js")) {
  const source = read(file);
  if (/console\.(log|debug)\(|debugger;/.test(source)) errors.push(`${file}: 存在调试输出`);
  const lines = source.split("\n").filter((line) => line.trim() && !/^\s*(\/\/|\/\*|\*)/.test(line)).length;
  if (lines > 300) errors.push(`${file}: ${lines} 行有效代码，超过 300 行`);
}

// 7. PHP 语法检查（本机没有 php 时跳过）
let php = true;
try {
  execFileSync("php", ["-v"], { stdio: "ignore" });
} catch {
  php = false;
  console.warn("未找到 php，跳过 PHP 语法检查。");
}
if (php) {
  for (const file of walk(".", ".php")) {
    try {
      execFileSync("php", ["-l", path.join(root, file)], { stdio: "pipe" });
    } catch (error) {
      errors.push(`${file}: ${String(error.stdout || error.message).trim()}`);
    }
  }
}

if (errors.length) {
  console.error(`主题检查未通过（${errors.length} 项）：`);
  for (const error of errors) console.error(`  - ${error}`);
  process.exit(1);
}
console.log(`主题检查通过：v${version}，${blockNames.size} 个区块，${patternSlugs.size} 个模式。`);
