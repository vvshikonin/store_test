<template>
    <DefaultModal v-if="modelValue" @close_modal="closeContragentsModal()" width="700px" title="Контрагенты">
    <template v-slot>
        <div class="d-flex p-3 flex-column justify-content-between">
            <div class="d-flex mb-3">
                <div class="me-3">
                    <label for="paymentFilter" class="form-label">Системный платёж</label>
                    <select class="form-select" id="paymentFilter" v-model="filters.payment">
                        <option value="">Все</option>
                        <option value="any">С системным платежом</option>
                        <option value="none">Без системного платежа</option>
                    </select>
                </div>
                <div>
                    <label for="userFilter" class="form-label">Ответственный</label>
                    <select class="form-select" id="userFilter" v-model="filters.user">
                        <option value="">Все</option>
                        <option v-for="user in users" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
            </div>
            <div style="max-height: 450px; overflow-y: auto;">
                <div v-for="expenseContragent in filteredContragents"
                    class="d-flex bg-white mt-2 mb-2 p-2 border border-light rounded align-items-center">
                    <div>
                        <a href="#" class="text-primary text-decoration-none fs-4"
                            @click.prevent="editExpenseContragent(expenseContragent)">
                            {{ expenseContragent.name }}
                        </a>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted pe-2">Системный платёж:</span>
                            <span>{{ getReadablePayment(expenseContragent.regular_payment) }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted pe-2">Ответственный:</span>
                            <span>{{ getUserName(expenseContragent.user_id) }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <span class="text-muted pe-2">Дата последней оплаты:</span>
                            <span>{{ moment(expenseContragent.last_payment_date) }}</span>
                        </div>
                    </div>
                    <TrashButton class="ms-auto" @click="deleteExpenseContragent(expenseContragent)">
                    </TrashButton>
                </div>
                <DefaultModal v-if="isEditingContragent" @close_modal="closeEditModal()" width="700px"
                    title="Контрагент">
                    <template v-slot>
                        <div class="d-flex p-3 flex-column justify-content-between">
                            <div style="max-height: 800px; overflow-y: auto;">
                                <div class="form-group">
                                    <label for="name">Название</label>
                                    <input type="text" class="form-control" id="name"
                                        placeholder="Введите название"
                                        v-model="activeExpenseContragent.name" />
                                    <label for="name">Специальные условия</label>
                                    <input type="text" class="form-control" id="name"
                                        placeholder="Запишите специальные условия"
                                        v-model="activeExpenseContragent.special_conditions" />
                                    <div>
                                        <input class="me-3 ms-2" type="checkbox" id="is_receipt_optional"
                                            v-model="activeExpenseContragent.is_receipt_optional">
                                        <label for="is_receipt_optional">Исключить обязательный файл
                                            счёта</label>
                                    </div>
                                    <div>
                                        <input class="me-3 ms-2" type="checkbox" id="is_period_coincides"
                                            v-model="activeExpenseContragent.is_period_coincides">
                                        <label for="is_period_coincides">Отчётный период совпадает с датой
                                            оплаты</label>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <div class="form-group">
                                    <label for="regular-payment" class="text-nowrap pe-2">Системный
                                        платёж:</label>
                                    <select class="form-control flex-grow-1" id="regular-payment"
                                        v-model="activeExpenseContragent.regular_payment">
                                        <option value="none">Без системного платежа</option>
                                        <option value="weekly">Каждую неделю</option>
                                        <option value="monthly">Каждый месяц</option>
                                        <option value="yearly">Каждый год</option>
                                    </select>
                                    <label for="regular-payment-responsible-user"
                                        class="text-nowrap pe-2">Ответственный за системный платёж:</label>
                                    <select class="form-control flex-grow-1"
                                        id="regular-payment-responsible-user"
                                        v-model="activeExpenseContragent.user_id">
                                        <option :value="null">Нет ответственного</option>
                                        <option v-for="user in users" :value="user.id" :key="user.id"> {{
                                            user.name }} </option>
                                    </select>
                                </div>
                                <hr class="my-4">
                                <!-- Выпадающий список для добавления новых типов расходов -->
                                <div
                                    class="form-group d-flex align-items-center justify-content-between mb-3">
                                    <label for="expense-type-select" class="text-nowrap pe-2">Тип расхода:
                                    </label>
                                    <select class="form-control flex-grow-1" id="expense-type-select"
                                        v-model="selectedExpenseType">
                                        <option v-for="type in expenseTypes" :value="type" :key="type.id">
                                            {{ type.name }} </option>
                                    </select>
                                    <AddButton @click="addSelectedType" label="Привязать"
                                        class="text-nowrap">
                                    </AddButton>
                                </div>
                                <hr class="my-4">
                                <!-- Блок для отображения выбранных типов расходов -->
                                <div class="mb-3">
                                    <div v-for="(type, index) in activeExpenseContragent.selectedExpenseTypes"
                                        :key="type.id"
                                        class="d-flex align-items-center justify-content-between"> {{
                                            type.name }} <TrashButton @click="removeSelectedType(index)">
                                        </TrashButton>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="isActiveCheckbox"
                                        v-model="activeExpenseContragent.is_active"
                                        :disabled="!canActivateContragents">
                                    <label class="form-check-label"
                                        for="isActiveCheckbox">Активирован</label>
                                </div>
                                <button @click="saveExpenseContragent()" type="button"
                                    class="btn btn-primary mt-2"> Сохранить изменения </button>
                            </div>
                        </div>
                    </template>
                </DefaultModal>
            </div>
            <div class="pt-2">
                <button @click="addExpenseContragent()" type="button" class="btn btn-primary mt-2"> Добавить
                    контрагента </button>
            </div>
        </div>
    </template>
    </DefaultModal>
</template>

<script>
import { expenseContragentAPI } from '../../api/expense_contragent_api.js';
import { expenseTypeAPI } from '../../api/expense_type_api.js';
import { userAPI } from '../../api/user_api.js';
import DefaultModal from './default_modal.vue';
import AddButton from '../inputs/add_button.vue';
import TrashButton from '../../ui/buttons/TrashButton.vue';

export default {
    components: { DefaultModal, TrashButton, AddButton },
    props: {
        modelValue: {
            type: Boolean,
            default: false
        },
    },
    emits: ['update:modelValue', 'on_update'],
    data() {
        return {
            users: [],
            expenseTypes: [],
            expenseContragents: [],
            isEditingContragent: false,
            activeExpenseContragent: {
                selectedExpenseTypes: [],
                is_receipt_optional: false,  // Установите начальные значения
            },
            filters: {
                payment: '',
                user: ''
            },
        }
    },
    methods: {
        closeContragentsModal() {
            this.$emit('update:modelValue', false); // Эмитируем обновление
        },
        async loadExpenseTypes() {
            const res = await expenseTypeAPI.index();
            this.expenseTypes = res.data.data;
        },
        async loadExpenseContragents() {
            const res = await expenseContragentAPI.index();
            // console.log(res.data);
            this.expenseContragents = res.data;
        },
        async addExpenseContragent() {
            const expenseContragent = {
                name: "Новый контрагент"
            }
            let response = await expenseContragentAPI.store(expenseContragent);
            this.editExpenseContragent(response.data);
        },
        handleSelectChange(newValues) {
            this.activeExpenseContragent.selectedExpenseTypes = newValues;
        },
        editExpenseContragent(expenseContragent) {
            this.activeExpenseContragent = {
                ...this.activeExpenseContragent, // текущие значения
                ...expenseContragent,
                // Преобразуем поля в булевы значения
                is_receipt_optional: !!expenseContragent.is_receipt_optional,
                is_period_coincides: !!expenseContragent.is_period_coincides,
                is_active: !!expenseContragent.is_active,
                selectedExpenseTypes: this.getRelatedExpenseTypes(expenseContragent.related_expense_types),
            };
            this.isEditingContragent = true;
        },
        // Метод для загрузки связанных типов расходов
        getRelatedExpenseTypes(relatedExpenseTypeIds) {
            // Проверяем, что relatedExpenseTypeIds не равен null или undefined
            if (!relatedExpenseTypeIds) {
                return []; // Возвращаем пустой массив, если нет сохраненных связей
            }
            // Предполагается, что relatedExpenseTypeIds - это массив строковых ID
            return this.expenseTypes.filter(type => relatedExpenseTypeIds.includes(String(type.id)));
        },
        addSelectedType() {
            if (this.selectedExpenseType && !this.activeExpenseContragent.selectedExpenseTypes.includes(this.selectedExpenseType)) {
                this.activeExpenseContragent.selectedExpenseTypes.push(this.selectedExpenseType);
            }
            this.selectedExpenseType = null; // Сбросить выбранный тип расхода
        },
        removeSelectedType(index) {
            this.activeExpenseContragent.selectedExpenseTypes.splice(index, 1);
        },
        async saveExpenseContragent() {
            console.log("Выбранные типы расходов перед формированием payload:", this.activeExpenseContragent.selectedExpenseTypes);

            // let relatedExpenseTypeIds = this.activeExpenseContragent.selectedExpenseTypes;
            let relatedExpenseTypeIds = this.activeExpenseContragent.selectedExpenseTypes.map(type => String(type.id));
            // Проверка, являются ли элементы массива объектами, и извлечение ID если необходимо
            if (relatedExpenseTypeIds.length > 0 && typeof relatedExpenseTypeIds[0] === 'object') {
                relatedExpenseTypeIds = relatedExpenseTypeIds.map(type => type.id);
            }

            let payload = {
                ...this.activeExpenseContragent,
                related_expense_types: relatedExpenseTypeIds
            };

            console.log("Полезная нагрузка для сохранения (payload): ", payload);

            await expenseContragentAPI.update(payload);
            console.log("Контрагент сохранен.");
            this.isEditingContragent = false;
            this.loadExpenseContragents(); // Перезагрузка списка контрагентов
            this.$emit('on_update');
        },
        async deleteExpenseContragent(expenseContragent) {
            const response = await expenseContragentAPI.destroy(expenseContragent);
            if (response.data.message) {
                this.showToast('Ошибка', response.data.message, 'warning');
            } else {
                this.loadExpenseContragents();
                this.$emit('on_update');
            }
        },
        getReadablePayment(payment) {
            switch (payment) {
                case 'none': return 'Без системного платежа';
                case 'weekly': return 'Каждую неделю';
                case 'monthly': return 'Каждый месяц';
                case 'yearly': return 'Каждый год';
                default: return '';
            }
        },
        getUserName(userId) {
            const user = this.users.find(user => user.id === userId);
            return user ? user.name : 'Нет ответственного';
        },
        closeEditModal() {
            this.isEditingContragent = false;
            this.activeExpenseContragent = null; // Сброс активного контрагента
        },
        async loadUsers() {
            const params = {
                sort_field: 'name',
                sort_type: 'asc',
                per_page: 100,
            };
            const res = await userAPI.index(params);
            this.users = res.data.data;
        },
    },
    computed: {
        filteredContragents() {
            return this.expenseContragents.filter(contragent => {
                let paymentFilterSatisfied = false;
                if (this.filters.payment === '') {
                    paymentFilterSatisfied = true;
                } else if (this.filters.payment === 'any') {
                    paymentFilterSatisfied = contragent.regular_payment !== 'none';
                } else if (this.filters.payment === 'none') {
                    paymentFilterSatisfied = contragent.regular_payment === 'none';
                }

                let userFilterSatisfied = this.filters.user === '' || contragent.user_id === this.filters.user;

                return paymentFilterSatisfied && userFilterSatisfied;
            });
        },
        canActivateContragents() {
            return this.checkPermission('contragents_activate');
        },
    },
    mounted() {
        this.loadExpenseContragents();
        this.loadExpenseTypes();
        this.loadUsers();
    }
}
</script>