<template>
  <AdminShell>
    <section class="glass-panel p-6 max-w-5xl mx-auto" v-loading="loadingData">
      <div class="mb-8 flex items-center justify-between">
        <h2 class="text-2xl font-black text-[#1B293C]">Edit Berita</h2>
        <el-button @click="$router.back()">Kembali</el-button>
      </div>

      <el-form label-position="top">
        <el-form-item label="Judul Berita" required>
          <el-input v-model="form.title" />
        </el-form-item>

        <el-form-item label="Konten Berita" required>
          <ClientOnly>
            <AdminAppRichTextEditor v-model="form.content" />
          </ClientOnly>
        </el-form-item>

        <el-form-item label="Ganti Gambar">
          <el-upload
            action="#"
            list-type="picture-card"
            :auto-upload="false"
            :limit="1"
            v-model:file-list="fileList"
            :on-change="handleImageChange"
          >
            <Icon icon="solar:camera-add-linear" class="text-2xl" />
          </el-upload>
        </el-form-item>

        <div class="flex justify-end mt-10">
          <el-button type="primary" color="#00a9c3" size="large" @click="updateArticle" :loading="busy">
            Simpan Perubahan
          </el-button>
        </div>
      </el-form>
    </section>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ElMessage } from 'element-plus';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const route = useRoute();
const router = useRouter();
const { apiFetch, uploadAttachment } = useApi();
const loadingData = ref(true);
const busy = ref(false);
const fileList = ref([]);

const form = reactive({ title: '', content: '', thumbnail_id: null });

// 1. Ambil data berita yang sudah ada
const loadArticle = async () => {
  try {
    const res: any = await apiFetch(`/articles/${route.params.id}`);
    form.title = res.title;
    form.content = res.content;
    form.thumbnail_id = res.thumbnail_id;
    if (res.thumbnail) {
        fileList.value = [{ name: res.thumbnail.name, url: res.thumbnail.url }];
    }
  } catch (e) {
    ElMessage.error('Berita tidak ditemukan');
    router.push('/admin/articles');
  } finally {
    loadingData.value = false;
  }
};

const handleImageChange = async (file: any) => {
  const attachment = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Article', type: 'thumbnail' });
  form.thumbnail_id = attachment.id;
};

const updateArticle = async () => {
  busy.value = true;
  try {
    await apiFetch(`/articles/${route.params.id}`, { method: 'PUT', body: form });
    ElMessage.success('Berhasil diperbarui');
    router.push('/admin/articles');
  } catch (e) {
    ElMessage.error('Gagal update');
  } finally {
    busy.value = false;
  }
};

onMounted(loadArticle);
</script>