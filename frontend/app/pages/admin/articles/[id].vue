<template>
  <AdminShell>
    <section class="glass-panel p-6 max-w-5xl mx-auto" v-loading="loadingData">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-black text-[#1B293C]">Edit Berita</h2>
          <p class="text-sm text-slate-500">Perbarui konten informasi untuk website ABC Jakarta.</p>
        </div>
        <el-button @click="$router.back()" plain>Kembali</el-button>
      </div>

      <el-form label-position="top" class="space-y-6">
        <el-form-item label="Judul Berita" required>
          <el-input v-model="form.title" size="large" class="custom-input" />
        </el-form-item>

        <!-- RINGKASAN / EXCERPT (BARU) -->
        <el-form-item label="Ringkasan Singkat (Excerpt)" required>
          <el-input 
            v-model="form.excerpt" 
            type="textarea" 
            :rows="3" 
            maxlength="200" 
            show-word-limit 
          />
        </el-form-item>

        <el-form-item label="Konten Berita" required>
          <div class="border rounded-xl overflow-hidden bg-white">
            <ClientOnly>
              <AdminAppRichTextEditor v-model="form.content" />
            </ClientOnly>
          </div>
        </el-form-item>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <el-form-item label="Ganti Gambar Sampul">
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

          <!-- STATUS (BARU) -->
          <el-form-item label="Status Publikasi" required>
            <div class="p-4 border border-slate-100 rounded-xl bg-slate-50/50">
              <el-radio-group v-model="form.status" class="flex flex-col gap-3">
                <el-radio label="draft" size="large" border class="!ml-0 w-full bg-white">Draft</el-radio>
                <el-radio label="published" size="large" border class="!ml-0 w-full bg-white text-[#00a9c3]">Published</el-radio>
              </el-radio-group>
            </div>
          </el-form-item>
        </div>

        <div class="flex justify-end mt-10 border-t pt-6">
          <el-button type="primary" color="#00a9c3" size="large" class="px-10 !rounded-2xl font-bold shadow-lg" @click="updateArticle" :loading="busy">
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

const form = reactive({ 
  title: '', 
  excerpt: '', // Baru
  content: '', 
  status: 'draft', // Baru
  thumbnail_id: null 
});

const loadArticle = async () => {
  try {
    const res: any = await apiFetch(`/articles/${route.params.id}`);
    form.title = res.title;
    form.excerpt = res.excerpt || ''; // Load data baru
    form.content = res.content;
    form.status = res.status || 'draft'; // Load data baru
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
  try {
    const attachment = await uploadAttachment(file.raw, { attachmentableType: 'App\\Models\\Article', type: 'thumbnail' });
    form.thumbnail_id = attachment.id;
    ElMessage.success('Gambar diperbarui');
  } catch (e) { ElMessage.error('Gagal unggah'); }
};

const updateArticle = async () => {
  if (!form.title || !form.content || !form.excerpt) {
    return ElMessage.warning('Semua kolom wajib diisi!');
  }
  busy.value = true;
  try {
    await apiFetch(`/articles/${route.params.id}`, { method: 'PUT', body: form });
    ElMessage.success('Berita berhasil diperbarui');
    router.push('/admin/articles');
  } catch (e) {
    ElMessage.error('Gagal update');
  } finally {
    busy.value = false;
  }
};

onMounted(loadArticle);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply bg-white rounded-[2rem] border border-slate-100 shadow-sm; }
.custom-input :deep(.el-input__wrapper) { @apply rounded-xl shadow-none border border-slate-200; }
</style>