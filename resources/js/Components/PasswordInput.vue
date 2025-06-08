<script setup>
import { ref } from 'vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    modelValue: String,
    id: String,
    required: Boolean,
    autocomplete: String,
    class: String,
});

const emit = defineEmits(['update:modelValue']);
const showPassword = ref(false);
</script>

<template>
    <div class="relative">
        <TextInput 
            :id="id"
            :type="showPassword ? 'text' : 'password'"
            :value="modelValue"
            @input="emit('update:modelValue', $event.target.value)"
            :required="required"
            :autocomplete="autocomplete"
            :class="class"
        />
        <button 
            @click="showPassword = !showPassword"
            type="button"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
        >
            <span class="text-xs">{{ showPassword ? 'Скрыть' : 'Показать' }}</span>
        </button>
    </div>
</template>