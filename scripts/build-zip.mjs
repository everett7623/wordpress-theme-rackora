// 用 git archive 生成可上传的主题包 dist/rackora-X.Y.Z.zip，顶层目录为 rackora/。
// 只打包已提交的内容，开发文件由 .gitattributes 的 export-ignore 排除。
import { execFileSync } from "node:child_process";
import { createHash } from "node:crypto";
import { mkdirSync, readFileSync } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..");
const version = readFileSync(path.join(root, "style.css"), "utf8").match(/^Version:\s*(.+)$/m)?.[1].trim();
if (!version) throw new Error("style.css 缺少 Version 头信息");

const dirty = execFileSync("git", ["status", "--porcelain"], { cwd: root, encoding: "utf8" }).trim();
if (dirty) console.warn("工作区有未提交的修改，ZIP 只包含已提交的内容。");

const output = path.join(root, "dist", `rackora-${version}.zip`);
mkdirSync(path.dirname(output), { recursive: true });
// git archive 按本机时区写入条目的 DOS 时间，固定 UTC 才能与 GitHub Actions 打出的包逐字节一致
execFileSync("git", ["archive", "--format=zip", "--prefix=rackora/", "-o", output, "HEAD"], {
  cwd: root,
  env: { ...process.env, TZ: "UTC" },
});

const zip = readFileSync(output);
const sha256 = createHash("sha256").update(zip).digest("hex");
console.log(`${path.relative(root, output)}  ${(zip.length / 1024).toFixed(1)} KiB  sha256:${sha256}`);
