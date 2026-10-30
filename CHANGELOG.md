# Changelog

本项目遵循 [语义化版本](https://semver.org/lang/zh-CN/)。GitHub Release 的说明与此文件对应章节保持一致。

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

[0.5.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.5.0
[0.4.1]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.4.1
[0.4.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.4.0
[0.3.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.3.0
[0.2.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.2.0
[0.1.0]: https://github.com/everett7623/wordpress-theme-rackora/releases/tag/v0.1.0
