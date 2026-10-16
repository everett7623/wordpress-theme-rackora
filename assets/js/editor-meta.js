/**
 * 文章编辑器侧栏的「Rackora」面板：系列、推广关系、核验日期等内容元数据。
 */
(function (wp, data) {
	"use strict";

	if (!wp || !data) return;

	const PluginDocumentSettingPanel = wp.editor?.PluginDocumentSettingPanel || wp.editPost?.PluginDocumentSettingPanel;
	if (!PluginDocumentSettingPanel) return;

	const { createElement: el } = wp.element;
	const { TextControl, SelectControl } = wp.components;
	const { useSelect } = wp.data;
	const { useEntityProp } = wp.coreData;

	function MetaFields() {
		const [meta, setMeta] = useEntityProp("postType", "post", "meta");
		if (!meta) return null;

		return Object.entries(data.postMeta).map(([key, field]) => {
			const common = {
				key,
				label: field.label,
				help: field.help,
				value: meta[key] ?? "",
				onChange: (value) => setMeta({ ...meta, [key]: value }),
				__nextHasNoMarginBottom: true,
				__next40pxDefaultSize: true,
			};

			if (field.type === "select") {
				return el(SelectControl, {
					...common,
					options: Object.entries(field.options).map(([value, label]) => ({ value, label })),
				});
			}
			return el(TextControl, { ...common, type: field.type === "date" || field.type === "number" ? field.type : "text" });
		});
	}

	function RackoraMetaPanel() {
		const postType = useSelect((select) => select("core/editor")?.getCurrentPostType?.(), []);
		if (postType !== "post") return null;
		return el(PluginDocumentSettingPanel, { name: "rackora-post-meta", title: data.i18n.metaPanel }, el(MetaFields));
	}

	wp.plugins.registerPlugin("rackora-post-meta", { render: RackoraMetaPanel, icon: null });
})(window.wp, window.rackoraEditor);
