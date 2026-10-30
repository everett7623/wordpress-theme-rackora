/**
 * 文章编辑器侧栏的「Rackora」面板：系列、推广关系、核验日期等内容元数据。
 */
(function (wp, data) {
	"use strict";

	if (!wp || !data) return;

	const PluginDocumentSettingPanel = wp.editor?.PluginDocumentSettingPanel || wp.editPost?.PluginDocumentSettingPanel;
	if (!PluginDocumentSettingPanel) return;

	const { createElement: el } = wp.element;
	const { TextControl, TextareaControl, SelectControl } = wp.components;
	const { useSelect } = wp.data;
	const { useEntityProp } = wp.coreData;

	function MetaFields({ postType }) {
		const [meta, setMeta] = useEntityProp("postType", postType, "meta");
		if (!meta) return null;

		return Object.entries(data.postMeta)
			.filter(([, field]) => (field.postTypes ? field.postTypes.includes(postType) : postType === "post"))
			.map(([key, field]) => {
				const common = {
					key,
					label: field.label,
					help: field.help,
					value: meta[key] ?? "",
					onChange: (value) => setMeta({ ...meta, [key]: value }),
					__nextHasNoMarginBottom: true,
				};

				if (field.type === "textarea") {
					return el(TextareaControl, { ...common, rows: 3 });
				}
				if (field.type === "select") {
					return el(SelectControl, {
						...common,
						__next40pxDefaultSize: true,
						options: Object.entries(field.options).map(([value, label]) => ({ value, label })),
					});
				}
				return el(TextControl, {
					...common,
					__next40pxDefaultSize: true,
					type: field.type === "date" || field.type === "number" ? field.type : "text",
				});
			});
	}

	function RackoraMetaPanel() {
		const postType = useSelect((select) => select("core/editor")?.getCurrentPostType?.(), []);
		if (postType !== "post" && postType !== "page") return null;
		return el(
			PluginDocumentSettingPanel,
			{ name: "rackora-post-meta", title: data.i18n.metaPanel },
			el(MetaFields, { postType })
		);
	}

	wp.plugins.registerPlugin("rackora-post-meta", { render: RackoraMetaPanel, icon: null });
})(window.wp, window.rackoraEditor);
