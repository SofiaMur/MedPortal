<script setup>
defineProps(['message']);
defineEmits(['close']);
</script>

<template>
    <Transition 
        enter-active-class="transition ease-out duration-300"
        leave-active-class="transition ease-in duration-200"
    >
        <div class="fixed top-16 left-1/2 -translate-x-1/2 mx-auto z-50">
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-6 py-3 rounded shadow-lg max-w-md w-full">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ message }}</span>
                    <button @click="$emit('close')" class="ml-auto text-green-700 hover:text-green-900">
                        &times;
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>