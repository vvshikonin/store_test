<template>
    <EntityLayout :loadingCover="isCover" :CancelDisabled="!isLoaded" :isLoaded="isLoaded" :withSaveButton="true"
        @save="formUpdateRequest()" @exit="onExit()" :withDeleteButton="false">
        <template v-slot:header>
            <div class="bg-primary bg-gradient p-3 rounded shadow-sm text-white w-100">
                <div>
                    <h3 class="d-flex flex-row m-0">Возврат ДС №{{ id }}</h3>
                </div>
                <div v-if="moneyRefund.converted_to_expense_id">
                    <small>Конвертирован в хозяйственный расход:</small>
                    <!-- <small class="ps-1">{{ moneyRefund.converted_to_expense_id }}</small> -->
                    <strong class="ps-1">{{ formatDate(moneyRefund.converted_to_expense_at, "DD.MM.YYYY HH:mm:ss")
                        }}</strong>
                </div>
                <div v-if="moneyRefund.creator?.name" class="d-inline me-1">
                    <small>Создал:</small>
                    <strong class="ps-1">{{ moneyRefund.creator.name }}</strong>
                </div>
                <div class="d-inline me-1">
                    <small>Создан:</small>
                    <strong class="ps-1">{{ formatDate(moneyRefund.created_at, "DD.MM.YYYY HH:mm:ss") }}</strong>
                </div>
                <div v-if="moneyRefund.updater?.name" class="d-inline me-1">
                    <small>Обновил:</small>
                    <strong class="ps-1">{{ moneyRefund.updater.name }}</strong>
                </div>
                <div class="d-inline me-1">
                    <small>Обновлён:</small>
                    <strong class="ps-1">{{ formatDate(moneyRefund.updated_at, "DD.MM.YYYY HH:mm:ss") }}</strong>
                </div>
            </div>
        </template>
        <template v-slot:content>
            <Card title="Данные о возврате денежных средств">
                <template v-slot:top>
                    <div class="d-flex">
                        <!-- Кнопка для конвертации в хозяйственный расход -->
                        <button v-if="!isAlreadyConverted && !isRefundConvertFromExpense" @click="showConfirmModal" type="button"
                            class="btn btn-danger me-2" :disabled="isConvertButtonDisabled"> Конвертировать в
                            хозяйственный расход </button>
                        <!-- Кнопка для перехода на страницу хозяйственного расхода -->
                        <button v-else @click="goToExpensePage" type="button" class="btn btn-primary me-2"> Перейти к
                            хозяйственному расходу </button>
                        <FileInput v-model:modelValue="moneyRefund.refund_doc_file" ref="refundFileInput"
                            :btnTitle="'Загрузить файл'" :fileLabel="'Возвратный документ'" @upload="uploadRefundFile"
                            @download="downloadRefundFile"></FileInput>
                    </div>
                </template>
                <inputDefault label="Сумма" v-model="moneyRefund.debt_sum" type="number" disabled></inputDefault>
                <inputDefault label="Возвращено по факту" v-model="moneyRefund.refund_sum_money" type="number"
                    step="0.01" min="0" :max="moneyRefund.debt_sum - moneyRefund.refund_sum_products
                        " placeholder="Укажите фактическую сумму возврата" disabled></inputDefault>
                <inputDefault label="Поставщики/Контрагент" v-model="contractorContragentSwitch" disabled></inputDefault>
                <inputDefault label="Оплата счетов" type="number" step="0.01" v-model="moneyRefund.refund_sum_products"
                    :disabled="true"></inputDefault>
                <inputDefault label="Дата создания" type="date" v-model="moneyRefund.created_at" :disabled="!isManual">
                </inputDefault>
                <inputDefault label="Юр.лицо" v-model="moneyRefund.legal_entity_name" disabled></inputDefault>
                <inputDefault label="Источник оплаты" v-model="moneyRefund.payment_method_name" disabled></inputDefault>
                <Select label="Статус возврата" v-model="moneyRefund.status" :options="statusOptions"
                    placeholder="Выберите статус возврата" :disabled="!canUserUpdateMoneyRefund"></Select>
                <inputDefault label="Дата возврата" type="date" v-model="moneyRefund.completed_at"
                    :disabled="!refundDateEnabled">
                </inputDefault>
                <Checkbox v-if="moneyRefund.is_main_contractor" v-model="moneyRefund.is_deduction_made"
                    :disabled="!canUserUpdateMoneyRefund" :title="'Сделан вычет'"></Checkbox>
                <Checkbox v-model="moneyRefund.approved" :disabled="true" :title="'Подтверждено директором'"></Checkbox>
                <textAreaDefault label="Комментарий" v-model="moneyRefund.comment" class="w-100"
                    placeholder="Укажите комментарий (опционально)" :disabled="!canUserUpdateMoneyRefund">
                </textAreaDefault>
            </Card>
            <Card title="Поступления">
                <div class="m-3">
                    <button v-if="canUserUpdateMoneyRefund" @click="addIncome()" type="button" class="btn btn-success">
                        <font-awesome-icon :icon="['fas', 'plus']" /> Добавить поступление </button>
                </div>
                <Table v-if="incomes.length" class="w-100 border-start-0 border-end-0 border-bottom-0">
                    <template #thead>
                        <TH> Тип </TH>
                        <TH> Способ </TH>
                        <TH> Сумма </TH>
                        <TH> Дата </TH>
                        <TH></TH>
                    </template>
                    <template #tbody>
                        <TR v-for="(income, index) in incomes" :key="`${moneyRefund.id}-${index}`">
                            <TD v-if="income.is_for_expense"> Перевод в хозяйственный расход </TD>
                            <TD v-else> Возврат ДС </TD>
                            <TD> {{ key }} <Select v-model="income.payment_method_id" :options="paymentMethodOptions"
                                    :disabled="!canUserUpdateMoneyRefund" placeholder="Выбрать способ оплаты" required
                                    class="w-100">
                                </Select>
                            </TD>
                            <TD>
                                <inputDefault v-model="income.sum" type="number" step="0.01" min="0" class="w-100"
                                    placeholder="Укажите фактическую сумму возврата"
                                    :disabled="!canUserUpdateMoneyRefund" required />
                            </TD>
                            <TD>
                                <inputDefault v-model="income.date" type="date" class="w-100"
                                    :disabled="!canUserUpdateMoneyRefund" required />
                            </TD>
                            <TD>
                                <a v-if="canUserUpdateMoneyRefund" @click.prevent="removeIncome(index)" href="#"
                                    class="link-danger"> Удалить </a>
                            </TD>
                        </TR>
                    </template>
                </Table>
                <div v-else class="w-100 d-flex flex-row justify-content-center">
                    <h1 class="m-5" style="font-size: 200px; color: #dadce0"> {{ this.randomKaomoji() }} </h1>
                </div>
            </Card>
            <DefaultModal :width="'600px'" @close_modal="hideConfirmModal" v-if="confirmModal" ref="confirmModal"
                title="Подтвердите действие">
                <div class="p-3">
                    <p style="font-weight: bold;">Вы уверены, что хотите конвертировать Возврат ДС в хозяйственный
                        расход?</p>
                    <p><i>- Вы можете перенести Возврат ДС в хозяйственный расход, если возврата денежных средств по
                            этому Возврату ДС не ожидается.</i></p>
                    <p><i>- Данные из этого Возврата ДС будут перенесены в новый хозяйственный расход.</i></p>
                    <p><i>- После конвертации Возврат ДС будет всё ещё доступен для просмотра, но не для
                            редактирования.</i></p>
                    <p><i>- Хозяйственный расход будет создан на не вернувшуюся сумму возврата: <b>{{ totalRefund }} рублей.</b></i></p>
                    <p><i>- На эту же сумму будет дозакрыт этот Возврат ДС.</i></p>
                    <p><i>- После конвертации сумму можно будет изменить в хозяйственном расходе</i></p>
                    <p style="font-weight: bold; color: red;">Эта операция необратима. Пожалуйста, убедитесь в правильности
                        выполнения операции перед конвертацией.</p>
                </div>
                <div class="d-flex justify-content-end p-2">
                    <button @click="hideConfirmModal" type="button" class="btn btn-secondary me-2">Отмена</button>
                    <button @click="convertToExpense" type="button" class="btn btn-danger">Конвертировать</button>
                </div>
            </DefaultModal>
        </template>
    </EntityLayout>
</template>
<script>
import { moneyRefundAPI } from "../api/money_refund_api";
import { paymentMethodAPI } from "../api/payment_method_api";
import EntityLayout from "../components/Layout/entity_edit_page.vue";
import indexTableMixin from "../utils/indexTableMixin";
import Card from "../components/Layout/card.vue";
import inputDefault from "../components/inputs/default_input.vue";
import textAreaDefault from "../components/inputs/DefaultTextarea.vue";
import Select from "../components/inputs/select_input.vue";
import Checkbox from "../ui/checkboxes/DefaultCheckbox.vue";
import FileInput from "../ui/inputs/FileInput.vue";
import SingleSelect from "../ui/selects/SingleSelect.vue";
import NoEntries from "../components/Tables v2/NoEntries.vue";
import DefaultModal from '../components/modals/default_modal.vue';

export default {
    mixins: [indexTableMixin],
    components: {
        EntityLayout,
        Card,
        inputDefault,
        textAreaDefault,
        Select,
        Checkbox,
        FileInput,
        SingleSelect,
        NoEntries,
        DefaultModal,
    },

    data() {
        return {
            id: this.$route.params.money_refund_id,
            moneyRefund: null,
            isCover: false,
            isLoaded: false,
            typeRefund: "App\\Models\\V1\\ProductRefund",
            typeDefect: "App\\Models\\V1\\Defect",
            typeInvoice: "App\\Models\\V1\\Invoice",
            statusOptions: [
                { id: 1, name: "Сделан" },
                { id: 0, name: "Не сделан" },
            ],
            newIncomes: [],
            deletedIncomes: [],
            confirmModal: false
        };
    },
    async mounted() {
        await paymentMethodAPI.index().then((res) => {
            this.paymentMethodOptions = res.data.data;
            this.paymentMethodOptions.forEach((method) => {
                method.name = method.legal_entity_name + " " + method.name;
            });
        });

        if (this.isEditing) {
            this.initMoneyRefundData();
        } else {
            this.moneyRefund = {};
            this.isLoaded = true;
        }
    },
    methods: {
        async initSettings() {
            this.settings.withInfo = false;
            this.settings.withTitle = false;
            this.settings.isLoading = false;
            this.settings.withHeader = false;
            this.settings.withFooter = false;
            this.settings.saveParams = false;
            this.settings.withExport = false;
            this.settings.withFilters = false;
            this.settings.withPagination = false;
            this.settings.withCreateButton = false;
        },
        async initMoneyRefundData() {
            const res = await moneyRefundAPI.show(this.id);
            if (res) {
                this.moneyRefund = res.data.data;
                this.newIncomes = [];
                this.deletedIncomes = [];
            }
            this.isCover = false;
            this.isLoaded = true;
        },
        async sendSaveRequest(data) {
            this.isCover = true;
            const res = await moneyRefundAPI.update(this.id, data);
            if (res) {
                this.initMoneyRefundData();
                this.showToast("OK", "Обновление завершено!", "success");
                this.isCover = false;
            }
        },
        formUpdateRequest() {
            this.showToast(
                "Отправка информации",
                "Выполняется обновление данных на сервере...",
                "info"
            );
            const requestData = {
                refund_sum_money: this.moneyRefund.refund_sum_money,
                comment: this.moneyRefund.comment,
                status: this.moneyRefund.status,
                is_deduction_made: this.moneyRefund.is_deduction_made ? 1 : 0,
                approved: this.moneyRefund.approved ? 1 : 0,
                completed_at: this.moneyRefund.completed_at,
                created_at: this.moneyRefund.created_at,
                incomes: this.moneyRefund.incomes,
                new_incomes: this.newIncomes,
                deleted_incomes: this.deletedIncomes,
            };
            this.sendSaveRequest(requestData);
        },
        async uploadRefundFile() {
            this.showToast(
                "Отправка файла",
                "Выполняется загрузка файла на сервер...",
                "info"
            );
            const formFileData = new FormData();
            if (this.$refs.refundFileInput.getRef().files.length) {
                formFileData.append(
                    "refund_doc_file",
                    this.$refs.refundFileInput.getRef().files[0]
                );
                await moneyRefundAPI
                    .uploadDocFile(this.id, formFileData)
                    .then((response) => {
                        if (response && response.data) {
                            this.initMoneyRefundData();
                            this.showToast(
                                "OK",
                                "Файл успешно загружен!",
                                "success"
                            );
                        }
                    });
            }
        },
        downloadRefundFile() {
            this.showToast(
                "Загрузка файла",
                "Выполняется скачивание файла на компьютер...",
                "info"
            );
            // ...
        },
        onExit() {
            this.$router.push("/money-refunds");
        },
        addIncome() {
            this.newIncomes.push({});
        },
        removeIncome(key) {
            if (key < this.moneyRefund.incomes.length) {
                this.deletedIncomes.push(this.moneyRefund.incomes[key].id);
                this.moneyRefund.incomes.splice(key, 1);
            } else {
                this.newIncomes.splice(
                    key - this.moneyRefund.incomes.length,
                    1
                );
            }
        },
        showConfirmModal() {
            this.confirmModal = true;
        },
        hideConfirmModal() {
            this.confirmModal = false;
        },
        async convertToExpense() {
            try {
                this.isCover = true;
                this.formUpdateRequest();
                const response = await moneyRefundAPI.convertToExpense(this.id);
                if (response.data.success) {
                    this.showToast("Успешно", "Возврат ДС конвертирован в хозяйственный расход", "success");
                    this.$router.push(`/expenses/${response.data.expenseId}/edit`);
                } else {
                    this.showToast("Ошибка", "Не удалось конвертировать возврат ДС", "error");
                }
            } catch (error) {
                console.error("Ошибка при конвертации:", error);
                this.showToast("Ошибка", "Произошла ошибка при конвертации", "danger");
            } finally {
                this.isCover = false;
            }
        },
        goToExpensePage() {
            // Перенаправление на страницу созданного расхода
            if (this.isRefundConvertFromExpense) {
                this.$router.push(`/expenses/${this.moneyRefund.refundable.id}/edit`);
            } else {
                this.$router.push(`/expenses/${this.moneyRefund.converted_to_expense_id}/edit`);
            }
        }
    },
    computed: {
        incomes() {
            return this.moneyRefund.incomes.concat(this.newIncomes);
        },
        canUserUpdateMoneyRefund() {
            // Проверка, может ли пользователь редактировать возврат ДС
            if (this.moneyRefund.approved && !this.checkPermission("money_refund_post_update")) {
                return false;
            }
            if (this.isAlreadyConverted) {
                return false;
            }
            return this.checkPermission("money_refund_update");
        },
        canUserApproveMoneyRefund() {
            return this.checkPermission("money_refund_approve");
        },
        refundDateEnabled() {
            if (!this.moneyRefund.status) {
                return false;
            }
            return this.canUserUpdateMoneyRefund;
        },
        isEditing() {
            return !!this.moneyRefundId;
        },
        moneyRefundId() {
            return this.$route.params.money_refund_id;
        },
        isManual() {
            return (
                this.moneyRefund.refundable_type ==
                "App\\Models\\V1\\MoneyRefundable"
            );
        },
        // Сумма всех incomes (включая newIncomes) на странице
        sumIncomes() {
            return this.incomes.reduce((sum, income) => sum + parseFloat(income.sum) || 0, 0);
        },
        totalRefund() {
            // Преобразуем sumIncomes в число
            const sumIncomes = parseFloat(this.sumIncomes) || 0;

            // Преобразуем refund_sum_products в число (с учётом возможности строки "0,00")
            const refundSumProducts = parseFloat(this.moneyRefund.refund_sum_products.replace(',', '.')) || 0;

            // Преобразуем debt_sum в число (если нужно)
            const debtSum = parseFloat(this.moneyRefund.debt_sum) || 0;

            const result = debtSum - (sumIncomes + refundSumProducts);
    
            return result.toFixed(2);
        },
        isAlreadyConverted() {
            // Проверка, был ли возврат ДС уже конвертирован
            return !!this.moneyRefund.converted_to_expense_at;
        },
        isRefundConvertFromExpense() {
            return this.moneyRefund.refundable_type == "App\\Models\\V1\\Expenses\\Expense";
        },

        contractorContragentSwitch() {
            return this.moneyRefund.contractor_id ? this.moneyRefund.contractor_name : this.moneyRefund.contragent_name;
        },
        isConvertButtonDisabled() {
            // Логируем начальные значения и их типы
            console.log("debt_sum:", this.moneyRefund.debt_sum, typeof this.moneyRefund.debt_sum);
            console.log("refund_sum_money:", this.moneyRefund.refund_sum_money, typeof this.moneyRefund.refund_sum_money);
            console.log("refund_sum_products:", this.moneyRefund.refund_sum_products, typeof this.moneyRefund.refund_sum_products);
            console.log("isAlreadyConverted:", this.isAlreadyConverted);
            console.log("legal_entity_id:", this.moneyRefund.legal_entity_id);
            console.log("payment_method_id:", this.moneyRefund.payment_method_id);

            // Преобразуем значения в числа, если они не являются числами
            const debtSum = parseFloat(this.moneyRefund.debt_sum);
            const refundSumMoney = parseFloat(this.moneyRefund.refund_sum_money);
            const refundSumProducts = parseFloat(this.moneyRefund.refund_sum_products);

            // Проверяем, если фактическая сумма больше или равна сумме долга
            const isDebtSumExceeded = debtSum <= (refundSumMoney + refundSumProducts);
            console.log("isDebtSumExceeded:", isDebtSumExceeded);

            // Проверяем, если возврат ДС уже конвертирован
            const isAlreadyConverted = this.isAlreadyConverted;
            console.log("isAlreadyConverted:", isAlreadyConverted);

            // Проверяем, если отсутствуют legal_entity_id или payment_method_id
            const isLegalEntityIdMissing = !this.moneyRefund.legal_entity_id;
            console.log("isLegalEntityIdMissing:", isLegalEntityIdMissing);

            const isPaymentMethodIdMissing = !this.moneyRefund.payment_method_id;
            console.log("isPaymentMethodIdMissing:", isPaymentMethodIdMissing);

            // Возвращаем результат проверки
            return isDebtSumExceeded || isAlreadyConverted || isLegalEntityIdMissing || isPaymentMethodIdMissing;
        },
    },
};
</script>
