<template>
    <div class="d-flex flex-column w-100 border-bottom p-2 bg-light">
        <details :open="isOpen">
            <summary @click.prevent="this.isOpen = !this.isOpen;" class="d-flex align-items-center">
                <a class="link-primary">
                    <font-awesome-icon icon="fa-regular fa-circle-question" class="me-1" />
                    <small>Справка по заполнению </small>
                </a>
                <font-awesome-icon v-if="isOpen" id="xmark" icon="fa-solid fa-xmark" class="me-3 ms-auto" />
            </summary>
            <p>
                <small>
                    Заполните поле <b>"Артикул"</b> или вставьте скопированный из <b>Excel</b> столбец артикулов,
                    что бы
                    выполнить
                    поиск товаров на складе. Если товоры будут найдены на складе поле <b>"Название"</b> и <b>"Бренд"</b>
                    будут
                    заполнены автоматически. Если товоры не будут найдены на складе, тогда будет необходимо
                    заполнить
                    <b>"Название"</b> и
                    <b>"Бренд"</b> самостоятельно. Поля <b>"Название"</b>, <b>"Количество"</b> и <b>"Цена"</b> так
                    же
                    можно заполнять вставкой скопированных
                    столбцов из <b>Excel</b>.
                    Если товар уже есть в списке на добавление или в самом счёте он будет помечен
                    значком <font-awesome-icon class="text-danger" icon="fa-solid fa-triangle-exclamation" />. Нельзя
                    добавить товары, пока в списке
                    есть задублированные товары.
                </small>

            </p>

        </details>
    </div>
</template>
<style scoped>
summary {
    list-style: none;
}

#xmark {
    color: var(--bs-gray-600)
}

#xmark:hover {
    color: var(--bs-red)
}
</style>

<script>
export default {
    data() {
        return {
            isOpen: false,
        }
    },
}
</script>
