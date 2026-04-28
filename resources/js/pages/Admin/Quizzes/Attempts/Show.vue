<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Props {
  attempt: any;
}

const props = defineProps<Props>();

// Helper to determine CSS classes for options
const getOptionStatus = (answer: any, option: any) => {
  const isSelected = answer.option_id === option.id;
  const isCorrect = option.is_correct; // Assumes 'is_correct' exists on QuizOption model

  if (isSelected && isCorrect) return 'bg-green-500/20 border-green-500 text-green-200';
  if (isSelected && !isCorrect) return 'bg-red-500/20 border-red-500 text-red-200';
  if (!isSelected && isCorrect) return 'border-green-500/50 border-dashed text-green-400';
  return 'bg-white/5 border-white/10 text-gray-400';
};
</script>

<template>
  <Head title="Review Attempt" />

  <div class="min-h-screen bg-[#0f0a24] text-white p-8">
    <div class="max-w-5xl mx-auto">
      
      <div class="bg-[#16103a] rounded-3xl p-8 border border-white/10 mb-8 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold mb-2">{{ attempt.student.name }}</h1>
          <p class="text-indigo-400 font-medium">Quiz: {{ attempt.quiz.title }}</p>
          <p class="text-gray-500 text-sm mt-1">Submitted on: {{ new Date(attempt.created_at).toLocaleString() }}</p>
        </div>
        
        <div class="text-right">
          <div class="text-sm uppercase tracking-widest text-gray-400 font-black mb-1">Final Score</div>
          <div class="text-5xl font-black text-indigo-500">
            {{ attempt.total_marks }} <span class="text-xl text-gray-600">/ {{ attempt.quiz.total_marks ?? 100 }}</span>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div v-for="(ans, index) in attempt.answers" :key="ans.id" 
          class="bg-[#16103a] rounded-2xl p-6 border transition"
          :class="ans.is_correct ? 'border-green-500/20' : 'border-red-500/20'"
        >
          <div class="flex justify-between items-start mb-4">
            <span class="bg-white/10 px-3 py-1 rounded-full text-[10px] font-black uppercase">
              Question {{ index + 1 }}
            </span>
            <div :class="ans.is_correct ? 'text-green-400' : 'text-red-400'" class="font-bold text-sm">
              {{ ans.is_correct ? '✓ Correct' : '✕ Incorrect' }} 
              <span class="ml-2 text-gray-500">({{ ans.marks_obtained }} Pts)</span>
            </div>
          </div>

          <h3 class="text-lg font-medium mb-6">{{ ans.question.question_text }}</h3>

          <div v-if="ans.question.question_type !== 'short_answer'" class="grid gap-3">
            <div v-for="opt in ans.question.options" :key="opt.id"
              :class="getOptionStatus(ans, opt)"
              class="p-4 rounded-xl border flex justify-between items-center text-sm"
            >
              <span>{{ opt.option_text }}</span>
              
              <div class="flex gap-2">
                <span v-if="ans.option_id === opt.id" class="text-[9px] font-black uppercase bg-white/20 px-2 py-0.5 rounded">
                  Student's Pick
                </span>
                <span v-if="opt.is_correct" class="text-[9px] font-black uppercase bg-green-600 text-white px-2 py-0.5 rounded">
                  Correct Answer
                </span>
              </div>
            </div>
          </div>

          <div v-else class="bg-black/30 p-4 rounded-xl border border-white/5">
            <p class="text-xs text-gray-500 uppercase font-bold mb-2">Student's Written Answer:</p>
            <p class="italic text-gray-200">"{{ ans.answer_text || 'No answer provided' }}"</p>
          </div>
        </div>
      </div>

      <div class="mt-10 text-center">
        <Link href="/admin/quizzes" class="text-gray-500 hover:text-white transition font-medium">
          ← Back to Quizzes
        </Link>
      </div>
    </div>
  </div>
</template>