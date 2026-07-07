<template>
  <AdminShell>
    <section class="glass-panel p-6 max-w-5xl mx-auto">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-black text-[#1B293C]">Tulis Berita Baru</h2>
          <p class="text-sm text-slate-500">Buat konten literasi yang menarik.</p>
        </div>
        <el-button @click="router.back()" plain>Batal & Kembali</el-button>
      </div>

      <el-form label-position="top" class="space-y-6">
        <el-form-item label="Judul Berita" required>
          <el-input 
            v-model="form.title" 
            placeholder="Masukkan judul berita yang menarik..." 
            size="large"
            class="custom-input"
          />
        </el-form-item>

        <el-form-item label="Isi Konten Berita" required>
          <div class="border rounded-xl overflow-hidden bg-white min-h-[400px]">
            <!-- Nama komponen otomatis dari folder components/Admin/AppRichTextEditor.client.vue -->
            <ClientOnly>
              <AdminAppRichTextEditor v-model="form.content" />
            </ClientOnly>
          </div>
        </el-form-item>
        
        <el-form-item label="Gambar Sampul (Thumbnail)" required>
          <el-upload
            action="#"
            list-type="picture-card"
            :auto-upload="false"
            :limit="1"
            accept="image/*"
            v-model:file-list="fileList"
            :on-change="handleImageChange"
            :on-remove="handleImageRemove"
          >
            <div class="flex flex-col items-center justify-center text-slate-400">
              <Icon icon="solar:camera-add-linear" class="text-2xl" />
              <span class="text-[10px] mt-1 font-bold">UPLOAD</span>
            </div>
          </el-upload>
        </el-form-item>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
          <el-button 
            type="primary" 
            color="#00a9c3" 
            size="large" 
            class="px-12 !rounded-2xl font-bold shadow-xl"
            :loading="loading" 
            @click="submitArticle"
          >
            Terbitkan Berita
          </el-button>
        </div>
      </el-form>
    </section>
  </AdminShell>
</template>

<script lang="ts" setup>
import { Icon } from "@iconify/vue";
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { ElMessage, ElNotification } from 'element-plus';
import AdminShell from '~/components/Admin/Shell.vue';
import { useApi } from '~/composables/useApi';

const router = useRouter();
const { apiFetch, uploadAttachment, getErrorMessage } = useApi();
const loading = ref(false);
const fileList = ref([]);

const form = reactive({
  title: '',
  content: '',
  thumbnail_id: null
});

const handleImageChange = async (file: any) => {
  if (!file.raw) return;
  try {
    const attachment = await uploadAttachment(file.raw, {
      attachmentableType: 'App\\Models\\Article',
      type: 'thumbnail',
    });
    form.thumbnail_id = attachment.id;
    ElMessage.success('Gambar berhasil diunggah');
  } catch (error) {
    fileList.value = [];
    ElMessage.error('Gagal mengunggah gambar');
  }
};

const handleImageRemove = () => {
  form.thumbnail_id = null;
  fileList.value = [];
};

const submitArticle = async () => {
  if (!form.title || !form.content) {
    return ElNotification({ title: 'Peringatan', message: 'Judul dan Isi konten wajib diisi!', type: 'warning' });
  }
  
  loading.value = true;
  try {
    await apiFetch('/articles', {
      method: 'POST',
      body: form
    });
    ElMessage.success('Berita ABC Jakarta berhasil diterbitkan!');
    router.push('/admin/articles');
  } catch (e) {
    ElMessage.error(getErrorMessage(e, 'Gagal menyimpan artikel'));
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { @apply bg-white rounded-[2rem] border border-slate-100 shadow-sm; }
.custom-input :deep(.el-input__wrapper) { @apply rounded-xl shadow-none border border-slate-200; }
</style>