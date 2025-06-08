<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
    modelValue: {
        type: Object,
        required: true,
        default: () => ({
            login: '',
            last_name: '',
            first_name: '',
            middle_name: '',
            birth_date: '',
            gender: '',
            phone: '',
            email: '',
            insurance_number: '',
            snils: '',
            chronic_diseases: '',
            allergies: ''
        })
    },
    isLoading: {
        type: Boolean,
        default: false
    },
    errors: {
        type: Object,
        default: () => ({})
    }
})

const model = computed({
    get() {
        return props.modelValue
    },
    set(value) {
        emit('update:modelValue', value)
    }
})

const emit = defineEmits([
    'update:modelValue',
    'register',
    'reset',
    'format-phone',
    'format-insurance',
    'format-snils'
])

// Функция для транслитерации кириллицы в латиницу
const transliterate = (text) => {
    const cyrillicMap = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
        'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
        'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
        'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
        'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
        'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo',
        'Ж': 'Zh', 'З': 'Z', 'И': 'I', 'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M',
        'Н': 'N', 'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U',
        'Ф': 'F', 'Х': 'H', 'Ц': 'Ts', 'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sch',
        'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu', 'Я': 'Ya'
    }

    return text.split('').map(char => {
        return cyrillicMap[char] || char
    }).join('')
}

// Функция для генерации случайного логина
const generateRandomLogin = () => {
    const { last_name, first_name, middle_name } = model.value
    
    if (!last_name || !first_name) return ''
    
    // Транслитерируем ФИО
    const translitLastName = transliterate(last_name.toLowerCase())
    const translitFirstName = transliterate(first_name.toLowerCase())
    const translitMiddleName = middle_name ? transliterate(middle_name.toLowerCase()) : ''
    
    const firstLetterLastName = translitLastName[0]
    const firstLetterFirstName = translitFirstName[0]
    const firstLettersMiddleName = translitMiddleName ? translitMiddleName.substring(0, 2) : ''
    
    const randomNumbers = Math.floor(1000 + Math.random() * 9000)
    
    let login = `${firstLetterLastName}${firstLetterFirstName}${firstLettersMiddleName}${randomNumbers}`
    
    login = login.replace(/[^a-zA-Z0-9_-]/g, '_')
    
    return login
}

watch(
    () => [model.value.last_name, model.value.first_name, model.value.middle_name],
    () => {
        // Генерируем логин только если поле логина пустое или соответствует паттерну сгенерированного
        if (!model.value.login || /^[a-zA-Z0-9_-]+$/.test(model.value.login)) {
            model.value.login = generateRandomLogin()
        }
    },
    { deep: true }
)
</script>

<template>
    <div class="bg-[#fafafa] dark:bg-gray-800 rounded-lg shadow dark:shadow-gray-900 p-6 mb-8 border border-gray-200 dark:border-gray-600/20">
        <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-6">Регистрация нового пациента</h2>

        <form @submit.prevent="emit('register')" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="reg-last-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Логин*</label>
                    <input id="reg-last-name" type="text" v-model="model.login"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        :class="{ 'border-red-500': errors.login }" required>
                    <p v-if="errors.login" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.login }}</p>
                </div>

                <div>
                    <label for="reg-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input id="reg-email" type="email" v-model="model.email"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        :class="{ 'border-red-500': errors.email }">
                    <p v-if="errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.email }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="reg-last-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фамилия*</label>
                    <input id="reg-last-name" type="text" v-model="model.last_name"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        :class="{ 'border-red-500': errors.last_name }" required>
                    <p v-if="errors.last_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.last_name }}</p>
                </div>

                <div>
                    <label for="reg-first-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Имя*</label>
                    <input id="reg-first-name" type="text" v-model="model.first_name"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        :class="{ 'border-red-500': errors.first_name }" required>
                    <p v-if="errors.first_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.first_name }}</p>
                </div>

                <div>
                    <label for="reg-middle-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Отчество*</label>
                    <input id="reg-middle-name" type="text" v-model="model.middle_name"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        :class="{ 'border-red-500': errors.middle_name }" required>
                    <p v-if="errors.middle_name" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.middle_name }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="reg-phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Телефон*</label>
                    <input id="reg-phone" type="tel" v-model="model.phone"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        placeholder="7 (___) ___-__-__" @input="emit('format-phone', $event)"
                        :class="{ 'border-red-500': errors.phone }" required>
                    <p v-if="errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.phone }}</p>
                </div>
                <div>
                    <label for="reg-birth-date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Дата рождения*</label>
                    <input id="reg-birth-date" type="date" v-model="model.birth_date"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        :max="new Date().toISOString().split('T')[0]"
                        :class="{ 'border-red-500': errors.birth_date }" required>
                    <p v-if="errors.birth_date" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.birth_date }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="reg-insurance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Полис ОМС*</label>
                    <input id="reg-insurance" type="text" v-model="model.insurance_number"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        placeholder="___-___-___-____" @input="emit('format-insurance', $event)"
                        :class="{ 'border-red-500': errors.insurance_number }" required>
                    <p v-if="errors.insurance_number" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.insurance_number }}</p>
                </div>

                <div>
                    <label for="reg-snils" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">СНИЛС*</label>
                    <input id="reg-snils" type="text" v-model="model.snils"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                        placeholder="___-___-___ __" @input="emit('format-snils', $event)"
                        :class="{ 'border-red-500': errors.snils }" required>
                    <p v-if="errors.snils" class="mt-1 text-sm text-red-600 dark:text-red-400">{{ errors.snils }}</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Пол*</label>
                <div class="mt-1 flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" v-model="model.gender" value="male"
                            class="h-4 w-4 text-blue-600 dark:text-blue-300 focus:ring-blue-500" required>
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Мужской</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" v-model="model.gender" value="female"
                            class="h-4 w-4 text-blue-600 dark:text-blue-300 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-300">Женский</span>
                    </label>
                </div>
            </div>

            <!-- Дополнительные медицинские данные -->
            <div>
                <label for="chronic-diseases" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Хронические заболевания</label>
                <textarea id="chronic-diseases" v-model="model.chronic_diseases" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                    placeholder="Перечислите хронические заболевания, если есть"></textarea>
            </div>

            <div>
                <label for="allergies" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Аллергии</label>
                <textarea id="allergies" v-model="model.allergies" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600/20 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-3 bg-[#fafafa] dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                    placeholder="Укажите аллергические реакции, если есть"></textarea>
            </div>

            <div class="flex justify-end space-x-3 pt-2">
                <button type="button" @click="emit('reset')"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600/20 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    Очистить
                </button>
                <button type="submit"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-[#fafafa] bg-blue-600 hover:bg-blue-700 dark:hover:bg-blue-500 transition-colors"
                    :disabled="isLoading">
                    <span v-if="isLoading">Регистрация...</span>
                    <span v-else>Зарегистрировать</span>
                </button>
            </div>
        </form>
    </div>
</template>
