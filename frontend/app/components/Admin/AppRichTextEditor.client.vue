<template>
  <div class="richtexteditor-wrapper w-full relative">
    <ejs-richtexteditor
      ref="rteObj"
      v-model="content"
      :toolbarSettings="toolbarSettings"
      :insertImageSettings="insertImageSettings"
      @imageUploading="onImageUploading"
      @imageUploadSuccess="onImageUploadSuccess"
      :height="height"
    >
    </ejs-richtexteditor>
  </div>
</template>

<script setup lang="ts">
import { computed, provide, shallowRef } from "vue";
import {
  RichTextEditorComponent as EjsRichtexteditor,
  Toolbar,
  Link,
  Image,
  HtmlEditor,
  QuickToolbar,
  Table,
  PasteCleanup,
} from "@syncfusion/ej2-vue-richtexteditor";
import { registerLicense } from "@syncfusion/ej2-base";

registerLicense(
  "Ngo9BigBOggjHTQxAR8/V1JFaF5cXGRCf1FpRmJGdld5fUVHYVZUTXxaS00DNHVRdkdmWH5cdnRWQmZfUkF0X0dWYEg="
);

provide("richtexteditor", [
  Toolbar,
  Link,
  Image,
  HtmlEditor,
  QuickToolbar,
  Table,
  PasteCleanup,
]);

const props = defineProps<{
  modelValue: string;
  uploadUrl: string;
  xsrfToken: string;
  height?: string | number;
}>();

const emit = defineEmits(["update:modelValue"]);

const content = computed({
  get: () => props.modelValue,
  set: (val) => emit("update:modelValue", val),
});

const height = computed(() => props.height || 650);

const rteObj = shallowRef();

const toolbarSettings = {
  items: [
    "Bold",
    "Italic",
    "Underline",
    "StrikeThrough",
    "FontName",
    "FontSize",
    "FontColor",
    "BackgroundColor",
    "LowerCase",
    "UpperCase",
    "|",
    "Formats",
    "Alignments",
    "OrderedList",
    "UnorderedList",
    "Outdent",
    "Indent",
    "|",
    "CreateLink",
    "Image",
    "CreateTable",
    "|",
    "ClearFormat",
    "Print",
    "SourceCode",
    "FullScreen",
    "|",
    "Undo",
    "Redo",
  ],
};

const insertImageSettings = computed(() => {
  return {
    saveUrl: props.uploadUrl,
  };
});

const onImageUploading = (args: any) => {
  // Pass XSRF token and credentials
  args.currentRequest.setRequestHeader("X-XSRF-TOKEN", props.xsrfToken || "");
  args.currentRequest.withCredentials = true;
};

const onImageUploadSuccess = (args: any) => {
  try {
    const response = JSON.parse(args.e.currentTarget.response);
    if (response && response.url) {
      if (args.operation === "upload") {
        const fileUploadName = args.file.name;
        // Wait a tick for RTE to insert the image tag
        setTimeout(() => {
          if (!rteObj.value) return;
          let changed = false;
          const imgElements =
            rteObj.value.$el.querySelectorAll("img.e-rte-image");
          imgElements.forEach((img: HTMLImageElement) => {
            if (
              img.src.includes("blob:") ||
              img.getAttribute("alt") === fileUploadName ||
              img.src.includes(fileUploadName)
            ) {
              img.src = response.url;
              changed = true;
            }
          });

          if (changed) {
            const contentDiv = rteObj.value.$el.querySelector(".e-content");
            if (contentDiv) {
              const finalHtml = contentDiv.innerHTML;
              if (rteObj.value.ej2Instances) {
                rteObj.value.ej2Instances.value = finalHtml;
              }
              emit("update:modelValue", finalHtml);
            }
          }
        }, 300); // Increased timeout to ensure Syncfusion finished DOM rendering before updating
      }
    }
  } catch (e) {
    console.error("Failed to parse uploaded image response", e);
  }
};
</script>

<style>
@import "@syncfusion/ej2-base/styles/material.css";
@import "@syncfusion/ej2-icons/styles/material.css";
@import "@syncfusion/ej2-buttons/styles/material.css";
@import "@syncfusion/ej2-splitbuttons/styles/material.css";
@import "@syncfusion/ej2-inputs/styles/material.css";
@import "@syncfusion/ej2-lists/styles/material.css";
@import "@syncfusion/ej2-navigations/styles/material.css";
@import "@syncfusion/ej2-popups/styles/material.css";
@import "@syncfusion/ej2-richtexteditor/styles/material.css";

.richtexteditor-wrapper {
  overflow: hidden;
}

/* Adjust Syncfusion RTE styles to match the application theme */
.e-richtexteditor {
  border-radius: 0.5rem;
  border-color: #e5e7eb;
}
.e-richtexteditor .e-rte-content {
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
}
.e-richtexteditor .e-toolbar {
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}
</style>
