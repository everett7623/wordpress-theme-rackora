/**
 * Rackora 区块编辑脚本：主题区块使用服务端渲染预览，属性面板按 block.json 自动生成；
 * 推广位区块使用子区块编辑推广卡片。
 */
(function (wp, data) {
	"use strict";

	if (!wp || !data) return;

	const { registerBlockType, getBlockType } = wp.blocks;
	const { createElement: el, Fragment } = wp.element;
	const { InspectorControls, useBlockProps, useInnerBlocksProps, InnerBlocks, MediaUpload, MediaUploadCheck } =
		wp.blockEditor;
	const { PanelBody, ToggleControl, TextControl, TextareaControl, SelectControl, RangeControl, CheckboxControl, Button, Placeholder, Disabled } =
		wp.components;
	const ServerSideRender = wp.serverSideRender;
	const { useSelect } = wp.data;

	const TEXTAREA_KEYS = ["description", "disclosure"];
	const SLOT = "rackora/ad-slot";

	function optionLabel(key, value) {
		return data.options[`${key}:${value}`] || value;
	}

	function ImageField({ label, value, onChange }) {
		return el(
			"div",
			{ className: "rackora-image-field" },
			el("p", null, label),
			value ? el("img", { src: value, alt: "", style: { maxWidth: "100%", height: "auto" } }) : null,
			el(
				MediaUploadCheck,
				null,
				el(MediaUpload, {
					allowedTypes: ["image"],
					onSelect: (media) => onChange(media.url),
					render: ({ open }) => el(Button, { variant: "secondary", onClick: open }, data.i18n.chooseImage),
				}),
			),
			value ? el(Button, { variant: "link", isDestructive: true, onClick: () => onChange("") }, data.i18n.removeImage) : null,
		);
	}

	function TargetsField({ label, value, onChange }) {
		const selected = Array.isArray(value) ? value : [];
		return el(
			"fieldset",
			{ className: "rackora-share-targets" },
			el("legend", null, label),
			Object.entries(data.share).map(([key, text]) =>
				el(CheckboxControl, {
					key,
					label: text,
					checked: selected.includes(key),
					onChange: (checked) => onChange(checked ? [...selected, key] : selected.filter((item) => item !== key)),
					__nextHasNoMarginBottom: true,
				}),
			),
		);
	}

	function Field({ name, schema, value, onChange }) {
		const label = data.labels[name] || name;
		const common = { label, __nextHasNoMarginBottom: true };

		if (name === "targets") return el(TargetsField, { label, value, onChange });
		if (name === "image") return el(ImageField, { label, value, onChange });
		if (schema.type === "boolean") return el(ToggleControl, { ...common, checked: Boolean(value), onChange });
		if (schema.type === "number" || schema.type === "integer") {
			return el(RangeControl, {
				...common,
				value,
				min: schema.minimum ?? 1,
				max: schema.maximum ?? 20,
				onChange: (next) => onChange(next ?? schema.default),
				__next40pxDefaultSize: true,
			});
		}
		if (Array.isArray(schema.enum)) {
			return el(SelectControl, {
				...common,
				value,
				options: schema.enum.map((option) => ({ value: option, label: optionLabel(name, option) })),
				onChange,
				__next40pxDefaultSize: true,
			});
		}
		if (TEXTAREA_KEYS.includes(name)) return el(TextareaControl, { ...common, value: value || "", onChange });
		return el(TextControl, { ...common, value: value || "", onChange, __next40pxDefaultSize: true });
	}

	function Inspector({ name, attributes, setAttributes }) {
		const keys = data.inspector[name] || [];
		if (!keys.length) return null;
		const schema = getBlockType(name).attributes;
		const shareKeys =
			name === "rackora/share-bar"
				? attributes.useThemeShare
					? keys.filter((key) => key === "useThemeShare")
					: keys
				: keys;

		return el(
			InspectorControls,
			null,
			el(
				PanelBody,
				{ title: data.i18n.settings },
				shareKeys.map((key) =>
					el(Field, {
						key,
						name: key,
						schema: schema[key] || {},
						value: attributes[key],
						onChange: (next) => {
							if (name === "rackora/share-bar" && key === "targets") {
								setAttributes({ targets: next, useThemeShare: false });
								return;
							}
							setAttributes({ [key]: next });
						},
					}),
				),
			),
		);
	}

	function Preview({ name, attributes }) {
		// 文章编辑器中传入当前文章 ID，站点编辑器的模板没有具体文章，渲染为空时显示占位。
		const postId = useSelect((select) => select("core/editor")?.getCurrentPostId?.(), []);
		const type = getBlockType(name);
		const placeholder = () => el(Placeholder, { icon: type.icon?.src, label: type.title, instructions: data.i18n.frontendOnly });

		return el(
			Disabled,
			null,
			el(ServerSideRender, {
				block: name,
				attributes,
				skipBlockSupportAttributes: true,
				urlQueryArgs: Number.isInteger(postId) ? { post_id: postId } : {},
				EmptyResponsePlaceholder: placeholder,
				ErrorResponsePlaceholder: placeholder,
			}),
		);
	}

	function GenericEdit(props) {
		return el(Fragment, null, el(Inspector, props), el("div", useBlockProps(), el(Preview, props)));
	}

	function SlotEdit(props) {
		const innerBlocksProps = useInnerBlocksProps(useBlockProps({ className: "ad-slot" }), {
			allowedBlocks: ["rackora/ad-card"],
			template: [["rackora/ad-card"]],
		});
		return el(Fragment, null, el(Inspector, props), el("section", innerBlocksProps));
	}

	for (const name of Object.keys(data.inspector)) {
		registerBlockType(name, {
			edit: name === SLOT ? SlotEdit : GenericEdit,
			save: name === SLOT ? () => el(InnerBlocks.Content) : () => null,
		});
	}
})(window.wp, window.rackoraEditor);
