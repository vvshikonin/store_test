<template>
    <DefaultModal v-if="modelValue" @close_modal="closeExpenseTypesModal()" width="700px" title="Типы расходов">
    <template v-slot:default>
        <div class="d-flex p-3 flex-column justify-content-between">
            <div style="max-height: 450px; overflow-y: auto;">
                <draggable v-model="expenseTypes" @end="updateSortOrder" item-key="id">
                    <template #item="{ element, index }">
                        <div :key="element.id" class="list-item">
                            <span class="drag-handle">☰</span> <!-- Иконка перетаскивания -->
                            <TableEditInput class="flex-grow-1" style="height: 35px;"
                                v-model:content="element.name" @update:content="updateExpenseType(element)">
                            </TableEditInput>
                            <!-- <input class="me-3" type="checkbox" v-model="element.is_receipt_optional"> -->
                            <TrashButton class="trash-button" @click="deleteExpenseType(element)">
                                <i class="fas fa-trash"></i>
                            </TrashButton>
                        </div>
                    </template>
                </draggable>
            </div>
            <div class="pt-2">
                <button @click="addExpenseType()" type="button" class="btn btn-primary mt-2">
                    Добавить типовой расход
                </button>
            </div>
        </div>
    </template>
    </DefaultModal>
</template>

<style scoped>
.drag-handle {
    cursor: grab;
    margin-right: 10px;
    /* Добавьте небольшой отступ справа */
}

.drag-handle:active {
    cursor: grabbing;
}

/* Стилизация для элемента списка */
.list-item {
    display: flex;
    align-items: center;
    background-color: #f8f9fa;
    /* Небольшой фон для элементов списка */
    margin-bottom: 5px;
    /* Отступ между элементами */
    padding: 10px;
    /* Паддинг внутри элементов списка */
    border-radius: 4px;
    /* Скругление углов */
    border: 1px solid #e1e4e8;
    /* Тонкая граница вокруг элементов */
}

/* Стилизация при наведении */
.list-item:hover {
    background-color: #eef2f7;
    /* Изменение фона при наведении */
}

/* Стилизация для иконки удаления */
.trash-button {
    color: #dc3545;
    /* Цвет иконки удаления */
    margin-left: auto;
    /* Выравнивание по правому краю */
}

.trash-button:hover {
    color: #ffffff;
    /* Цвет при наведении */
}
</style>

<script>
import { expenseTypeAPI } from '../../api/expense_type_api.js';
import DefaultModal from './default_modal.vue';
import TableEditInput from '../inputs/table_edit_input.vue';
import TrashButton from '../../ui/buttons/TrashButton.vue';
import draggable from 'vuedraggable';

export default {
    components: { DefaultModal, TableEditInput, TrashButton, draggable },
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
    },
    emits: ['update:modelValue', 'on_update'],
    data() {
        return {
            expenseTypes: [],
        }
    },
    methods: {
        closeExpenseTypesModal() {
            this.$emit('update:modelValue', false); // Эмитируем обновление
        },
        async addExpenseType() {
            const expenseType = {
                name: "Новый типовой расход"
            }
            await expenseTypeAPI.store(expenseType);
            this.loadExpenseTypes();
        },
        async loadExpenseTypes() {
            const res = await expenseTypeAPI.index();
            this.expenseTypes = res.data.data;
            this.$emit('on_update');
        },
        async updateExpenseType(expenseType) {
            await expenseTypeAPI.update(expenseType);
            this.loadExpenseTypes();
        },
        async deleteExpenseType(expenseType) {
            const response = await expenseTypeAPI.destroy(expenseType);
            if (response.data.message) {
                this.showToast('Ошибка', response.data.message, 'warning');
            } else {
                this.loadExpenseTypes();
            }
        },
        updateSortOrder() {
            // Подготовка данных для отправки на сервер
            const updatedOrder = this.expenseTypes.map((expenseType, index) => ({
                id: expenseType.id,
                sort_order: index
            }));

            // Использование expenseTypeAPI для отправки обновленного порядка на сервер
            expenseTypeAPI.updateOrder(updatedOrder)
                .then(response => {
                    // Обработка успешного обновления порядка
                    console.log('Порядок успешно обновлен:', response.data);
                })
                .catch(error => {
                    // Обработка ошибок запроса
                    console.error('Ошибка обновления порядка:', error);
                });
        },
    },
    mounted() {
        this.loadExpenseTypes();
    }
}
</script>