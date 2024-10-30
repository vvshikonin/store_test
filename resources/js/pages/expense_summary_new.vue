<template>
  <Table>
    <template v-slot:filters>
      <!-- <pre>{{ expenseTypes }}</pre> -->
    </template>
    <template v-slot:thead>
      <TH field="accounting_year">Год</TH>
      <TH field="accounting_month">Месяц</TH>
      <TH field="expense_type_id" v-for="expenseType in expenseTypes" :key="expenseType.id">{{ expenseType.name }}</TH>
      <TH field="total_income">Прибыль</TH>
      <TH field="total_expenses">Операционные расходы</TH>
      <TH>Финансовый результат</TH>
    </template>
    <template v-slot:tbody>
      <TR v-for="summary in expenseSummary" :key="summary.id">
        <TD>{{ summary.accounting_year }}</TD>
        <TD>{{ summary.accounting_month }}</TD>
        <TD v-for="expenseType in expenseTypes" :key="expenseType.id"></TD>
        <TD>{{ summary.total_income.priceFormat(true) }}</TD>
        <TD>-{{ summary.total_expenses.priceFormat(true) }}</TD>
        <TD>{{ getFinancialResult(summary).priceFormat(true) }}</TD>
        <template v-slot:sub-thead>
          <TH v-for="expenseType in expenseTypes" :key="expenseType.id">{{ expenseType.name }}</TH>
        </template>
        <template v-slot:sub-tbody>
      <TR>
        <TD></TD>
      </TR>
    </template>
    </TR>
</template>
<template v-slot:tfoot>
</template>
<template v-slot:info>
</template>
</Table>
<div>
  <h1>Отчёт о прибылях и убытках</h1>
  <div v-for="(period, periodIndex) in expenseSummary" :key="periodIndex" class="period-container">
    <div class="period-header">
      <div class="period-title">{{ getMonthName(period.accounting_month) }} {{ period.accounting_year }}</div>
      <div class="period-info">
        <div>
          <label class="bold">Прибыль по дате доставке (исходной): </label>
          <input v-model="period.total_income" @change="updateIncome(period)">
        </div>
        <div class="period-financial">
          <div>
            <span class="bold">Операционные расходы: </span><span>-{{ period.total_expenses.priceFormat(true) }}</span>
          </div>
          <div>
            <span class="bold">Финансовый результат: </span><span>{{ formatHeaderPrice(getFinancialResult(period))
              }}</span>
          </div>
        </div>
        <button @click="toggleDetails(period)" class="btn btn-primary btn-sm">{{ period.showDetails ? 'Свернуть' :
        'Развернуть' }}</button>
      </div>
    </div>
    <div class="table-responsive" v-if="!period.showDetails">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th style="width: 100px;">Тип расхода</th>
            <th v-for="expenseType in expenseTypes" :key="expenseType.id" class="expense-column">{{ expenseType.name }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="width: 100px;">Общая сумма</td>
            <td v-for="expenseType in expenseTypes" :key="expenseType.id"
              :class="{ 'highlight': getTotalExpense(period, expenseType.id) > 0 }">
              <span v-if="getTotalExpense(period, expenseType.id) > 0">-{{ formatPrice(getTotalExpense(period,
        expenseType.id)) }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="table-responsive" v-if="period.showDetails">
      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th style="width: 100px;">Дата оплаты</th>
            <th v-for="expenseType in expenseTypes" :key="expenseType.id" class="expense-column">{{ expenseType.name }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(payment, paymentIndex) in period.payments" :key="paymentIndex"
            :class="{ 'odd-row': paymentIndex % 2 !== 0 }">
            <td style="width: 100px;">{{ payment.expense.payment_date }}</td>
            <td v-for="expenseType in expenseTypes" :key="expenseType.id"
              :class="{ 'highlight': getExpenseAmount(payment.expense.items, expenseType.id) > 0, 'expense-column': true }"
              v-html="getFormattedExpense(payment, expenseType.id)">
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</template>
<script>
import { mapGetters } from 'vuex';
import IndexTableMixin from '../utils/indexTableMixin.js';
import { expenseSummaryAPI } from '../api/expense_summary_api';
import { expenseTypeAPI } from '../api/expense_type_api';

export default {
  components: { IndexTableMixin },
  mixins: [IndexTableMixin],

  data() {
    return {
      expenseSummary: [],
      expenseTypes: []
    };
  },
  methods: {
    initSettings() {
      this.settings.tableTitle = 'Отчёт о прибылях и убытках';
      // this.settings.createButtonText = 'Внести доход';
      this.settings.localStorageKey = 'expense_summary_params';

      this.settings.withCreateButton = false;
      this.settings.withHeader = false;
      this.settings.withExport = true;
      this.settings.isLoading = true;
      this.settings.saveParams = true;
      this.settings.withBottomBox = false;
      this.settings.withFilterTemplates = true;

      // await this.generateSummaries();
      this.settings.indexAPI = params => expenseSummaryAPI.index(params);

      this.onInitData = res => {
        this.expenseSummary = res.data.data;
        // this.financialControls = res.data.data;

        // this.totalManualSum = res.data.totalManualSum;
        // this.totalAutoSum = res.data.totalAutoSum;
        // this.totalDifference = res.data.totalDifference;

        // this.financialControls.forEach((financialControl) => {
        //   financialControl.finances = []
        // });
      }

      // this.onExport = () => this.toggleExportModal();

      this.onInitParamsDefault = defaultParams => {
        defaultParams.sort_field = this.params.sort_field || 'accounting_year';
        defaultParams.sort_type = this.params.sort_type || 'desc';
      }

      // this.onClickCreateButton = function () {
      //   this.editingFinance.payment_method = {
      //     legal_entity_id: null
      //   };

      //   this.modalWindowTitle = "Внести транзакцию";
      //   this.modalWindowSubmitButtonText = "Внести";
      //   this.isAddingFinance = true;

      //   this.saveFinance = async () => {
      //     this.isAddingFinance = false;
      //     await financialControlAPI.store(this.editingFinance);
      //     this.editingFinance = {};
      //     this.index();
      //   }
      // };

      // this.onExport = () => this.selectExportType = true;
    },
    async generateSummaries() {
      try {
        const res = await expenseSummaryAPI.generate();
        this.showToast('Успешно', 'Отчёт сгенерирован', 'info');
        console.log('Сводки расходов сгенерированы.');
      } catch (error) {
        this.showToast('Ошибка генерации', 'Ошибка при генерации сводок расходов.', 'danger');
        console.error('Ошибка при генерации сводок расходов:', error);
      }
    },
    async loadSummaries() {
      try {
        const response = await expenseSummaryAPI.index();
        this.expenseSummary = response.data.map(summary => ({ ...summary, showDetails: false, payments: [] }));
        if (this.expenseSummary.length === 0) {
          this.showToast('Ошибка загрузки', 'Нет доступных сводок расходов.', 'danger');
        } else {
          // this.showToast('Успешно', 'Периоды загружены', 'info');
          // this.loadExpenseItems(); // Загрузка сумм расходов сразу при загрузке страницы
        }
        console.log('Загруженные сводки расходов:', this.expenseSummary);
      } catch (error) {
        this.showToast('Ошибка загрузки', 'Ошибка при загрузке сводок расходов.', 'danger');
        console.error('Ошибка при загрузке сводок расходов:', error);
      }
    },
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
    async loadExpenseItems() {
      for (let period of this.expenseSummary) {
        try {
          const response = await expenseSummaryAPI.show(period.id);
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
      this.showToast('Успешно', 'Подробные сводки загружены', 'info');
    },
    toggleDetails(period) {
      period.showDetails = !period.showDetails;
    },
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
    getMonthName(month) {
      const months = [
        'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
      ];
      return months[month - 1];
    },
    formatDate(date) {
      const options = { day: '2-digit', month: '2-digit', year: 'numeric' };
      return new Date(date).toLocaleDateString('ru-RU', options);
    },
    formatHeaderPrice(value) {
      if (value && typeof value === 'number') {
        return `${value.priceFormat(true)}`;
      }
      return 'Не удалось отформатировать цену';
    },
    getFinancialResult(period) {
      return period.total_income - period.total_expenses;
    },
    getFormattedExpense(payment, typeId) {
      const expenseItems = payment.expense.items.filter(item => item.expense_type_id === typeId);
      let totalAmount = 0;
      expenseItems.forEach(item => {
        totalAmount += item.amount * item.price;
      });
      if (totalAmount > 0) {
        return `<a href="/#/expenses/${payment.expense.id}/edit">-${this.formatPrice(totalAmount)}</a>`;
      }
      return '';
    },
    getExpenseAmount(items, typeId) {
      const item = items.find(item => item.expense_type_id === typeId);
      return item ? item.amount * item.price : 0;
    },
    formatPrice(amount) {
      return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB'
      }).format(amount);
    },
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
        console.error('Ошибка при обновлении дохода по дате доставки:', error);
      }
    }
  },
  async mounted() {
    console.log('Монтирование компонента expense_summary.vue');
    this.showToast('Страница загружается', 'Идёт генерация отчёта. Пожалуйста, подождите...', 'info');
    await this.loadExpenseTypes();
    // await this.generateSummaries();
    // await this.loadSummaries();
    // await this.loadExpenseItems();
  }
};
</script>
<!-- <style scoped>
.period-container {
  margin-bottom: 20px;
  width: 100%;
}

.table-responsive {
  width: 100%;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.7em;
}

th,
td {
  border: 1px solid black;
  padding: 2px;
  height: 1em;
  word-wrap: break-word;
}

th {
  background-color: #f2f2f2;
  text-align: center;
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
</style> -->
