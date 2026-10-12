<template>
  <AdminShell>
    <section class="glass-panel p-6 max-w-5xl mx-auto" v-loading="loadingData">
      <!-- HEADER -->
      <div class="mb-8 flex items-center justify-between">
        <div>
          <h2 class="text-2xl font-black text-[#1B293C]">Edit Berita</h2>
          <p class="text-sm text-slate-500">Perbarui konten informasi untuk website ABC Jakarta.</p>
        </div>
        <el-button @click="$router.back()" plain class="!rounded-xl font-semibold">
          Kembali
        </el-button>
      </div>

      <el-form label-position="top" class="space-y-6">
        <!-- 1. JUDUL BERITA -->
        <el-form-item label="Judul Berita" required>
          <el-input 
            v-model="form.title" 
            placeholder="Masukkan judul berita yang menarik..."
            size="large" 
            class="custom-input" 
          />
        </el-form-item>

        <!-- 2. RINGKASAN / EXCERPT DENGAN TOOLTIP -->
        <el-form-item required>
          <template #label>
            <div class="flex items-center gap-2">
              <span class="font-bold text-slate-700">Ringkasan Singkat (Excerpt)</span>
              <el-tooltip content="Muncul di kartu berita halaman depan website (Landing Page)" placement="top">
                <Icon icon="solar:info-circle-linear" class="text-slate-400 cursor-pointer" />
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

        <!-- 3. KONTEN BERITA (RICH TEXT EDITOR) -->
        <el-form-item label="Konten Berita" required>
          <div class="border rounded-xl overflow-hidden bg-white min-h-[400px]">
            <ClientOnly>
              <AdminAppRichTextEditor v-model="form.content" />
            </ClientOnly>
          </div>
        </el-form-item>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- 4. GANTI GAMBAR SAMPUL -->
          <el-form-item label="Ganti Gambar Sampul (Thumbnail)">
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
                <span class="text-[10px] mt-1 font-bold">GANTI FOTO</span>
              </div>
            </el-upload>
          </el-form-item>

          <!-- 5. STATUS PUBLIKASI (SELARAS DENGAN HALAMAN TAMBAH) -->
          <el-form-item label="Status Publikasi" required>
            <div class="p-4 border border-slate-100 rounded-xl bg-slate-50/50">
              <el-radio-group v-model="form.status" class="flex flex-col gap-3 w-full">
                <!-- Pilihan 1: Draft -->
                <el-radio value="draft" label="draft" size="large" border class="!ml-0 !h-auto py-3 px-4 w-full bg-white !rounded-xl transition-all">
                  <div class="flex flex-col text-left">
                    <span class="font-bold text-slate-700">Simpan sebagai Draft</span>
                    <span class="text-[11px] text-slate-400 mt-0.5 leading-snug">Berita tidak akan muncul di website publik.</span>
                  </div>
                </el-radio>
                
                <!-- Pilihan 2: Published / Live -->
                <el-radio value="published" label="published" size="large" border class="!ml-0 !h-auto py-3 px-4 w-full bg-white !rounded-xl transition-all">
                  <div class="flex flex-col text-left">
                    <span class="font-bold text-[#00a9c3]">Terbitkan (Live)</span>
                    <span class="text-[11px] text-slate-400 mt-0.5 leading-snug">Berita langsung tayang di Jendela Literasi.</span>
                  </div>
                </el-radio>
              </el-radio-group>
            </div>
          </el-form-item>
        </div>

        <!-- TOMBOL SIMPAN -->
        <div class="flex justify-end mt-10 border-t pt-6">
          <el-button 
            type="primary" 
            color="#00a9c3" 
            size="large" 
            class="px-10 !rounded-2xl font-bold shadow-lg" 
            @click="updateArticle" 
            :loading="busy"
          >
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
const fileList = ref<any[]>([]);

const form = reactive({ 
  title: '', 
  excerpt: '', 
  content: '', 
  status: 'draft', 
  thumbnail_id: null 
});

const loadArticle = async () => {
  try {
    const res: any = await apiFetch(`/articles/${route.params.id}`);
    form.title = res.title;
    form.excerpt = res.excerpt || ''; 
    form.content = res.content;
    form.status = res.status || 'draft';
    form.thumbnail_id = res.thumbnail_id;

    // Tampilkan gambar pratinjau jika sudah ada sebelumnya
    if (res.image_url) {
      fileList.value = [{ name: 'thumbnail', url: res.image_url }];
    }
  } catch (e) {
    ElMessage.error('Berita tidak ditemukan');
    router.push('/admin/articles');
  } finally {
    loadingData.value = false;
  }
};

const handleImageChange = async (file: any) => {
  if (!file.raw) return;
  try {
    const attachment = await uploadAttachment(file.raw, { 
      attachmentableType: 'App\\Models\\Article', 
      type: 'thumbnail' 
    });
    form.thumbnail_id = attachment.id;
    ElMessage.success('Gambar baru berhasil diunggah!');
  } catch (e) { 
    ElMessage.error('Gagal mengunggah gambar'); 
    fileList.value = [];
  }
};

const handleImageRemove = () => {
  form.thumbnail_id = null;
  fileList.value = [];
};

const updateArticle = async () => {
  if (!form.title || !form.content || !form.excerpt) {
    return ElMessage.warning('Judul, Ringkasan, dan Isi Konten wajib diisi!');
  }
  
  busy.value = true;
  try {
    await apiFetch(`/articles/${route.params.id}`, { 
      method: 'PUT', 
      body: form 
    });
    ElMessage.success('Berita ABC Jakarta berhasil diperbarui!');
    router.push('/admin/articles');
  } catch (e: any) {
    ElMessage.error(e.data?.message || 'Gagal memperbarui berita. Silakan cek kembali.');
  } finally {
    busy.value = false;
  }
};

onMounted(loadArticle);
</script>

<style scoped>
@reference "tailwindcss";
.glass-panel { 
  @apply bg-white rounded-[2rem] border border-slate-100 shadow-sm; 
}
.custom-input :deep(.el-input__wrapper) { 
  @apply rounded-xl shadow-none border border-slate-200; 
}
</style>