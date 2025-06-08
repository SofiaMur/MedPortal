<template>
  <section class="py-16">
    <div class="max-w-7xl md:mx-auto px-4 sm:px-6 md:px-8">
      <div class="md:text-center mb-2 lg:mb-12">
        <p
          class="mt-2 text-2xl sm:text-3xl md:text-2xl lg:text-3xl leading-8 font-extrabold tracking-tight text-gray-900">
          Выгодные предложения
        </p>
        <p class="mt-4 max-w-2xl text-lg sm:text-xl md:text-lg lg:text-xl text-gray-500 md:mx-auto">
          Мы регулярно обновляем специальные условия для наших пациентов
        </p>
      </div>

      <!-- Десктопная версия (grid) -->
      <div class="hidden md:grid mt-10 grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <PromotionTimeCard discount="-20%" title="Консультация терапевта"
          description="Только в этом месяце специальное предложение для наших постоянных клиентов"
          imageUrl="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=225&q=80"
          :durationHours="24" />
        <PromotionTimeCard discount="-30%" title="Комплексная диагностика"
          description="Полное обследование организма со скидкой для новых пациентов"
          imageUrl="https://images.unsplash.com/photo-1530026186672-2cd00ffc50fe?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=225&q=80"
          :durationHours="72" />
        <PromotionNewCard tag="Новинка" title="Цифровая медицинская карта"
          description="Полный переход на электронные карты - вся Ваша медицинская история всегда под рукой в удобном формате"
          imageUrl="https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=225&q=80" />
        <PromotionTimeCard discount="-25%" title="Годовое комплексное обследование"
          description="Годовое медицинское сопровождение с фиксированной стоимостью"
          imageUrl="https://images.unsplash.com/photo-1581595219315-a187dd40c322?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=225&q=80"
          :duration-hours="24" :start-date="'2025-05-01'" :end-date="'2025-05-31'" :is-expired="true" />
      </div>

      <!-- Мобильная версия (карусель) -->
      <div class="md:hidden relative">
        <div class="overflow-x-auto py-8 scrollbar-hide">
          <div class="flex space-x-4">
            <!-- Карточки в карусели -->
            <div class="w-80 flex-shrink-0 flex flex-col">
              <PromotionTimeCard discount="-20%" title="Консультация терапевта"
                description="Только в этом месяце специальное предложение"
                imageUrl="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=225&q=80"
                :durationHours="24" />
            </div>

            <div class="w-80 flex-shrink-0 flex flex-col">
              <PromotionTimeCard discount="-15%" title="Консультация терапевта"
                description="Тц для наших клиентов Тц для наших клиентов Тц для наших клиентов"
                imageUrl="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=225&q=80"
                :durationHours="72" />
            </div>

            <div class="w-80 flex-shrink-0 flex flex-col">
              <PromotionNewCard tag="Новинка" title="Онлайн консультации"
                description="Консультация врача не выходя из дома"
                imageUrl="https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=225&q=80" />
            </div>

            <!-- Завершенная акция -->
            <div class="w-80 flex-shrink-0 flex flex-col">
              <PromotionTimeCard discount="-15%" title="Консультация терапевта"
                description="Предложение прошлого месяца"
                imageUrl="https://images.unsplash.com/photo-1576091160550-2173dba999ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=400&h=225&q=80"
                :duration-hours="24" :start-date="'2025-05-01'" :end-date="'2025-05-31'" :is-expired="true" />
            </div>

            <!-- Кнопка "Показать все" -->
            <div class="w-48 flex-shrink-0 flex items-center justify-center">
              <Link href="/promotions"
                class="w-full mx-2 px-4 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-300 flex items-center justify-center">
              Показать все
              <svg class="ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                  d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
                  clip-rule="evenodd" />
              </svg>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div class="hidden md:block mt-12 text-center">
        <Link href="/promotions"
          class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-300">
        Показать все акции
        <svg class="ml-3 -mr-1 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z"
            clip-rule="evenodd" />
        </svg>
        </Link>
      </div>
    </div>
  </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import PromotionTimeCard from '@/Components/App/Cards/PromotionTimeCard.vue'
import PromotionNewCard from '@/Components/App/Cards/PromotionNewCard.vue'
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.flex.space-x-4 {
  align-items: stretch;
}
</style>