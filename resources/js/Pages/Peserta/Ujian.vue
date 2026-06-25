<template>
  <ExamLayout
    :siswa="siswa"
    :jurusan="jurusan"
    :saving="saving"
    :remaining-seconds="timeLeft"
    :current-exam-step="current_exam_step"
    :total-exams="total_exams"
  >
    <div class="max-w-6xl mx-auto px-4 py-6">
      <div class="flex flex-col lg:flex-row gap-6">
        <!-- Main Question Area -->
        <div class="flex-1">
          <div class="bg-white rounded-2xl elev-1 p-6 mb-4 fade-in">
            <div class="flex items-center justify-between mb-4">
              <span class="text-xs font-bold text-gray-400 uppercase">Soal {{ currentIndex + 1 }} dari {{ soal.length }}</span>
              <button
                @click="toggleFlag"
                class="btn text-xs px-3 py-1.5 rounded-lg border-2 transition flex items-center gap-1.5"
                :class="currentFlagged ? 'border-warning bg-yellow-50 text-warning' : 'border-gray-200 text-gray-400 hover:border-warning'"
              >
                <svg v-if="currentFlagged" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                <span>{{ currentFlagged ? 'Ditandai' : 'Tandai Ragu' }}</span>
              </button>
            </div>

            <div class="text-base font-semibold text-gray-800 leading-relaxed mb-6" v-html="currentSoal?.pertanyaan"></div>

            <!-- Options -->
            <div class="space-y-3">
              <div
                v-for="letter in (jurusan.teknik_penilaian === 'likert' ? ['a','b','c','d'] : ['a','b','c','d','e'])"
                :key="letter"
                class="opt"
                :class="{ selected: localAnswers[currentIndex] === letter }"
                @click="selectAnswer(letter)"
              >
                <span class="opt-letter">{{ letter.toUpperCase() }}</span>
                <span class="text-sm">{{ currentSoal?.['opsi_' + letter] }}</span>
              </div>
            </div>
          </div>

          <!-- Nav buttons -->
          <div class="flex items-center justify-between">
            <button
              @click="goTo(currentIndex - 1)"
              :disabled="currentIndex === 0"
              class="btn px-5 py-2.5 rounded-xl bg-white elev-1 text-gray-600 hover:bg-gray-50 disabled:opacity-40 text-sm font-semibold flex items-center gap-2"
            >&larr; Sebelumnya</button>
            <button
              v-if="currentIndex < soal.length - 1"
              @click="goTo(currentIndex + 1)"
              class="btn px-5 py-2.5 rounded-xl gradient-primary text-white text-sm font-semibold flex items-center gap-2"
            >Selanjutnya &rarr;</button>
            <button
              v-else
              @click="showSubmitConfirm = true"
              class="btn px-5 py-2.5 rounded-xl gradient-gold text-white text-sm font-semibold"
            >Kumpulkan Jawaban</button>
          </div>
        </div>

        <!-- Sidebar: Question Navigation -->
        <div class="lg:w-72 shrink-0">
          <div class="bg-white rounded-2xl elev-1 p-5 sticky top-20">
            <h3 class="text-sm font-bold text-gray-600 mb-4">Navigasi Soal</h3>
            <div class="grid grid-cols-5 gap-2 mb-4">
              <div
                v-for="(s, i) in soal"
                :key="i"
                class="qdot"
                :class="{
                  current: i === currentIndex,
                  answered: localAnswers[i] && i !== currentIndex,
                  flagged: localFlags[i] && !localAnswers[i] && i !== currentIndex
                }"
                @click="goTo(i)"
              >{{ i + 1 }}</div>
            </div>

            <div class="space-y-2 text-xs text-gray-500 border-t pt-3">
              <div class="flex items-center gap-2"><span class="qdot w-6 h-6 text-[10px] answered">1</span> Terjawab</div>
              <div class="flex items-center gap-2"><span class="qdot w-6 h-6 text-[10px] current">1</span> Aktif</div>
              <div class="flex items-center gap-2"><span class="qdot w-6 h-6 text-[10px] flagged">1</span> Ragu-ragu</div>
              <div class="flex items-center gap-2"><span class="qdot w-6 h-6 text-[10px]">1</span> Belum</div>
            </div>

            <div class="mt-4 pt-3 border-t">
              <div class="text-xs text-gray-400 mb-1">Progress</div>
              <div class="w-full bg-gray-100 rounded-full h-2">
                <div class="bg-success rounded-full h-2 transition-all" :style="{ width: progressPercent + '%' }"></div>
              </div>
              <div class="text-xs text-gray-500 mt-1">{{ answeredCount }}/{{ soal.length }} terjawab</div>
            </div>

            <button
              :disabled="answeredCount < soal.length"
              @click="showSubmitConfirm = true"
              class="btn w-full mt-4 py-2.5 rounded-xl text-white text-sm font-bold transition-all"
              :class="answeredCount < soal.length ? 'bg-gray-300 cursor-not-allowed' : 'gradient-gold hover:opacity-90'"
            >
              {{ answeredCount < soal.length ? 'Jawab Semua Soal Dulu' : 'Kumpulkan Jawaban' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <ConfirmModal
      :show="showSubmitConfirm"
      title="Kumpulkan Jawaban?"
      :message="submitMessage"
      confirm-text="Ya, Kumpulkan"
      variant="primary"
      @close="showSubmitConfirm = false"
      @confirm="submitExam"
    />
  </ExamLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import ExamLayout from '../../Layouts/ExamLayout.vue';
import ConfirmModal from '../../Components/ConfirmModal.vue';

const props = defineProps({
  siswa: Object,
  jurusan: Object,
  soal: Array,
  answers: Object,
  flags: Object,
  remaining_seconds: Number,
  current_exam_step: Number,
  total_exams: Number
});

const currentIndex = ref(0);
const saving = ref(false);
const showSubmitConfirm = ref(false);
const timeLeft = ref(props.remaining_seconds);

// Initialize answers and flags from server
const localAnswers = reactive({});
const localFlags = reactive({});

Object.entries(props.answers || {}).forEach(([k, v]) => { localAnswers[k] = v; });
Object.entries(props.flags || {}).forEach(([k, v]) => { localFlags[k] = v; });

const currentSoal = computed(() => props.soal[currentIndex.value]);
const currentFlagged = computed(() => !!localFlags[currentIndex.value]);
const answeredCount = computed(() => Object.values(localAnswers).filter(Boolean).length);
const progressPercent = computed(() => (answeredCount.value / props.soal.length) * 100);

const submitMessage = computed(() => {
  const unanswered = props.soal.length - answeredCount.value;
  if (unanswered > 0) {
    return `Masih ada ${unanswered} soal yang belum dijawab. Yakin ingin mengumpulkan?`;
  }
  return 'Semua soal sudah dijawab. Yakin ingin mengumpulkan jawaban?';
});

function goTo(index) {
  if (index >= 0 && index < props.soal.length) {
    currentIndex.value = index;
  }
}

function selectAnswer(letter) {
  localAnswers[currentIndex.value] = letter;
  autoSave();
}

function toggleFlag() {
  localFlags[currentIndex.value] = !localFlags[currentIndex.value];
  autoSave();
}

let saveTimeout;
function autoSave() {
  clearTimeout(saveTimeout);
  saving.value = true;
  saveTimeout = setTimeout(() => {
    window.axios.post(route('peserta.ujian.save'), {
      soal_id: props.soal[currentIndex.value].id,
      jawaban: localAnswers[currentIndex.value] || null,
      ditandai: !!localFlags[currentIndex.value]
    }).then(() => {
      saving.value = false;
    }).catch(() => {
      saving.value = false;
    });
  }, 500);
}

function submitExam() {
  showSubmitConfirm.value = false;
  router.post(window.route('peserta.ujian.submit'));
}

// Timer countdown
let timerInterval;
onMounted(() => {
  timerInterval = setInterval(() => {
    timeLeft.value--;
    if (timeLeft.value <= 0) {
      clearInterval(timerInterval);
      router.post(window.route('peserta.ujian.submit'));
    }
  }, 1000);
});

onUnmounted(() => {
  clearInterval(timerInterval);
  clearTimeout(saveTimeout);
});
</script>