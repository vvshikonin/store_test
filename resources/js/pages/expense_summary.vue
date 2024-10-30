<template>
    <div style="background-color: white; border-radius: 5px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
        <div class="p-1">
            <h1 class="ms-2">Отчёт о прибылях и убытках</h1>
            <div class="row mb-0">
                <div class="col-md-4 p-0">
                    <MultiSelectInput label="Фильтр по месяцу" id="monthFilter"
                        :options="monthsList.map((month, index) => ({ id: index + 1, name: month }))"
                        v-model="filters.accounting_months_filter" placeholder="Все месяцы" />
                </div>
                <div class="col-md-4">
                    <MultiSelectInput label="Фильтр по году" id="yearFilter" :options="yearsList"
                        v-model="filters.accounting_years_filter" placeholder="Все годы" />
                </div>
                <div class="col-md-4 p-0">
                    <MultiSelectInput label="Фильтр по юр. лицу" id="legalEntityFilter" :options="legalEntityList"
                        v-model="legalEntityFilter" placeholder="Все юр.лица" />
                </div>
            </div>
            <div class="row mb-3 ms-2">
                <div class="col-md-6 text-start">
                    <button title="Применить установленные фильтры" class="btn btn-sm btn-primary me-2"
                        @click="applyFilters">Применить</button>
                    <button title="Очистить установленные фильтры" class="btn btn-sm btn-secondary"
                        @click="resetFilters" :disabled="isFiltersDefault">Очистить</button>
                </div>
                <div class="col-md-6 text-end d-flex justify-content-end align-items-center">
                    <button title="Актурализировать данные в отчёте" class="btn btn-sm btn-warning me-2"
                        @click="regenerateSummaries" :disabled="isLoading">Перегенерировать</button>
                    <div class="dropdown" @mouseleave="closeDropdown">
                        <button class="btn btn-sm btn-success me-2" type="button" :disabled="isLoading"
                            @click="toggleDropdown">Экспорт</button>
                        <ul class="dropdown-menu dropdown-menu-end" v-if="isDropdownOpen"
                            @mouseenter="keepDropdownOpen">
                            <li><a class="dropdown-item" @click="exportReport('regular')">Сгруппированный</a></li>
                            <li><a class="dropdown-item" @click="exportReport('detailed')">Детализированный</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div v-if="isLoading">
                <span class="text-dark ms-2 mt-5 mb-5">Идёт генерация отчёта. Пожалуйста, подождите...</span>
            </div>
            <div v-for="(period, periodIndex) in expenseSummary" :key="periodIndex" class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="period-title">{{ getMonthName(period.accounting_month) }} {{ period.accounting_year }}
                    </div>
                    <div class="period-info">
                        <div>
                            <label class="bold">Прибыль по дате доставки (исходной): </label>
                            <input v-model="period.total_income">
                            <button :disabled="period.total_income === '0.00' || !period.total_income"
                                type="button" @click="updateIncome(period)"
                                class="btn btn-light border border-secondary bg-opacity ms-1 btn-sm">
                                <font-awesome-icon class="text-success" icon="fa-solid fa-plus" />
                            </button>
                        </div>
                        <div class="period-financial">
                            <div>
                                <span class="bold">Операционные расходы: </span>
                                <span>-{{ formatPrice(period.total_expenses, true) }}</span>
                            </div>
                            <div>
                                <span class="bold">Финансовый результат: </span>
                                <span>{{ formatPrice(getFinancialResult(period), true) }}</span>
                            </div>
                        </div>
                        <button @click="toggleDetails(period)" class="btn btn-primary btn-sm mt-2">{{ period.showDetails
                            ? 'Свернуть' : 'Развернуть' }}</button>
                    </div>
                </div>
                <div class="card-body" v-if="!period.showDetails">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Тип расхода</th>
                                <th v-for="expenseType in expenseTypes" :key="expenseType.id" class="expense-column">{{
                                    expenseType.name }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="width: 100px;">Сумма</td>
                                <td v-for="expenseType in expenseTypes" :key="expenseType.id"
                                    :class="{ 'highlight': getTotalExpense(period, expenseType.id) > 0 }">
                                    <span v-if="getTotalExpense(period, expenseType.id) > 0">-{{
                                        formatPrice(getTotalExpense(period, expenseType.id), false) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-body table-responsive" v-if="period.showDetails">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Дата оплаты</th>
                                <th v-for="expenseType in expenseTypes" :key="expenseType.id" class="expense-column">{{
                                    expenseType.name }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Добавляем строку с итоговыми суммами в развернутый вид -->
                            <tr>
                                <td style="width: 100px;">Сумма</td>
                                <td v-for="expenseType in expenseTypes" :key="expenseType.id"
                                    :class="{ 'highlight': getTotalExpense(period, expenseType.id) > 0 }">
                                    <span v-if="getTotalExpense(period, expenseType.id) > 0">-{{
                                        formatPrice(getTotalExpense(period, expenseType.id), false) }}</span>
                                </td>
                            </tr>
                            <!-- Конец строки с итоговыми суммами в развернутом виде -->
                            <tr v-for="(payment, paymentIndex) in period.payments" :key="paymentIndex"
                                :class="{ 'odd-row': paymentIndex % 2 !== 0 }">
                                <!-- <template v-if="isLegalEntitySelected(payment.expense.legal_entity_id)"> -->
                                    <td style="width: 100px;">{{ payment.expense.payment_date }}</td>
                                    <td v-for="expenseType in expenseTypes" :key="expenseType.id"
                                        :class="{ 'highlight': getExpenseAmount(payment.expense.items, expenseType.id) > 0, 'expense-column': true }"
                                        v-html="getFormattedExpense(payment, expenseType.id)">
                                    </td>
                                <!-- </template> -->
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { expenseSummaryAPI } from '../api/expense_summary_api';
import { expenseTypeAPI } from '../api/expense_type_api';
// import SearchSelector from '../components/inputs/select_input_search.vue';
import MultiSelectInput from '../components/inputs/multiselect_input_expense_summary.vue';
import FilterInputBetween from '../ui/inputs/filter_input_between_expense_summary.vue';

export default {
    components: { MultiSelectInput, FilterInputBetween },

    data() {
        return {
            expenseSummary: [],
            expenseTypes: [],
            filters: {
                accounting_months_filter: [],
                accounting_years_filter: [],
            },
            legalEntityFilter: [],
            monthsList: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
            yearsList: this.generateYearsList(),
            legalEntityList: [
                {'id': 1, 'name': 'ИП Грибанов'},
                {'id': 2, 'name': 'ИП Шиконин'}
            ],
            isLoading: true,
            isDropdownOpen: false,
        };
    },
    methods: {
        async regenerateSummaries() {
            this.isLoading = true;
            this.expenseSummary = [];
            await this.generateSummaries();
            await this.loadSummaries();
        },
        // Генерация сводок расходов
        async generateSummaries() {
            this.isLoading = true;
            this.expenseSummary = [];
            try {
                const res = await expenseSummaryAPI.generate();
                // this.showToast('Успешно', 'Отчёт сгенерирован', 'info');
                console.log('Сводки расходов сгенерированы.');
                this.isLoading = false;
            } catch (error) {
                this.showToast('Ошибка генерации', 'Ошибка при генерации сводок расходов.', 'danger');
                console.error('Ошибка при генерации сводок расходов:', error);
            }
        },
        // Загрузка сводок расходов
        async loadSummaries() {
            try {
                const params = {
                    accounting_months_filter: this.filters.accounting_months_filter,
                    accounting_years_filter: this.filters.accounting_years_filter,
                    legal_entity_filter: this.legalEntityFilter,
                };
                const response = await expenseSummaryAPI.index(params);
                this.expenseSummary = response.data.data.map(summary => ({ ...summary, showDetails: false, payments: [] }));
                if (this.expenseSummary.length === 0) {
                    this.showToast('Нет данных', 'Нет доступных сводок расходов. Сбросьте фильтры и попробуйте ещё раз.', 'warning');
                }
                await this.loadExpenseItems();
                console.log('Загруженные сводки расходов:', this.expenseSummary);
            } catch (error) {
                this.showToast('Ошибка загрузки', 'Ошибка при загрузке сводок расходов.', 'danger');
                console.error('Ошибка при загрузке сводок расходов:', error);
            }
        },
        applyFilters() {
            this.loadSummaries();
        },
        resetFilters() {
            this.filters.accounting_months_filter = [];
            this.filters.accounting_years_filter = [];
            this.legalEntityFilter = [];
            this.loadSummaries();
        },
        // Загрузка типов расходов
        async loadExpenseTypes() {
            try {
                const response = await expenseTypeAPI.index();
                this.expenseTypes = response.data.data;
                // this.showToast('Успешно', 'Типы загружены', 'info');
                console.log('Загруженные типы расходов:', this.expenseTypes);
            } catch (error) {
                this.showToast('Ошибка загрузки', 'Ошибка при загрузке типов расходов.', 'danger');
                console.error('Ошибка при загрузке типов расходов:', error);
            }
        },
        // Загрузка деталей расходов для каждого периода
        async loadExpenseItems() {
            for (let period of this.expenseSummary) {
                try {
                    const response = await expenseSummaryAPI.show(period.id, { legalEntityFilter: this.legalEntityFilter});
                    period.payments = response.data.map(payment => {
                        payment.expense.items = payment.expense.items.map(item => ({
                            ...item,
                            expense: payment.expense
                        }));
                        payment.expense.payment_date = this.formatDate(payment.expense.payment_date);
                        return payment;
                    });
                } catch (error) {
                    this.showToast('Ошибка', 'Не удалось получить детали периода', 'danger');
                    console.error('Ошибка при загрузке деталей расходов:', error);
                }
            }
            // this.showToast('Успешно', 'Подробные сводки загружены', 'info');
        },
        // Переключение отображения деталей периода
        toggleDetails(period) {
            period.showDetails = !period.showDetails;
        },
        // Получение общей суммы расходов по типу для периода
        getTotalExpense(period, typeId) {
            let total = 0;
            period.payments.forEach(payment => {
                payment.expense.items.forEach(item => {
                    if (item.expense_type_id === typeId) {
                        total += item.amount * item.price;
                    }
                });
            });
            return total > 0 ? total : '';
        },
        // Получение названия месяца по номеру
        getMonthName(month) {
            const months = [
                'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
            ];
            return months[month - 1];
        },
        // Форматирование даты
        formatDate(date) {
            const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
            return new Date(date).toLocaleDateString('ru-RU', options);
        },
        // Форматирование цены для заголовка
        formatHeaderPrice(value) {
            if (value && typeof value === 'number') {
                return `${value.priceFormat(true)}`;
            }
            return 'Не удалось отформатировать цену';
        },
        // Получение финансового результата для периода
        getFinancialResult(period) {
            return period.total_income - period.total_expenses;
        },
        // Получение отформатированной строки расходов
        getFormattedExpense(payment, typeId) {
            const expenseItems = payment.expense.items.filter(item => item.expense_type_id === typeId);
            let totalAmount = 0;
            expenseItems.forEach(item => {
                totalAmount += item.amount * item.price;
            });
            if (totalAmount > 0) {
                return `<a href="/#/expenses/${payment.expense.id}/edit">-${this.formatPrice(totalAmount, false)}</a>`;
            }
            return '';
        },
        // Получение суммы расходов по типу из списка элементов
        getExpenseAmount(items, typeId) {
            const item = items.find(item => item.expense_type_id === typeId);
            return item ? item.amount * item.price : 0;
        },
        // Форматирование цены
        formatPrice(amount, includeCurrency = true) {
            const options = {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            };
            if (!includeCurrency) {
                options.style = 'decimal';
            }
            return new Intl.NumberFormat('ru-RU', options).format(amount);
        },
        // Обновление дохода для периода
        async updateIncome(period) {
            try {
                const totalIncome = parseFloat(period.total_income);
                if (isNaN(totalIncome)) {
                    throw new Error('Значение дохода должно быть числом');
                }
                const payload = { total_income: totalIncome };
                console.log('Отправляемый payload:', payload); // Логирование отправляемых данных
                await expenseSummaryAPI.update(period.id, payload);
                this.showToast('Обновление успешно', 'Доход по дате доставки обновлен.', 'success');
            } catch (error) {
                this.showToast('Ошибка обновления', error.message || 'Ошибка при обновлении дохода по дате доставки.', 'danger');
                console.error('Ошибка при обновлении дохода по дате доставки: ', error);
            }
        },
        generateYearsList() {
            const currentYear = new Date().getFullYear();
            return [
                { id: currentYear, name: currentYear.toString() },
                { id: currentYear - 1, name: (currentYear - 1).toString() }
            ];
        },
        toggleDropdown() {
            this.isDropdownOpen = !this.isDropdownOpen;
            console.log('toggleDropdown method; Dropdown state:', this.isDropdownOpen); // Отладочное сообщение
        },
        closeDropdown() {
            this.closeDropdownTimeout = setTimeout(() => {
                this.isDropdownOpen = false;
            }, 150); // Задержка в 300 мс
            console.log('closeDropdown method; Dropdown state:', this.isDropdownOpen); // Отладочное сообщение
        },
        keepDropdownOpen() {
            clearTimeout(this.closeDropdownTimeout);
            console.log('keepDropdownOpen method; Dropdown state:', this.isDropdownOpen); // Отладочное сообщение
        },
        exportReport(type) {
            // this.isLoading = true;
            this.showToast('Экспорт начат', 'Готовится файл экспорта. Пожалуйста, подождите...', 'info');
            this.closeDropdown();
            const params = {
                // accounting_month_filter: this.filters.accounting_month_filter,
                // accounting_year_filter: this.filters.accounting_year_filter,
                accounting_months_filter: this.filters.accounting_months_filter,
                accounting_years_filter: this.filters.accounting_years_filter,
                legal_entity_filter: this.legalEntityFilter
            };
            if (type === 'regular') {
                expenseSummaryAPI.exportRegular(params).then(response => {
                    this.downloadFile(response, 'Экспорт сводного отчёта');
                    this.showToast('Экспорт завершён', 'Экспорт сводного отчёта завершён', 'success');
                    // this.isLoading = false;
                }).catch(error => {
                    console.error('Ошибка при экспорте обычного отчёта:', error);
                    this.showToast('Ошибка экспорта', 'Ошибка при экспорте обычного отчёта', 'danger');
                    // this.isLoading = false;
                });
            } else if (type === 'detailed') {
                expenseSummaryAPI.exportDetailed(params).then(response => {
                    this.downloadFile(response, 'Экспорт детализированного отчёта');
                    this.showToast('Экспорт завершён', 'Экспорт детализированного отчёта завершён', 'success');
                    // this.isLoading = false;
                }).catch(error => {
                    console.error('Ошибка при экспорте детализированного отчёта:', error);
                    this.showToast('Ошибка экспорта', 'Ошибка при экспорте детализированного отчёта', 'danger');
                    // this.isLoading = false;
                });
            }
        },
        downloadFile(response, fileName) {
            const url = window.URL.createObjectURL(new Blob([response.data], { type: response.headers['content-type'] }));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName + '.xlsx');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },
    },
    computed: {
        availableYears() {
            const years = this.expenseSummary.map(period => period.accounting_year);
            return [...new Set(years)];
        },
        isFiltersDefault() {
            return this.filters.accounting_months_filter.length === 0
                && this.filters.accounting_years_filter.length === 0
                && this.legalEntityFilter.length === 0;
        },
    },
    async mounted() {
        console.log('Монтирование компонента expense_summary.vue');
        // this.showToast('Страница загружается', 'Идёт генерация отчёта. Пожалуйста, подождите...', 'info');
        await this.generateSummaries(); // Дождаемся завершения generateSummaries
        await this.loadExpenseTypes();
        await this.loadSummaries();
        // await this.loadExpenseItems();
    }
};
</script>
<style scoped>
.period-container {
    margin-bottom: 20px;
    width: 100%;
}

.table-responsive {
    width: 100%;
    /* background-color: #f0f8ff; */
    overflow-x: auto;
    /* Добавляем горизонтальный скролл */
}

.table-responsive tbody tr:hover {
    background-color: #f0f8ff;
    /* Цвет фона при наведении */
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.7em;
    table-layout: fixed;
    /* Добавляем фиксированную ширину колонок */
}

th,
td {
    border: 1px solid rgb(121, 121, 121);
    padding: 2px;
    height: 1em;
    word-wrap: break-word;
    text-align: center;
    /* Центрируем текст в ячейках */
    vertical-align: middle;
    /* Центрируем текст по вертикали */
    min-width: 10px;
    /* Минимальная ширина колонок */
    max-width: 10px;
    /* Максимальная ширина колонок */
}

th {
    background-color: #f2f2f2;
    text-align: center;
    /* Центрируем текст в заголовках */
    vertical-align: middle;
    /* Центрируем текст по вертикали */
    font-size: 0.8em;
    /* Уменьшаем шрифт в заголовках */
}

.period-header {
    display: flex;
    justify-content: space-between;
    background-color: #eaeaea;
    padding: 5px;
    margin-top: 20px;
    align-items: center;
}

.period-title {
    font-weight: bold;
    font-size: 1.2em;
    flex: 1;
}

.period-info {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    font-size: 0.8em;
    text-align: right;
}

.period-financial {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
}

.bold {
    font-weight: bold;
}

.highlight {
    background-color: #c0ffc0;
}

.odd-row {
    background-color: #f9f9f9;
}

input {
    font-size: 0.8em;
    padding: 2px;
    width: 100px;
}

.expense-column {
    width: 100px;
}

.card {
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    max-width: 100%;
    /* Максимальная ширина карточки */
    overflow-x: auto;
    /* Добавляем горизонтальный скролл для карточек */
}

.card-header {
    background-color: #f7f7f7;
    border-bottom: 1px solid #ddd;
    padding: 10px;
}

.card-body {
    padding: 0;
    /* Убираем паддинг у таблицы внутри карточки */
    overflow-x: auto;
    /* Добавляем горизонтальный скролл для таблиц */
}

.row {
    margin-right: 0;
    /* Убираем отступы справа */
    margin-left: 0;
    /* Убираем отступы слева */
}

.col-md-6,
.col-md-12 {
    padding-right: 0;
    /* Убираем отступы справа */
    padding-left: 0;
    /* Убираем отступы слева */
}

.container {
    max-width: 100%;
    /* Ограничиваем максимальную ширину контейнера */
    overflow-x: hidden;
    /* Убираем горизонтальный скролл */
}

.red-background {
    background-color: red;
    max-width: 100%;
    /* Ограничиваем максимальную ширину */
    overflow-x: hidden;
    /* Убираем горизонтальный скролл */
}

.dropdown-menu {
    display: block;
    position: absolute;
    top: 100%;
    right: 0;
    /* Выравнивание по правому краю */
    z-index: 1000;
    float: left;
    min-width: 10rem;
    padding: 0.5rem 0;
    margin: 0.125rem 0 0;
    font-size: 1rem;
    color: #212529;
    text-align: left;
    list-style: none;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.175);
}

.dropdown-item {
    display: block;
    width: 100%;
    padding: 0.25rem 1.5rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
    cursor: pointer;
    /* Добавляем курсор-палец */
}

.dropdown-item.disabled {
    pointer-events: none;
    opacity: 0.6;
}

.dropdown-item:hover {
    color: #1d2124;
    text-decoration: none;
    background-color: #f8f9fa;
}
</style>