<template>
  <div>
    <h1>Отчёт о прибылях и убытках</h1>
    <div v-for="(period, periodIndex) in expenseSummary" :key="periodIndex" class="period-container">
      <div class="period-header">
        <div class="period-title"> {{ getMonthName(period.month) }} {{ period.year }} </div>
        <div class="period-info">
          <div>
            <label class="bold">Прибыль по дате доставке (исходной): </label>
            <input v-model="period.crmIncome" @change="updateCRMIncome(period)">
          </div>
          <div class="period-financial">
            <div>
              <span class="bold">Операционные расходы: </span><span>{{ formatHeaderPrice(period.totalExpenses) }}</span>
            </div>
            <div>
              <span class="bold">Финансовый результат: </span><span>{{ formatHeaderPrice(getFinancialResult(period))
                }}</span>
            </div>
          </div>
        </div>
      </div>
      <table>
        <thead>
          <tr>
            <th style="width: 100px;">Дата оплаты</th>
            <!-- Устанавливаем фиксированную ширину для колонки с датой оплаты -->
            <th v-for="type in expenseTypes" :key="type.id" class="expense-column">{{ type.name }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(payment, paymentIndex) in period.payments" :key="paymentIndex"
            :class="{ 'odd-row': paymentIndex % 2 !== 0 }">
            <td style="width: 100px;">{{ payment.payment_date }}</td>
            <!-- Устанавливаем фиксированную ширину для колонки с датой оплаты -->
            <td v-for="type in expenseTypes" :key="type.id"
              :class="{ 'highlight': payment.expenses[type.id]?.amount > 0, 'expense-column': true }"
              v-html="getFormattedExpense(payment, type.id)">
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
<style scoped>
.period-container {
  margin-bottom: 20px;
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
  /* Выровнять заголовки по центру */
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
  /* Фиксированная ширина для колонок с типами расходов */
}
</style>
<script>
import { expenseAPI } from '../api/expense_api';
import { expenseTypeAPI } from '../api/expense_type_api';

export default {
  data() {
    return {
      expenses: [],
      expenseTypes: [],
      expenseSummary: [],
      rawExpenseData: [],
      monthsList: [
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
    };
  },
  computed: {
    formattedExpenseSummary() {
      return this.expenseSummary.map(period => {
        return {
          ...period,
          totalExpenses: period.totalExpenses.priceFormat(true),
          payments: period.payments.map(payment => {
            const formattedExpenses = {};
            Object.keys(payment.expenses).forEach(key => {
              formattedExpenses[key] = {
                amount: payment.expenses[key].amount.priceFormat(true),
                id: payment.expenses[key].id
              };
            });
            return {
              ...payment,
              expenses: formattedExpenses
            };
          })
        };
      });
    }
  },
  methods: {
    async loadExpenses() {
      try {
        const res = await expenseAPI.index({
          sort_field: 'payment_date',
          sort_type: 'asc',
          no_paginate: 'true'
        });
        if (res.data && Array.isArray(res.data.data)) {
          this.rawExpenseData = res.data.data;
          console.log("Загруженные данные:", this.rawExpenseData);
          this.expenseSummary = this.processExpenseData(this.rawExpenseData);
        } else {
          console.error("Ошибка при загрузке расходов: данные не являются массивом");
        }
      } catch (error) {
        console.error("Ошибка при загрузке расходов:", error);
      }
    },
    async loadExpenseTypes() {
      try {
        const res = await expenseTypeAPI.index();
        if (res.data && Array.isArray(res.data.data)) {
          this.expenseTypes = res.data.data.sort((a, b) => a.sort_order - b.sort_order);
          console.log("Загруженные типы расходов:", this.expenseTypes);
        } else {
          console.error("Ошибка при получении типов расходов: данные не являются массивом");
        }
      } catch (error) {
        console.error("Ошибка при загрузке типов расходов:", error);
      }
    },
    getMonthName(month) {
      const months = [
        'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
        'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
      ];
      return months[month - 1];
    },
    processExpenseData(data) {
      const summary = {};

      data.forEach(expense => {
        if (!expense.is_paid) return;

        const accountingMonth = expense.accounting_month;
        const accountingYear = expense.accounting_year;

        if (accountingMonth === null || accountingYear === null) return;

        const payment_date = new Date(expense.payment_date).toLocaleDateString();
        const periodKey = `${accountingMonth}-${accountingYear}`;

        if (!summary[periodKey]) {
          summary[periodKey] = {
            month: accountingMonth,
            year: accountingYear,
            crmIncome: 0,
            totalExpenses: 0,
            payments: []
          };
        }

        const paymentSummary = {
          payment_date,
          expenses: {}
        };

        expense.items.forEach(item => {
          const expenseTypeId = item.expense_type_id;
          if (!expenseTypeId) return; // Пропуск значений с null

          const expenseAmount = parseFloat(item.price) * item.amount;

          if (!paymentSummary.expenses[expenseTypeId]) {
            paymentSummary.expenses[expenseTypeId] = {
              amount: 0,
              id: expense.id // Сохраняем ID расхода
            };
          }

          paymentSummary.expenses[expenseTypeId].amount += expenseAmount;
        });

        summary[periodKey].payments.push(paymentSummary);
      });

      const summaryArray = Object.values(summary).sort((a, b) => new Date(b.year, b.month - 1) - new Date(a.year, a.month - 1));

      summaryArray.forEach(period => {
        period.payments.sort((a, b) => new Date(a.payment_date) - new Date(b.payment_date));

        let recalculatedTotalExpenses = 0;
        period.payments.forEach(payment => {
          Object.entries(payment.expenses).forEach(([key, value]) => {
            recalculatedTotalExpenses += value.amount;
            console.log(`Период: ${period.month}-${period.year}, Тип расхода: ${key}, Значение расходов: ${value.amount}, Текущая сумма: ${recalculatedTotalExpenses}`);
          });
        });
        period.totalExpenses = recalculatedTotalExpenses;
        console.log(`Период: ${period.month}-${period.year}, Пересчитанные операционные расходы: ${period.totalExpenses}`);
      });

      console.log("Сгруппированные данные по периодам:", summaryArray);

      return summaryArray;
    },
    formatPrice(value) {
      if (value && typeof value === 'number') {
        return `-${value.priceFormat(true)}`; // Добавляем знак минус перед значением
      }
      return '';
    },
    formatHeaderPrice(value) {
      if (value && typeof value === 'number') {
        return `${value.priceFormat(true)}`; // Форматирование без добавления знака минус
      }
      return '';
    },
    getFinancialResult(period) {
      return period.crmIncome - period.totalExpenses;
    },
    getFormattedExpense(payment, typeId) {
      const expense = payment.expenses[typeId];
      if (expense && expense.amount > 0) {
        return `<a href="/#/expenses/${expense.id}/edit">${this.formatPrice(expense.amount)}</a>`;
      }
      return '';
    },
    async updateCRMIncome(period) {
      try {
        await expenseAPI.updateCRMIncome({ month: period.month, year: period.year, crmIncome: period.crmIncome });
        console.log('Доход по CRM обновлен');
      } catch (error) {
        console.error("Ошибка при обновлении дохода по CRM:", error);
      }
    },
    async saveSummary(period) {
      try {
        const response = await expenseAPI.saveSummary(period);
        console.log('Сводка сохранена');
      } catch (error) {
        console.error("Ошибка при сохранении сводки:", error);
      }
    }
  },
  mounted() {
    console.log("Монтирование компонента expense_summary.vue");
    this.loadExpenses();
    this.loadExpenseTypes();
  }

};
</script>
