<template>
    <Table>
        <template v-slot:filters>
            <FilterInput v-model="params.id_filter" type="number" label="Номер" placeholder="Номер Расхода" />
            <FilterInputBetween v-model="params.created_at_filter" label="Дата создания" type="date"
                style="width: 16.6%;" />
            <FilterInput v-model="params.comment_filter" label="Комментарий" type="text" />
            <FilterMultipleSelect v-model="params.contragent_filter" :options="expenseContragents"
                placeholder="Контрагент" label="Контрагент" />
            <FilterMultipleSelect v-model="params.expense_types_filter" :options="expenseTypes"
                placeholder="Тип расхода" label="Тип расхода" />
            <FilterMultipleSelect v-model="params.legal_entity_filter" :options="legalEntities"
                placeholder="Выбрать юр. лицо" label="Юридическое лицо" />
            <FilterMultipleSelect v-model="params.payment_method_filter" :options="paymentMethodsWithLegalEntities"
                placeholder="Выбрать способ оплаты" label="Способ оплаты" />
            <FilterInputBetween v-model="params.payment_date_filter" label="Дата оплаты" type="date"
                style="width: 16.6%;" />
            <FilterSelect v-model="params.payment_status_filter" :options="paymentStatuses" placeholder="Статус оплаты"
                label="Статус оплаты" />
            <FilterInputBetween v-model="params.sum_filter" type="number" step="0.01" label="Сумма"
                style="width: 16.6%;" />
            <FilterMultipleSelect v-model="params.creators_filter" :options="users"
                placeholder="Ответственные (Кто создал)" label="Ответственные" />
            <FilterMultipleSelect v-model="params.accounting_years_filter" :options="yearsList"
                placeholder="Годы периода" label="Годы периода" />
            <FilterMultipleSelect v-model="params.accounting_months_filter" :options="monthsList"
                placeholder="Месяцы периода" label="Месяцы периода" />
            <FilterSelect v-model="params.has_receipt_file" :options="hasFileOptions" label="Файл счёта" />
            <FilterSelect v-model="params.has_invoice_file" :options="hasFileOptions" label="Файл чека/ттн" />
            <FilterSelect v-model="params.is_edo" :options="isEdoOptions" label="Чек в ЭДО" />
            <button @click="isEditingContragents = true" v-if="canEditContragents"
                class="btn btn-light border border-secondary bg-opacity ms-auto mt-1 d-flex align-items-center"
                style="font-size: 14px; height: 20%;" type="button"> Контрагенты <font-awesome-icon
                    class="ms-1 text-success" icon="fa-solid fa-gear" />
            </button>
            <button @click="isEditingExpenseTypes = true" v-if="canEditExpenseTypes"
                class="btn btn-light border border-secondary bg-opacity ms-1 mt-1 d-flex align-items-center"
                style="font-size: 14px; height: 20%;" type="button"> Типы расходов <font-awesome-icon
                    class="ms-1 text-success" icon="fa-solid fa-gear" />
            </button>
            <button @click="goToSummaryPage()" v-if="canEditExpenseTypes"
                class="btn btn-light border border-secondary bg-opacity ms-1 mt-1 d-flex align-items-center"
                style="font-size: 14px; height: 20%;" type="button"> Отчёт <font-awesome-icon class="ms-1 text-success"
                    icon="fa-solid fa-gear" />
            </button>
        </template>
        <template v-slot:thead>
            <TH field="id">Номер</TH>
            <TH field="payment_date">Дата оплаты</TH>
            <TH field="is_paid">Статус оплаты</TH>
            <TH>Сумма</TH>
            <TH field="created_by">Кто создал</TH>
            <TH field="comment">Комментарий</TH>
            <TH field="is_edo">Чек в ЭДО</TH>
            <TH field="legal_entity_id">Юридическое лицо</TH>
            <TH field="payment_method_id">Способ оплаты</TH>
            <TH field="contragent_id">Контрагент</TH>
            <TH field="expense_type_id">Тип расхода</TH>
            <TH field="converted_to_money_refunds_id">Перевод в Возврат ДС</TH>
            <TH field="created_at">Дата создания</TH>
            <TH></TH>
        </template>
        <template v-slot:tbody>
            <TR v-for="expense in expenses" :key="expense" 
                :custom-class="{
                    'table-warning': expense.is_need_to_complete,
                    'table-primary': expense.converted_to_money_refunds_id
                }">
                <TD>№{{ expense.id }} </TD>
                <TD> {{ moment(expense.payment_date) }} </TD>
                <TD :class="{ 'text-danger': expense.is_paid === 2 }"> {{ setPaymentStatusCell(expense) }} </TD>
                <TD> {{ calculateTotalPrice(expense).priceFormat(true) }} </TD>
                <TD> {{ expense.creator.name }} </TD>
                <TD> {{ expense.is_need_to_complete ? 'Быстрый расход - требуется заполнение!' : expense.comment }} </TD>
                <TD> {{ expense.is_edo ? 'Да' : '-' }} </TD>
                <TD> {{ expense.is_need_to_complete ? '-' : expense.legal_entity_id?.name }} </TD>
                <TD> {{ expense.is_need_to_complete ? '-' : expense.payment_method_id?.name }} </TD>
                <TD> {{ expense.contragent_id?.name }} </TD>
                <TD> {{ getExpenseTypesString(expense) }} </TD>
                <TD v-if="expense.converted_to_money_refunds_id"> 
                    <a :href="`#/money-refunds/${expense.converted_to_money_refunds_id}/edit`"> Переведен </a> 
                </TD>
                <TD v-else> - </TD>
                <TD> {{ moment(expense.created_at) }} </TD>
                <TD>
                    <button @click="toExpense(expense.id)" class="btn btn-outline-primary border-0">
                        <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                    </button>
                </TD>
            </TR>
        </template>
        <template v-slot:tfoot>
            <div v-if="expenses.length" class="d-inline">
                <span class="pe-3">Общая сумма: {{ summary.total_sum.priceFormat(true) }}</span>
                <span>Всего хоз. расходов: {{ meta.total }}</span>
            </div>
        </template>
        <template v-slot:info>
            <ExpenseTypesModal v-model="isEditingExpenseTypes"></ExpenseTypesModal>
            <ExpenseContragentsModal v-model="isEditingContragents"></ExpenseContragentsModal>
            <DefaultModal width="660px" v-if="selectExportType" @close_modal="selectExportType = false"
                title="Выберите тип выгрузки">
                <div class="d-flex p-3">
                    <span>Что вы хотите выгрузить?</span>
                </div>
                <div class="d-flex justify-content-end p-2 mb-0 bg-light">
                    <button class="btn bg-gradient btn-outline-primary m-1" @click="onExpenseItemsExport()"
                        title="Выгрузить все позиции хоз. расходов.">Cписок расходов с позициями</button>
                    <!-- <button class="btn bg-gradient btn-outline-primary m-1" @click="onSortedExpenseItemsExport()"
                        title="Выгрузить типы расходов.">Отсортированный список типов расходов</button> -->
                    <button class="btn bg-gradient btn-light border m-1"
                        @click="selectExportType = false">Отмена</button>
                </div>
            </DefaultModal>
            <DefaultModal width="500px" v-if="isFastExpenseModal" @close_modal="isFastExpenseModal = false; isFileValid = false"
                title="Быстрое создание расхода">
                <div class="d-flex p-3">
                    <span>Для генерации быстрого расхода необходимо только прикрепить файл.</span>
                </div>
                <div class="d-flex justify-content-start p-3">
                    <input type="file" class="form-control" @change="onFileChange" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div class="d-flex justify-content-start p-3">
                    <button type="button" class="btn btn-primary" @click="onCreateFastExpense()" :disabled="!isFileValid">
                        Создать
                    </button>
                </div>
            </DefaultModal>
        </template>
    </Table>
</template>

<script>
import { mapGetters } from 'vuex';
import { expenseAPI } from '../api/expense_api.js';
import { expenseTypeAPI } from '../api/expense_type_api.js';
import { expenseContragentAPI } from '../api/expense_contragent_api.js';
import { userAPI } from "../api/user_api.js";
import IndexTableMixin from '../utils/indexTableMixin.js';
import ExpenseTypesModal from '../components/modals/ExpenseTypesModal.vue';
import ExpenseContragentsModal from '../components/modals/ExpenseContragentsModal.vue';
import DefaultModal from '../components/modals/default_modal.vue';
import TableEditInput from '../components/inputs/table_edit_input.vue';
import TrashButton from '../ui/buttons/TrashButton.vue';
import FilterInputBetween from '../ui/inputs/FilterInputBetween.vue';
import inputDefault from '../components/inputs/default_input.vue';
import AddButton from '../components/inputs/add_button.vue';
import draggable from 'vuedraggable';

export default {
    components: { 
        IndexTableMixin, DefaultModal, TableEditInput,
        TrashButton, FilterInputBetween, inputDefault,
        AddButton, draggable, ExpenseTypesModal,
        ExpenseContragentsModal
    },
    mixins: [IndexTableMixin],

    data() {
        return {
            users: [],
            expenses: [],
            expenseTypes: [],
            expenseContragents: [],
            summary: {},
            newExpenseFile: null,
            isFileValid: false,
            paymentStatuses: [
                { id: 0, name: 'Не платить (оплата не требуется)' },
                { id: 1, name: 'Оплачен' },
                { id: 2, name: 'Требует оплаты' }
            ],
            selectExportType: false,
            isEditingExpenseTypes: false,
            isEditingContragents: false,
            isFastExpenseModal: false,
            monthsList: [
                { id: null, name: 'Нет периода по месяцу' },
                { id: 1, name: 'Январь' },
                { id: 2, name: 'Февраль' },
                { id: 3, name: 'Март' },
                { id: 4, name: 'Апрель' },
                { id: 5, name: 'Май' },
                { id: 6, name: 'Июнь' },
                { id: 7, name: 'Июль' },
                { id: 8, name: 'Август' },
                { id: 9, name: 'Сентябрь' },
                { id: 10, name: 'Октябрь' },
                { id: 11, name: 'Ноябрь' },
                { id: 12, name: 'Декабрь' }
            ],
            yearsList: this.generateYearsList(),
            hasFileOptions: [
                { id: '1', name: 'Есть' },
                { id: '0', name: 'Нет' }
            ],
            isEdoOptions: [
                { id: '1', name: 'Да' },
                { id: '0', name: 'Нет' }
            ],
        }
    },
    methods: {
        initSettings() {
            this.settings.tableTitle = 'Хозяйственные расходы';
            this.settings.createButtonText = 'Новый расход';
            this.settings.additionalHeaderButtonText = 'Быстрый расход';
            this.settings.localStorageKey = 'expenses_params';

            this.settings.withCreateButton = this.canUserCreate;
            this.settings.withAdditionalHeaderButton = this.canUserCreateFast;
            this.settings.withHeader = false;
            this.settings.withExport = true;
            this.settings.isLoading = true;
            this.settings.saveParams = true;
            this.settings.withBottomBox = false;
            this.settings.withFilters = true;
            this.settings.withFilterTemplates = true;

            this.settings.indexAPI = params => expenseAPI.index(params);

            this.onInitData = res => {
                this.expenses = res.data.data;
                this.summary = res.data.summary;
            }

            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'id';
                defaultParams.sort_type = this.params.sort_type || 'desc';
            }

            this.onClickCreateButton = () => this.$router.push('/expenses/new');
            this.onClickAdditionalHeaderButton = () => this.isFastExpenseModal = true;

            this.onExport = () => this.selectExportType = true;
        },
        toExpense(id) {
            this.$router.push('/expenses/' + id + '/edit');
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
        goToSummaryPage() {
            this.$router.push('/expense-summary');
        },
        paymentStatus(status) {
            return this.paymentStatuses[status].name
        },
        getExpenseTypesString(expense) {
            // Сначала получаем уникальные ID типов расходов
            const uniqueTypeIds = [...new Set(expense.items.map(item => item?.expense_type_id ?? ''))];
            // Затем получаем имена для каждого уникального ID
            const typeNames = uniqueTypeIds
                .map(typeId => {
                    const type = this.expenseTypes.find(type => type.id === typeId);
                    return type ? type.name : '...';
                })
                .join(", "); // Преобразовываем массив имен в строку, разделенную запятыми
            return typeNames;
        },
        calculateTotalPrice(expense) {
            // Проверяем, что у расхода есть элементы и это массив
            if (expense.items && Array.isArray(expense.items)) {
                // Рассчитываем общую цену
                return expense.items.reduce((total, item) => {
                    return total + (item.price * item.amount);
                }, 0).toFixed(2);
            } else {
                // Если элементы отсутствуют, возвращаем 0
                return '0.00';
            }
        },
        downloadFile(response, fileName) {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName + '.xlsx');
            document.body.appendChild(link);
            link.click();
        },
        async onExpenseItemsExport() {
            const response = await expenseAPI.expense_items_export(this.params)
            this.downloadFile(response, 'Экспорт позиций хозяйственных расходов');
            this.showToast('Экспорт завершён', 'Экспорт позиций хозяйственных расходов завершён', 'success');
            this.selectExportType = false;
        },
        async onSortedExpenseItemsExport() {
            const response = await expenseAPI.expense_sorted_types_export(this.params)
            this.downloadFile(response, 'Экспорт типов расходов расходов');
            this.showToast('Экспорт завершён', 'Экспорт типов расходов завершён', 'success');
            this.selectExportType = false;
        },
        generateYearsList() {
            const currentYear = new Date().getFullYear();
            return [
                { id: null, name: 'Нет периода по году' },
                { id: currentYear, name: currentYear.toString() },
                { id: currentYear - 1, name: (currentYear - 1).toString() }
            ];
        },
        async loadExpenseTypes() {
            const res = await expenseTypeAPI.index();
            this.expenseTypes = res.data.data;
        },
        async loadExpenseContragents() {
            const res = await expenseContragentAPI.index();
            this.expenseContragents = res.data;
        },
        onFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                const validExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (validExtensions.includes(fileExtension)) {
                    this.newExpenseFile = file;
                    this.isFileValid = true;
                } else {
                    this.newExpenseFile = null;
                    this.isFileValid = false;
                    this.showToast('Недопустимый формат файла.', 'Пожалуйста, выберите файл с расширением .pdf, .jpg, .jpeg или .png.', 'danger');
                }
            } else {
                this.newExpenseFile = null;
                this.isFileValid = false;
            }
        },
        async onCreateFastExpense() {
            this.settings.isLoading = true;
            const formData = new FormData();
            formData.append('file', this.newExpenseFile);
            try {
                    const res = await expenseAPI.createFastExpense(formData);
                    this.showToast("OK", "Быстрый расход создан", "success");
                    window.location.reload();
                    this.settings.isLoading = false;
                    this.isFastExpenseModal = false;
                } catch (error) {
                    console.error("Ошибка создания быстрого расхода", error);
                    this.showToast("Ошибка!", error.message, "danger");
                }
        },
        setPaymentStatusCell(expense) {
            if (expense.is_need_to_complete) {
                return '-';
            } else {
                return this.paymentStatus(expense.is_paid);
            }
        },
    },
    computed: {
        ...mapGetters({ legalEntities: 'getLegalEntities' }),
        ...mapGetters({ paymentMethods: 'getPaymentMethods' }),
        canUserCreate() {
            return this.checkPermission('all_expenses_create') || this.checkPermission('expenses_create');
        },
        canUserCreateFast() {
            return this.checkPermission('all_expenses_create') || this.checkPermission('fast_expenses_create');
        },
        // Новый метод для проверки права на редактирование контрагентов
        canEditContragents() {
            return this.checkPermission('contragents_edit');
        },
        // Новый метод для проверки права на редактирование типов расходов
        canEditExpenseTypes() {
            return this.checkPermission('expense_types_edit');
        },
        payment_types_options() {
            if (this.expense.legal_entity_id !== null) {
                return this.paymentMethods.filter((element) => {
                    return element.legal_entity_id == this.expense.legal_entity_id;
                });
            } else {
                return [];
            }
        },
        formattedExpenseTypes() {
            console.log(this.expenseTypes);
            return this.expenseTypes.map(type => ({
                value: type.id,
                text: type.name
            }));
        },
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
        paymentMethodsWithLegalEntities() {
            this.paymentMethods.forEach(method => {
                method.name = method.legal_entity_name + " " + method.name;
            });
            return this.paymentMethods;
        }
    },
    mounted() {
        this.loadUsers();
        this.loadExpenseTypes();
        this.loadExpenseContragents();
        this.$store.dispatch('loadLegalEntities');
        this.$store.dispatch('loadPaymentMethods');
    }
}
</script>
