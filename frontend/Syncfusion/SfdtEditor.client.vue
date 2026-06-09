<template>
  <div class="sfdt-editor">
    <ejs-documenteditorcontainer
      ref="container"
      :height="height"
      :enableToolbar="true"
      :serviceUrl="serviceUrl"
    />
  </div>
</template>

<script>
import { DocumentEditorContainerComponent, Toolbar } from '@syncfusion/ej2-vue-documenteditor'

export default {
  name: 'SfdtEditor',
  components: {
    'ejs-documenteditorcontainer': DocumentEditorContainerComponent,
  },
  provide: {
    DocumentEditorContainer: [Toolbar],
  },
  props: {
    modelValue: {
      type: String,
      default: '',
    },
    height: {
      type: String,
      default: '520px',
    },
    serviceUrl: {
      type: String,
      default: '',
    },
  },
  emits: ['update:modelValue'],
  data() {
    return {
      isReady: false,
      isInternalUpdate: false,
      lastLoaded: '',
    }
  },
  mounted() {
    this.initializeEditor()
  },
  watch: {
    modelValue(nextValue) {
      if (!this.isReady || this.isInternalUpdate) return
      if (nextValue === this.lastLoaded) return
      this.loadContent(nextValue)
    },
  },
  methods: {
    getContainer() {
      return this.$refs.container?.ej2Instances || null
    },
    loadContent(value) {
      const container = this.getContainer()
      const editor = container?.documentEditor
      if (!editor) return
      if (!value) {
        editor.openBlank()
        this.lastLoaded = ''
        return
      }
      editor.open(value)
      this.lastLoaded = value
    },
    initializeEditor() {
      const container = this.getContainer()
      const editor = container?.documentEditor
      if (!editor) return
      this.loadContent(this.modelValue)
      this.isReady = true
      editor.contentChange = () => {
        const sfdt = editor.serialize()
        this.isInternalUpdate = true
        this.lastLoaded = sfdt
        this.$emit('update:modelValue', sfdt)
        this.$nextTick(() => {
          this.isInternalUpdate = false
        })
      }
    },
  },
}
</script>

<style scoped>
@import "~/assets/css/syncfusion.css";

.sfdt-editor :deep(.e-documenteditorcontainer) {
  border: 1px solid var(--slate-200);
  border-radius: 16px;
  overflow: hidden;
  background: #ffffff;
}
</style>
