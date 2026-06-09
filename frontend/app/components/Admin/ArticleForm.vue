<template>
  <AdminShell>
    <section class="glass-panel p-4 sm:p-6 mb-6">
      <div class="flex items-center gap-3 mb-6">
        <el-button @click="goBack" text>
          <Icon icon="solar:arrow-left-outline" class="text-xl" />
        </el-button>
        <div>
          <h2 class="section-title mb-0">
            {{ isEdit ? "Edit Article" : "Create Article" }}
          </h2>
          <p class="text-sm text-[var(--slate-500)] mt-1">
            Fill in the details for your article.
          </p>
        </div>
      </div>

      <el-form ref="formRef" :model="form" :rules="rules" label-position="top">
        <el-form-item label="Title" prop="title">
          <el-input
            v-model="form.title"
            placeholder="Article Title"
            size="large"
          />
        </el-form-item>

        <el-form-item label="Description" prop="description">
          <client-only>
            <AdminAppRichTextEditor
              v-model="form.description"
              :upload-url="`${apiBase}/api/articles/upload-image`"
              :xsrf-token="getXsrfToken() || ''"
            />
            <template #fallback>
              <div
                class="h-64 flex items-center justify-center bg-gray-50 border border-gray-200 rounded"
              >
                <el-icon class="is-loading text-2xl text-gray-400"
                  ><Loading
                /></el-icon>
              </div>
            </template>
          </client-only>
        </el-form-item>

        <el-form-item label="Thumbnail" prop="thumbnail">
          <el-upload
            action="#"
            list-type="picture-card"
            :auto-upload="false"
            :limit="1"
            accept="image/*"
            v-model:file-list="thumbnailFiles"
            :on-change="handleThumbnailChange"
            :on-remove="handleThumbnailRemove"
          >
            <div
              class="flex h-20 w-20 flex-col items-center justify-center text-xs text-[var(--slate-500)]"
            >
              <span class="text-lg font-semibold text-[var(--blue-900)]"
                >+</span
              >
              <span>Upload</span>
            </div>
          </el-upload>
        </el-form-item>

        <div class="flex justify-end mt-8">
          <el-button @click="goBack">Cancel</el-button>
          <el-button type="primary" @click="submit" :loading="saving">
            {{ isEdit ? "Update Article" : "Publish Article" }}
          </el-button>
        </div>
      </el-form>
    </section>
  </AdminShell>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { useRouter } from "vue-router";
import { ElMessage, ElNotification } from "element-plus";
import type {
  FormInstance,
  FormRules,
  UploadFile,
  UploadUserFile,
} from "element-plus";
import { Loading } from "@element-plus/icons-vue";
import AdminShell from "~/components/Admin/Shell.vue";
import { useApi } from "~/composables/useApi";
import type { ArticleApi, Article } from "~/types/admin";

const props = defineProps<{
  isEdit?: boolean;
  initialData?: Article;
}>();

const router = useRouter();
const { apiFetch, apiBase, getXsrfToken, uploadAttachment, getErrorMessage } =
  useApi();

const formRef = ref<FormInstance>();
const saving = ref(false);

const form = ref({
  title: props.initialData?.title || "",
  description: props.initialData?.description || "",
  thumbnail: props.initialData?.thumbnail || (null as UploadUserFile | null),
});

const thumbnailFiles = ref<UploadFile[]>(
  props.initialData?.thumbnail ? [props.initialData.thumbnail as any] : []
);

const rules = reactive<FormRules>({
  title: [
    { required: true, message: "Please input article title", trigger: "blur" },
    { min: 3, max: 255, message: "Length should be 3 to 255", trigger: "blur" },
  ],
  thumbnail: [
    {
      required: true,
      validator: (_rule, value, callback) => {
        if (!value) callback(new Error("Thumbnail harus diisi!"));
        else callback();
      },
      trigger: ["change", "blur"],
    },
  ],
});

const handleThumbnailChange = (uploadFile: UploadFile) => {
  thumbnailFiles.value = [uploadFile];
  form.value.thumbnail = uploadFile as unknown as UploadUserFile;
};
const handleThumbnailRemove = () => {
  thumbnailFiles.value = [];
  form.value.thumbnail = null;
};

const isUuid = (value: string) =>
  /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i.test(value);

const resolveAttachmentId = (file: any | null): string | null => {
  if (!file) return null;
  const fallbackStr = String(file.attachmentId || file.uid || "");
  if (isUuid(fallbackStr)) return fallbackStr;

  if (
    file.response &&
    typeof file.response === "object" &&
    "id" in file.response
  ) {
    return String(file.response.id);
  }
  return null;
};

const emit = defineEmits(["save"]);

const submit = async () => {
  const formEl = formRef.value;
  if (!formEl) return;

  await formEl.validate(async (valid) => {
    if (valid) {
      saving.value = true;
      try {
        if (form.value.thumbnail && form.value.thumbnail.raw) {
          const fileRaw = form.value.thumbnail.raw;
          const res = await uploadAttachment(fileRaw, {
            attachmentableType: "article",
            type: "thumbnail",
          });
          form.value.thumbnail.url = res.path;
          form.value.thumbnail.uid = res.id as any;
          (form.value.thumbnail as any).response = res;
        }

        const payload = {
          title: form.value.title,
          description: form.value.description,
          thumbnail_id: resolveAttachmentId(form.value.thumbnail),
        };

        emit("save", payload);
      } catch (error) {
        console.error(error);
        const errorMessage = getErrorMessage(
          error,
          "Failed to prepare article thumbnail data."
        );
        ElMessage.error(errorMessage);
        saving.value = false;
      }
    } else {
      ElNotification({
        title: "Error",
        message: "Harap lengkapi form dengan benar, termasuk Thumbnail!",
        type: "error",
      });
    }
  });
};

const goBack = () => {
  router.push("/admin/articles");
};
</script>
