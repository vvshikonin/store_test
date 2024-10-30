<template>
    <div style="position: fixed; z-index:10000; bottom:8%; right:2%;">
        <!-- Итерация по массиву toasts и отображение уведомлений для всех элементов, которые имеют isShow:true -->
        <template v-for="toast in toasts" :key="toast">
            <div class="mb-1 shadow" :class="['toast', 'show']" role="alert" aria-live="assertive" aria-atomic="true"
                v-if="toast.isShow">
                <div class="toast-header d-flex justify-content-between align-items-center text-white"
                    :class="[classObject[toast.type] || classObject['info']]">
                    <font-awesome-icon v-if="toast.type === 'success'" icon="fas fa-circle-check"
                        class=" fa-lg mr-2"></font-awesome-icon>
                    <font-awesome-icon v-else-if="toast.type === 'warning'" icon="fas fa-exclamation-triangle"
                        class=" fa-lg mr-2"></font-awesome-icon>
                    <font-awesome-icon v-else-if="toast.type === 'danger'" icon="fas fa-times-circle"
                        class=" fa-lg mr-2"></font-awesome-icon>
                    <font-awesome-icon v-else-if="toast.type === 'info'" icon="fas fa-info-circle"
                        class=" fa-lg mr-2"></font-awesome-icon>
                    <!-- Отображение заголовка уведомления -->
                    <strong>{{ toast.title }}</strong>
                    <!-- Кнопка закрытия уведомления -->
                    <button @click="toast.isShow = false" type="button" class="btn-close btn-close-white" aria-label="Close"
                        data-dismiss="toast"></button>
                </div>
                <!-- Отображение текста уведомления -->
                <div class="toast-body"><p style="white-space: pre-line;">{{ toast.message }}</p></div>
            </div>
        </template>
    </div>
</template>

<script>
export default {
    props: {
        // Массив объектов, представляющих уведомления
        toasts: {
            type: Array,
            default: [
                {
                    isShow: true,
                    type: "info",
                    title: "Пример заголовка info",
                    message: "Пример сообщения success",
                },
                {
                    isShow: true,
                    type: "success",
                    title: "Пример заголовка success",
                    message: "Пример сообщения success",
                },
                {
                    isShow: true,
                    type: "warning",
                    title: "Пример заголовка warning",
                    message: "Пример сообщения warning",
                },
                {
                    isShow: true,
                    type: "danger",
                    title: "Пример заголовка danger",
                    message: "Пример сообщения danger",
                },
            ],
        },
    },
    computed: {
        // Вычисляемый объект классов, который зависит от свойства type в объекте уведомления
        classObject() {
            return {
                info: "bg-primary",
                success: "bg-success",
                warning: "bg-warning",
                danger: "bg-danger",
            };
        },
    },
    watch: {
        // Следим за изменениями в массиве toasts
        toasts: {
            // Функция-обработчик, которая вызывается при изменении массива
            handler(newVal, oldVal) {
                // Ищем новый элемент с isShow:true
                const newToast = newVal.find((toast) => toast.isShow === true);
                // Если такой элемент найден, запускаем для него таймер
                if (newToast) {
                    this.startTimer(newToast);
                }
            },
            // Глубокое наблюдение за массивом (следим за изменениями во вложенных объектах)
            deep: true
        }
    },
    methods: {
        // Функция для запуска таймера, который скрывает уведомление через указанное в параметре время
        startTimer(toast) {
            setTimeout(() => {
                toast.isShow = false
            }, 6000)
        }
    }
};
</script>
