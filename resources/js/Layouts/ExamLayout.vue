<template>
  <div class="min-h-screen bg-[#eef1f5] text-[#2b3445]">
    <!-- Top bar -->
    <header class="gradient-primary text-white sticky top-0 z-40 elev-2">
      <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl gradient-gold flex items-center justify-center font-extrabold text-white">P</div>
          <div class="leading-tight">
            <div class="font-bold text-sm">{{ siswa?.nama || 'Peserta' }}</div>
            <div class="text-[11px] text-white/60">
              {{ jurusan?.kode || 'Jurusan' }} • {{ siswa?.ruangan?.nama || 'Ruangan' }}
            </div>
          </div>
          <span
            v-if="totalExams > 1"
            class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-bold bg-white/10 text-white px-2.5 py-1 rounded-full"
          >
            Ujian {{ currentExamStep }} dari {{ totalExams }} • {{ jurusan?.kode }}
          </span>
        </div>
        <div class="flex items-center gap-3">
          <div class="hidden sm:flex items-center gap-1.5 text-xs text-white/70">
            <span class="dot" :class="saving ? 'bg-warning' : 'bg-success'"></span>
            <span>{{ saving ? 'Menyimpan...' : 'Tersimpan' }}</span>
          </div>
          <div class="timer-pill text-primary" :class="{ danger: isDanger }">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <span>{{ formattedTime }}</span>
          </div>
          <button @click="toggleFullscreen" class="btn bg-white/10 hover:bg-white/20 p-2 rounded-xl" title="Layar penuh">
            <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3m13-5v3a2 2 0 0 1-2 2h-3"/></svg>
          </button>
        </div>
      </div>
    </header>

    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  siswa: Object,
  jurusan: Object,
  saving: Boolean,
  remainingSeconds: Number,
  currentExamStep: Number,
  totalExams: Number
});

const isDanger = computed(() => props.remainingSeconds <= 60);

const formattedTime = computed(() => {
  const m = String(Math.floor(props.remainingSeconds / 60)).padStart(2, '0');
  const s = String(props.remainingSeconds % 60).padStart(2, '0');
  return `${m}:${s}`;
});

function toggleFullscreen() {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {});
  } else {
    document.exitFullscreen();
  }
}
</script>
