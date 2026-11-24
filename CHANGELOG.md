# Changelog

本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。GitHub Release 的说明与此文件对应章节保持一致。

## [0.6.4] - 2026-11-24

### 修复

- 修复普通独立页面与索引页的 Open Graph `og:type` 被误标为 `article` 的问题（仅文章输出 `article`，关于页为 `profile`，其余独立页面统一为 `website`）
- 修复标题锚点注入与目录大纲正则可能误匹配 `<pre>`、`<script>`、`<style>`、`<textarea>` 内部字面量 `<h2>`/`<h3>` 标签的问题
- 修复站点统计区块（`rackora/site-stats`）统计分类总数时包含空分类导致与侧栏分类列表数量不一致的问题

### 性能与体验

- 优化区块注册启动开销：将 `window.rackoraEditor` 编辑器配置采集与序列化下沉至 `enqueue_block_editor_assets` 钩子，消除前台每次请求中 21 次多余的 `block.json` 磁盘读取与 JSON 编解码
- 精准收敛页面模板缓存（`rackora_template_pages`）失效条件，仅在 `page` 类型内容变更时刷新
- 精准收敛全站总阅读量缓存（`rackora_total_views`）失效条件，仅在内容状态跨越 `publish` 边界或已发布内容删除时清理
- 文章卡片在摘要为空时不再输出空白 `<p class="post-entry__excerpt">` 元素

## [0.6.3] - 2026-11-23

### 修复

- 修复文章标题含 `&`、引号等 HTML 实体时，服务端目录（TOC）二次转义乱码且锚点 ID 混入 `amp` 的问题，并改用 `mb_strtolower()` 正确处理非 ASCII 大写字母
- 修复密码保护文章（Password-Protected Post）在文章导语、`<meta name="description">` 与 JSON-LD 中泄露手动摘要或正文内容的问题
- 修复移动端多层嵌套评论回复缩进过深导致正文挤压变形的问题，窄屏下收敛缩进与头像列宽并限制深层嵌套继续缩进
- 修复友情链接页模板（`template-links`）缺失正文区块（`core/post-content`）导致后台填写的友链交换须知前台不显示的问题

### 性能与体验

- 优化文章列表封面图加载策略：当前页面首张封面图启用 `loading="eager"` 与 `fetchpriority="high"` 加速首屏 LCP，其余卡片保持懒加载
- 404 错误页新增居中站内搜索框，便于访客快速检索找回目标内容

## [0.6.2] - 2026-11-19

### 修复

- 修复深色模式下打印文章页面时，正文、标题与引用因沿用深色 CSS 变量导致白纸打印几乎不可见的问题，在 `@media print` 中强制重置全部打印配色变量
- 修复超长文章或文档页的吸顶侧栏（`.article-sidebar`）超出视口高度时底部目录被截断且无法滚动的问题
- 修复手动切换明暗模式后移动端 `<meta name="theme-color">` 状态栏颜色未联动更新以及与实际纸张背景存在色差的问题（统一为 `#f4f6f4` / `#111312` 并随配色实时同步）
- 修复代码块语言类名区分大小写导致大写标识（如 `language-YAML`、`language-Bash`）高亮失效的问题，并补充 `docker`、`toml`、`jsx`、`tsx`、`svg`、`xhtml`、`txt`、`plain` 等常用别名

### 性能与体验

- 优化文章目录滚动监听（`assets/js/post-toc.js`）：缓存目录链接集合并按激活章节 ID 变更去重，同时在桌面端侧栏可滚动时自动将当前激活目录项滚入视野内
- 优化返回顶部按钮（`assets/js/theme.js`）：接入 `requestAnimationFrame` 合帧与显隐阈值去重，减少高频滚动时的重复 DOM 操作

## [0.6.1] - 2026-11-17

### 修复

- 修复阅读量限流函数 `rackora_client_ip()` 直接信任外部代理头可能被伪造绕过防刷限制的问题；改为默认以 `REMOTE_ADDR` 为准，仅在来源属于内网/保留网段或通过 `rackora_trust_proxy_headers` 过滤器显式开启时解析代理转发头
- 修复文章目录区块（`rackora/post-toc`）在独立页面（Page）前台仍被跳过渲染的问题，现已支持在页面中展示目录
- 修复正文含短代码输出标题时，目录大纲（`rackora_post_heading_outline()`）未执行短代码导致标题与大纲映射错位的问题

### 性能

- 为 `rackora_settings_defaults()` 与 `rackora_detected_seo_plugin()` 增加请求内静态缓存，消除单次渲染中的重复计算与插件状态遍历
- 为分类/标签汇总区块（`rackora/term-index`）与友情链接区块（`rackora/friend-links`）的 `get_terms()` 查询关闭多余的 Term Meta 缓存预热
- 为后台分类编辑页「精选文章」列表查询关闭多余的 Post Meta 与分类法词元缓存预热

### 体验

- 图片放大灯箱支持点击放大后的图片本身直接关闭，并增加 `zoom-out` 光标提示
- 移动端目录抽屉开启时自动聚焦关闭按钮，通过关闭按钮或 `Escape` 退出时自动将焦点归还至目录触发按钮

## [0.6.0] - 2026-11-10

### 修复

- 修复侧栏多级分类列表（含子分类 `.children`）缺少 `flex-wrap: wrap` 导致父分类与子分类挤压在同一行的问题
- 修复静态页面（Page）未加载正文增强脚本导致代码块语法高亮与复制按钮、宽表格滚动包装、图片灯箱、分享栏复制按钮及页面阅读量统计失效的问题
- 修复文章包含同步样板（`<!-- wp:block`）或短代码输出标题时，目录大纲（`rackora_post_heading_outline`）因过早短路而误判为空的问题
- 修复文章状态变更（移入回收站、转为草稿）或删除时，全站总阅读量（`rackora_total_views`）Transient 缓存未同步清理的问题
- 修复归档标题区块（`rackora/archive-heading`）读取索引页面手动摘要时未剥离 HTML 标签的问题

### 性能

- 优化文章系列区块（`rackora/post-series`）查询：改为仅拉取文章 ID 数组（`fields => ids`）与必要元数据排序，切片后再按需预热目标文章缓存，避免拉取最多 100 篇文章的完整 `post_content`
- 优化归档时间线（`rackora/archives-timeline`）自定义查询，显式关闭无需使用的 Post Meta 缓存预热（`update_post_meta_cache => false`）
- 优化相关文章区块（`rackora/related-posts`）查询，显式关闭无需使用的 Post Meta 与分类法词元缓存预热

### 功能

- 日期归档页（`is_date()`）面包屑导航在站点存在「归档」索引页（`page-archives` 模板）时自动挂载归档页层级
- 日期归档页补齐 `CollectionPage` 与 `BreadcrumbList` Schema.org JSON-LD 结构化数据输出
- 阅读量服务端防刷限流新增 `rackora_client_ip()` 识别，兼容 Cloudflare / 反向代理转发头并提供 `rackora_client_ip` 过滤器

## [0.5.1] - 2026-11-09

### 修复

- 修复 `languages/zh_CN.mo` 编译产物丢失带 `msgctxt` 上下文、复数形式与转义引号词条的问题，并补齐 `zh_CN.po` 与 `rackora.pot` 缺失的 6 条历史词条
- 修复普通独立页面在使用 `<!--nextpage-->` 分页时 Canonical URL 被错误覆写为归档分页格式（`/page/N/`）的问题
- 修复朴素固定链接（`?m=YYYYMMDD`）下日期归档页无法解析年份与月份导致缺失全部 SEO 标签的问题
- 修复「静态首页 + 独立文章页（Posts Page）」模式下，文章页未读取自身页面的自定义 SEO 标题、SEO 描述、摘要、特色图片与 `noindex` 设置的问题
- 修复通过 REST API 或快速编辑单独变更 `_wp_page_template` 元数据时页面模板映射缓存未即时刷新的问题

### 变更

- 校验脚本 `scripts/validate-theme.mjs` 增加 PHP 翻译词条覆盖率检查以及 `zh_CN.po` 与 `zh_CN.mo` 键值一致性检查

## [0.5.0] - 2026-10-30

### 修复

- 修复文章无评论时最新评论区块在侧栏仍渲染空白占位，且当侧栏无内容时自动折叠单栏布局
- 修复文章卡片与文章元信息在「仅统计文章阅读数」开启后仍对页面渲染阅读数
- 修复站点总阅读数未过滤已发布状态以及未遵从「仅统计文章阅读数」过滤条件，并在设置保存时及时清理总阅读数缓存
- 修复归档时间线年份标题在含 WordPress 管理栏时吸顶偏移重叠（补齐 `--rack-admin-bar`）
- 修复多页页面在静态首页模式下分页检测回退及归档模板多页标题分页后缀缺失

### 性能

- 优化页面模板映射为单次批量预解析并接入 Transient 缓存与文章保存/删除联动失效机制
- 优化字数估算与阅读时长计算，避免重复正则剥离正文字符
- 优化文章大纲解析与正文标题锚点注入，在正文不含标题标签时直接短路跳过正则匹配
- 优化默认 Logo 回退图片输出属性，增加 `fetchpriority="high"` 提高 LCP 得分
- 优化目录滚动监听逻辑，缓存页头高度避免每帧重复触发 `getComputedStyle` 样式重排

### 功能

- 文章与页面侧栏新增「SEO 描述」（`_rackora_seo_description`）自定义元字段，并在主题 SEO 输出中优先使用
- 独立页面现支持配置「SEO 标题」、「SEO 描述」与「搜索索引策略」（noindex）
- 结构化数据增强：文章输出 `wordCount`、`articleSection` 与 `keywords`；分类/标签归档输出 `CollectionPage` + `BreadcrumbList`；作者页输出 `ProfilePage`
- 社交卡片增强：自动补齐 `og:image:alt` 与 `twitter:image:alt`；单篇文章输出 `article:section` 与 `article:tag`

## [0.4.1] - 2026-10-30

### 变更

- 对齐 Halo 版主题封面与品牌图标：更新 `screenshot.png`（1200×900）、`docs/marketplace-cover.png` 与多尺寸品牌 Logo（`docs/rackora-logo-*.png`、`docs/rackora-mark.svg`）

## [0.4.0] - 2026-10-30

只收各站点都适用的缺口：无 SEO 插件时主题应输出完整描述与标题；有插件时 SEO 让位，统计仍由站点自己开关。站点运营向的草稿链隐藏、默认 noindex 作者页、检测到插件就关统计，不进入通用主题。

### 修复

- 主题 SEO 补齐缺失的 `<meta name="description">`（此前只写了 og/twitter description）
- `og:locale` 改为 `zh-CN` 这类连字符形式
- `robots.txt` 缺少 `User-agent` 分组时补齐 WordPress 默认规则

### 功能

- 文章侧栏增加「SEO 标题」，用于文档标题与 Open Graph（有 SEO 插件时忽略）
- 搜索结果 `noindex,follow`；主题接管 SEO 时 `robots.txt` 屏蔽 `/?s=` 与 `/search/`
- 统计可选排除已登录用户（默认开启，与常见分析工具一致）

## [0.3.0] - 2026-10-29

### 修复

- 静态首页的 Open Graph `og:type` 不再误标为 `article`，统一为 `website`
- 文档标题过滤器仅在主题 SEO 生效时运行，避免与 SEO 插件抢标题
- SEO 插件检测改为优先判断插件是否激活，再回退到运行时常量
- 分享栏增加「使用主题默认」开关：可显式关闭全部平台；设置页全不选不再被误当成「未提交」
- 阅读数 REST 接口按 IP + 文章限流（1 小时），降低刷量风险

### 性能

- 文章脚本（目录 / 代码高亮 / 阅读进度）仅在 `post` 类型加载
- 分类汇总一次查询后按父子分组，消除 N+1
- 归档时间线预热本页分类缓存

### 功能

- 文章目录改为服务端输出大纲，前台脚本负责锚点、高亮与移动端抽屉
- WebSite JSON-LD 增加 `SearchAction`
- 文章侧栏可设置「暂不索引」；分类支持索引策略（对齐标签）
- 设置项「仅统计文章阅读数」

## [0.2.0] - 2026-10-28

### 功能

- 去掉「网站地区」与 `geo.region` 输出
- 主题设置新增：建站日期、ICP / 公网安备、默认许可、默认分享平台、推广披露文案
- 社交主页改为平台预设槽 +「其他」地址，仍输出为 Organization `sameAs`；兼容旧版每行一个 URL
- 备案、站点统计、版权声明、分享栏、推广位在区块属性为空时回退到主题设置

## [0.1.0] - 2026-10-16

### 新增

- 首个面向 WordPress 的 Rackora 区块主题发布
- 全站编辑模板与模式、自定义区块、浅色 / 深色配色、页面级 SEO 与可选分析脚本

[0.5.1]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.5.1
[0.5.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.5.0
[0.4.1]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.4.1
[0.4.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.4.0
[0.3.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.3.0
[0.2.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.2.0
[0.1.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.1.0
