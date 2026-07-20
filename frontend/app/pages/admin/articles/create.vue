<template>
  <AdminShell>
    <section class="glass-panel p-6 max-w-5xl mx-auto">
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-black text-[#1B293C]">Tulis Berita Baru</h2>
          <p class="text-sm text-slate-500">Buat konten literasi yang menarik untuk jemaat.</p>
        </div>
        <el-button @click="router.back()" plain>Batal & Kembali</el-button>
      </div>

      <el-form label-position="top" class="space-y-6">
        <!-- JUDUL -->
        <el-form-item label="Judul Berita" required>
          <el-input 
            v-model="form.title" 
            placeholder="Masukkan judul berita yang menarik..." 
            size="large"
            class="custom-input"
          />
        </el-form-item>

        <!-- RINGKASAN / EXCERPT (BARU) -->
        <el-form-item label="Ringkasan Singkat (Excerpt)" required>
          <template #label>
            <div class="flex items-center gap-2">
              <span>Ringkasan Singkat (Excerpt)</span>
              <el-tooltip content="Muncul di halaman depan website (Landing Page)" placement="top">
                <Icon icon="solar:info-circle-linear" class="text-slate-400" />
              </el-tooltip>
            </div>
          </template>
          <el-input 
            v-model="form.excerpt" 
            type="textarea" 
            :rows="3" 
            maxlength="200" 
            show-word-limit 
            placeholder="Tulis 1-2 kalimat ringkasan isi berita..." 
          />
        </el-form-item>

        <!-- KONTEN EDITOR -->
        <el-form-item label="Isi Konten Berita" required>
          <div class="border rounded-xl overflow-hidden bg-white min-h-[400px]">
            <ClientOnly>
              <AdminAppRichTextEditor v-model="form.content" />
            </ClientOnly>
          </div>
        </el-form-item>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- UPLOAD GAMBAR -->
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

          <!-- STATUS (BARU) -->
          <el-form-item label="Status Publikasi" required>
            <div class="p-4 border border-slate-100 rounded-xl bg-slate-50/50">
              <el-radio-group v-model="form.status" class="flex flex-col gap-3">
                <el-radio label="draft" size="large" border class="!ml-0 w-full bg-white">
                  <div class="flex flex-col">
                    <span class="font-bold">Simpan sebagai Draft</span>
                    <span class="text-[10px] text-slate-400">Berita tidak akan muncul di website publik.</span>
                  </div>
                </el-radio>
                <el-radio label="published" size="large" border class="!ml-0 w-full bg-white">
                  <div class="flex flex-col">
                    <span class="font-bold text-[#00a9c3]">Terbitkan (Live)</span>
                    <span class="text-[10px] text-slate-400">Berita langsung tayang di Jendela Literasi.</span>
                  </div>
                </el-radio>
              </el-radio-group>
            </div>
          </el-form-item>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
          <el-button 
            type="primary" 
            color="#00a9c3" 
            size="large" 
            class="px-12 !rounded-2xl font-bold shadow-xl"
            :loading="loading" 
            @click="submitArticle"
          >
            Simpan Berita
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
  excerpt: '', // Baru
  content: '',
  status: 'draft', // Baru (Default Draft)
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

const handleImageRemove = () => { form.thumbnail_id = null; fileList.value = []; };

const submitArticle = async () => {
  if (!form.title || !form.content || !form.excerpt) {
    return ElNotification({ title: 'Peringatan', message: 'Judul, Ringkasan, dan Isi wajib diisi!', type: 'warning' });
  }
  
  loading.value = true;
  try {
    await apiFetch('/articles', { method: 'POST', body: form });
    ElMessage.success('Berita ABC Jakarta berhasil disimpan!');
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