<template>
    <div>
        <LoadingCover :loadingCover="loadingCover" />
        <Table>
            <template v-slot:filters>
                <FilterInput v-model="params.reason_filter" label="Причина" placeholder="Номер счёта, заказа и т.д." />
                <FilterInput v-model="params.id_filter" type="number" label="Номер" placeholder="Номер Возврата ДС" />
                <FilterMultipleSelect v-model="params.contractors_filter" label="Поставщик"
                    placeholder="Выбрать поставщика" :options="contractors" />
                <FilterSelect v-model="params.contractors_type_filter" label="Тип поставщика"
                    :options="contractorTypes" />
                <FilterMultipleSelect v-model="params.contragents_filter" label="Контрагент"
                    placeholder="Выбрать контрагента" :options="contragents" />
                <FilterInputBetween v-model="params.sum_filter" label="Сумма" type="number" step="0.01" />
                <FilterInputBetween v-model="params.refund_sum_filter" label="Фактический возврат" type="number"
                    step="0.01" />
                <FilterSelect v-model="params.legal_entity_filter" label="Юр. лицо" :options="legalEntityOptions" />
                <FilterSelect v-model="params.payment_method_filter" label="Способ оплаты"
                    :options="paymentMethodOptions" />
                <FilterSelect v-model="params.status_filter" label="Статус" :options="statusOptions" />
                <FilterSelect v-model="params.approved_filter" label="Подтвержден" :options="approvedOptions" />
                <FilterSelect v-model="params.type_filter" :options="typeOptions" label="Тип возврата" />
                <FilterInputBetween v-model="params.created_at_filter" label="Дата создания" type="date" />
                <FilterInputBetween v-model="params.completed_at_filter" label="Дата возврата" type="date" />
            </template>

            <template v-slot:thead>
                <TH field="money_refundables.id" width="20px"> № </TH>
                <TH field="" width="150px"> Причина </TH>
                <TH field="contractor_id" width="150px"> Поставщик </TH>
                <TH field="contragent_id" width="150px"> Контрагент </TH>
                <TH field="legal_entity_id" width="150px"> Юр.лицо </TH>
                <TH field="payment_method_id" width="150px"> Способ оплаты </TH>
                <TH field="status" width="150px"> Статус </TH>
                <TH field="comment" width="135px"> Комментарий </TH>
                <TH align="center" field="debt_sum" width="120px"> Сумма </TH>
                <TH align="center" field="refund_sum_money" width="120px"> По факту </TH>
                <TH align="center" field="" width="120px"> Задолженность для вычета</TH>
                <TH field="created_at" width="80px"> Создан </TH>
                <TH field="converted_to_expense_at" width="40px"> Конвертирован </TH>
                <TH field="completed_at" width="40px"> Завершён </TH>
                <TH></TH>
            </template>

            <template v-slot:tbody>
                <TR v-for="refund in moneyRefunds" :key="refund">
                    <template v-slot:default>
                        <TD class="fw-bold"> {{ refund.id }} </TD>
                        <TD>
                            <a v-if="refund.refundable" @click.stop :href="reasonURL(refund)" class="link-primary fw-bold">
                                {{ reason(refund) }}
                            </a>
                            <span v-else> {{ reason(refund) }} </span>
                        </TD>
                        <TD> {{ refund.contractor_name ?? '—' }} </TD>
                        <TD> {{ refund.contragent_name ?? '—' }} </TD>
                        <TD> {{ refund.legal_entity_name }} </TD>
                        <TD> {{ refund.payment_method_name }} </TD>
                        <TD> {{ refund.status ? 'Сделан' : 'Не сделан' }} </TD>
                        <TD> {{ refund.comment || '—' }} </TD>
                        <TD align="center"> {{ parseFloat(refund.debt_sum).priceFormat(true) }} </TD>
                        <TD align="center" class="bg-light border-start border-end border-1">
                            {{ (parseFloat(refund.refund_sum_money) +
            parseFloat(refund.refund_sum_products)).priceFormat(true) }}</TD>
                        <TD class="fw-bold" align="center" :style="{ color: deductionTextColor(refund) }">
                            {{ deduction(refund).priceFormat(true) }}
                        </TD>
                        <TD> {{ formatDate(refund.created_at) }} </TD>
                        <TD> {{ formatDate(refund.converted_to_expense_at) }} </TD>
                        <TD> {{ formatDate(refund.completed_at) }} </TD>
                        <TD>
                            <button @click.stop="showRefund(refund.id)" type="button"
                                class="btn btn-outline-primary border-0">
                                <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                            </button>
                        </TD>
                    </template>
                </TR>
            </template>
            <template v-slot:tfoot>
                <div class="d-inline">
                    <span v-if="moneyRefundsSummary.total_refund_sum" class="pe-3">
                        Сумма по задолженности для вычета: {{ parseFloat(moneyRefundsSummary.total_debt_sum -
            moneyRefundsSummary.total_refund_sum).priceFormat(true) }}
                    </span>
                    <span v-if="moneyRefundsSummary.total_refund_sum" class="pe-3">
                        Сумма по факт. возвратам: {{ parseFloat(moneyRefundsSummary.total_refund_sum).priceFormat(true)
                        }}
                    </span>
                    <span v-if="moneyRefundsSummary.total_debt_sum" class="pe-3">
                        Общая сумма по возвратам: {{ parseFloat(moneyRefundsSummary.total_debt_sum).priceFormat(true) }}
                    </span>
                </div>
            </template>
        </Table>

        <DefaultModal v-if="isShowExportModal" width="400px" title="Экспорт"
            @close_modal="this.isShowExportModal = false">
            <div class="p-3">
                <p>
                    Выбирите тип выгрузки
                </p>
                <button type="button" class="btn btn-primary m-1" @click="moneyRefundsExport()">
                    Возвраты ДС
                </button>
                <button type="button" class="btn btn-primary m-1" @click="incomesExport()">
                    Поступления
                </button>
            </div>
        </DefaultModal>

        <DefaultModal v-if="isShowCreateModal" width="600px" title="Создание возврата ДС"
            @close_modal="isShowCreateModal = false">
            <form @submit.prevent="createRefund()">
                <div class="d-flex flex-column p-3">
                    <div class="w-100 pb-3">
                        <label for="reason">Причина возврата</label>
                        <select v-model="newRefund.reason" id="reason" class="form-select" type="text" required>
                            <option value="Депозит">Депозит</option>
                            <option value="Переплата">Переплата</option>
                            <option value="Акт сверки">Акт сверки</option>
                            <option value="Маркетинговый бонус">Маркетинговый бонус</option>
                        </select>
                    </div>
                    <div class="w-100 pb-3">
                        <label for="contractor">Поставщик</label>
                        <select v-model="newRefund.contractor_id" id="contractor" class="form-control" type="text"
                            required>
                            <option v-for="contractor in contractors" :value="contractor.id">{{ contractor.name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-100 pb-3">
                        <label for="contractor">Юр. лицо</label>
                        <select v-model="newRefund.legal_entity_id" id="contractor" class="form-control" type="text"
                            required>
                            <option v-for="legalEntity in legalEntities" :value="legalEntity.id">{{ legalEntity.name }}
                            </option>
                        </select>
                    </div>
                    <div class="w-100 pb-3">
                        <label for="contractor">Способ оплаты</label>
                        <select v-model="newRefund.payment_method_id" id="contractor" class="form-control" type="text"
                            required>
                            <option v-for="paymentMethod in currentPaymentMethods" :value="paymentMethod.id">{{
            paymentMethod.name }}</option>
                        </select>
                    </div>
                    <div class="w-100 pb-3">
                        <label for="sum">Сумма возврата</label>
                        <input v-model="newRefund.debt_sum" id="sum" class="form-control" type="number"
                            placeholder="Укажите сумму возврата" step="0.01" min="1" required>
                    </div>
                    <div class="w-100 pb-3">
                        <label for="date">Дата возврата</label>
                        <input v-model="newRefund.created_at" id="date" class="form-control" type="date" required>
                    </div>
                </div>
                <div class="d-flex justify-content-start p-2 mb-0 bg-light border-top rounded-bottom">
                    <button type="submit" class="btn btn-primary m-1" @click="">
                        Сохранить
                    </button>
                    <button type="button" class="btn bg-gradient btn-light border m-1 " style="height: 38px;"
                        @click="isShowCreateModal = false">Отмена</button>
                </div>
            </form>
        </DefaultModal>
    </div>
</template>

<script>
import { moneyRefundAPI } from '../api/money_refund_api';
import { paymentMethodAPI } from '../api/payment_method_api';
import { expenseContragentAPI } from '../api/expense_contragent_api';
import { mapGetters } from 'vuex';

import indexTableMixin from '../utils/indexTableMixin';
import FilterInputBetween from '../ui/inputs/FilterInputBetween.vue'
import FilterInput from '../ui/inputs/FilterInput.vue'
import DefaultModal from '../components/modals/default_modal.vue';
import TableCheckbox from '../components/inputs/table_checkbox.vue';
import TextAreaPopup from '../components/popups/table_textarea_popup.vue';
import LoadingCover from '../components/Layout/LoadingCover.vue';
import FormInput from '../ui/inputs/FormInput.vue';


export default {
    mixins: [indexTableMixin],
    components: { TableCheckbox, DefaultModal, TextAreaPopup, LoadingCover, FilterInputBetween, FormInput, FilterInput },
    data() {
        return {
            moneyRefunds: [],
            moneyRefundsSummary: {},
            newRefund: {},
            statusOptions: [
                { id: 1, name: 'Сделан' },
                { id: 0, name: 'Не сделан' }
            ],
            approvedOptions: [
                { id: 1, name: 'Подтвержден' },
                { id: 0, name: 'Не подтвержден' }
            ],
            legalEntityOptions: [
                { id: 1, name: 'ИП Грибанов' },
                { id: 2, name: 'ИП Шиконин' }
            ],
            contractorTypes: [
                { id: 1, name: 'Только основные' },
                { id: 0, name: 'Только дополнительные' }
            ],
            typeOptions: [
                { id: 'App\\Models\\V1\\Invoice', name: 'Счёт' },
                { id: 'App\\Models\\V1\\ContractorRefund', name: 'Возврат поставщику' },
                { id: 'App\\Models\\V1\\ProductRefund', name: 'Возврат на склад' },
                { id: 'App\\Models\\V1\\Defect', name: 'Брак' },
                { id: 'Депозит', name: 'Депозит' },
                { id: 'Переплата', name: 'Переплата' },
                { id: 'Акт сверки', name: 'Акт сверки' },
                { id: 'Маркетинговый бонус', name: 'Маркетинговый бонус' },
            ],
            paymentMethodOptions: [],
            contragents: [],
            loadingCover: false,
            isShowCreateModal: false,
            isShowExportModal: false,
        }
    },
    mounted() {
        this.initNewRefund();
        this.$store.dispatch('loadContractorsData');
        this.$store.dispatch('loadLegalEntities');
        this.$store.dispatch('loadPaymentMethods');
    },
    methods: {
        async initSettings() {
            this.settings.localStorageKey = 'money_refundables_params'
            this.settings.tableTitle = 'Возвраты ДС';
            this.settings.createButtonText = 'Создать Возврат ДС';
            this.settings.withCreateButton = this.checkPermission('money_refund_create');
            this.settings.withHeader = false;
            this.settings.withInfo = false;
            this.settings.withFooter = true;
            this.settings.isLoading = true;
            this.settings.saveParams = true;
            this.settings.withExport = true;
            this.settings.indexAPI = async params => await moneyRefundAPI.index(params);
            this.onInitData = async res => {
                this.moneyRefunds = res.data.data;
                this.moneyRefundsSummary = res.data.summary;
                await paymentMethodAPI.index().then((res) => {
                    this.paymentMethodOptions = res.data.data;
                    this.paymentMethodOptions.forEach(method => {
                        method.name = method.legal_entity_name + ' ' + method.name;
                    })
                })
                await expenseContragentAPI.index().then((res) => {
                    this.contragents = res.data;
                })
                this.settings.isLoading = false;
            }
            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'id';
                defaultParams.sort_type = this.params.sort_type || 'desc';
            }
            this.onClickCreateButton = () => this.isShowCreateModal = true;
            this.onExport = () => this.isShowExportModal = true;
        },

        initNewRefund() {
            this.newRefund = {
                reason: null,
                contractor_id: null,
                debt_sum: null,
                created_at: null,
                legal_entity_id: null,
                payment_method_id: null,
            }
        },

        async createRefund() {
            this.isShowCreateModal = false;
            this.isCover = true;
            await moneyRefundAPI.create(this.newRefund);
            await this.index()
            this.isCover = false;

        },

        async moneyRefundsExport() {
            const res = await moneyRefundAPI.export(this.params);
            this.downloadFile(res, 'Выгрузка возвратов ДС')
            this.toggleExportModal();
        },

        async incomesExport() {
            const res = await moneyRefundAPI.exportIncomes(this.params);
            this.downloadFile(res, 'Выгрузка возвратов ДС');
        },

        /**
         * Скачивает файл полученный из запроса.
         *
         * @param {AxiosResponse} response
         * @param {string} fileName
         */
        downloadFile(response, fileName) {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName + '.xlsx');
            document.body.appendChild(link);
            link.click();
        },
        deductionTextColor(refund) {
            let deduction = this.deduction(refund);

            if (!deduction)
                return 'green';
            else if (deduction == refund.debt_sum)
                return '#d79500';
            else
                return 'red';
        },
        deduction(refund) {
            return parseFloat(refund.debt_sum) - (parseFloat(refund.refund_sum_money) + parseFloat(refund.refund_sum_products));
        },
        showRefund(id) {
            this.$router.push('/money-refunds/' + id + '/edit');
        },
        reason(refund) {
            switch (refund.refundable_type) {
                case 'App\\Models\\V1\\Invoice': return "Счёт " + refund.refundable.number;
                case 'App\\Models\\V1\\MoneyRefundable': return refund.reason;
                case 'App\\Models\\V1\\ContractorRefund': return "Возврат поставщику №" + refund.refundable.id;
                case 'App\\Models\\V1\\ProductRefund': return "Возврат товара " + refund.refundable.order.number;
                case 'App\\Models\\V1\\Defect': return "Брак " + refund.refundable.order.number;
                case 'App\\Models\\V1\\Expenses\\Expense': return "Хоз.расход №" + refund.refundable.id;
            }
        },
        reasonURL(refund) {
            switch (refund.refundable_type) {
                case 'App\\Models\\V1\\Invoice': return location.protocol + '//' + location.host + '/#/invoices/' + refund.refundable.id + '/edit';
                case 'App\\Models\\V1\\MoneyRefundable': return location.protocol + '//' + location.host + '/#/money_refunds/' + refund.id + '/edit';
                case 'App\\Models\\V1\\ContractorRefund': return location.protocol + '//' + location.host + '/#/contractor_refunds/' + refund.refundable.id + '/edit';
                case 'App\\Models\\V1\\ProductRefund': return location.protocol + '//' + location.host + '/#/product_refunds/' + refund.refundable.id ?? null + '/edit';
                case 'App\\Models\\V1\\Defect': return location.protocol + '//' + location.host + '/#/defects/' + refund.refundable.id + '/edit';
                case 'App\\Models\\V1\\Expenses\\Expense': return location.protocol + '//' + location.host + '/#/expenses/' + refund.refundable.id + '/edit';
            }
        }
    },
    computed: {
        ...mapGetters({ contractors: 'getContractors', paymentMethods: 'getPaymentMethods', legalEntities: 'getLegalEntities' }),
        currentPaymentMethods() {
            let legalEntityPaymentMethods = [];
            this.paymentMethods.forEach(method => {
                if (method.legal_entity_id == this.newRefund.legal_entity_id)
                    legalEntityPaymentMethods.push(method);
            });
            return legalEntityPaymentMethods;
        }
    }
}
</script>
