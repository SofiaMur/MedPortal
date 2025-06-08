<template>
  <div class="w-full">
    <h3 class="text-xl font-semibold mb-4 pb-2 border-b border-gray-300">Мои заметки</h3>

    <!-- Поле для ввода новой заметки -->
    <div class="mb-6">
      <textarea
        v-model="newNote"
        placeholder="Введите новую заметку..."
        maxlength="150"
        class="w-full border border-gray-300 rounded-md p-3 min-h-[5rem] resize-y focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400"
        @keydown.enter.prevent="addNote"
      ></textarea>
      <button
        @click="addNote"
        class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
      >
        Добавить заметку
      </button>
    </div>

    <!-- Список заметок -->
    <div v-if="notes.length > 0" class="overflow-x-auto">
      <div class="flex gap-4 w-max">
        <div
          v-for="(note, index) in notes"
          :key="index"
          class="min-w-[300px] max-w-[300px] flex flex-col justify-between border border-blue-100 rounded-md bg-blue-50 p-4"
        >
          <div class="flex justify-between items-start mb-2">
            <p class="text-sm text-gray-800 whitespace-pre-wrap break-words">{{ note.text }}</p>
            <button
              @click="deleteNote(index)"
              class="text-gray-400 hover:text-red-500 transition"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
          <div class="text-xs text-gray-600">
            {{ formatDate(note.date) }}
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center text-gray-500 py-4">
      Нет сохраненных заметок
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted } from 'vue';

const newNote = ref('');
const notes = ref([]);

const loadNotes = () => {
  const savedNotes = localStorage.getItem('doctorNotes');
  if (savedNotes) {
    notes.value = JSON.parse(savedNotes);
  }
};

const addNote = () => {
  if (newNote.value.trim()) {
    const note = {
      text: newNote.value.trim(),
      date: new Date()
    };
    notes.value.unshift(note);
    saveNotes();
    newNote.value = '';
  }
};

const deleteNote = (index) => {
  notes.value.splice(index, 1);
  saveNotes();
};

const saveNotes = () => {
  localStorage.setItem('doctorNotes', JSON.stringify(notes.value));
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(() => {
  loadNotes();
});
</script>