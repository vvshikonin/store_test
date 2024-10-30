<template>
    <Table>
        <template v-slot:filters>
            <div class="d-flex">
                <FilterMultipleSelect v-model="params.legal_entity_ids" label="Юр. лицо" placeholder="Выбрать юр.лицо"
                    :options="legalEntities"></FilterMultipleSelect>
                <FilterMultipleSelect v-model="params.payment_method_ids" label="Способ оплаты"
                    placeholder="Выбрать способ оплаты" :options="paymentMethodsWithLegalEntities">
                </FilterMultipleSelect>
                <!-- <FilterInput v-model="params.source_name" label="Источник" placeholder="Номер заказа/счета/и т.д."></FilterInput> -->
                <!-- <FilterSelect v-model="params.source_type" label="Тип источника" :options="sourceTypes"></FilterSelect> -->
                <FilterInputBetween v-model:start="params.payment_date_start" v-model:end="params.payment_date_end"
                    v-model:equal="params.payment_date_equal" v-model:notEqual="params.payment_date_not_equal"
                    label="Дата оплаты" type="date"></FilterInputBetween>
            </div>
            <div class="d-flex align-items-center justify-content-end ms-auto">
                <button v-if="canUserCreate" @click="openImportModal()"
                    class="btn btn-light border border-secondary bg-opacity ms-auto mt-1 p-3 d-flex align-items-center"
                    style="font-size: 14px; height: 20%;" type="button"> Импорт транзакций <font-awesome-icon
                        class="ms-1 text-success" icon="fa-solid fa-file-import" />
                </button>
                <button v-if="canUserCreate" @click="openResponsibleModal()"
                    class="btn btn-light border border-secondary bg-opacity ms-1 mt-1 p-3 d-flex align-items-center"
                    style="font-size: 14px; height: 20%;" type="button"> Ответственные <font-awesome-icon
                        class="ms-1 text-success" icon="fa-solid fa-gear" />
                </button>
            </div>
            <!-- <pre>{{ expenseContragents }}</pre> -->
        </template>
        <template v-slot:thead>
            <TH></TH>
            <TH field="payment_date">Дата оплаты</TH>
            <TH field="legal_entity_name">Юр. лицо</TH>
            <TH field="payment_method_name">Способ оплаты</TH>
            <TH field="manual_sum" title="Сумма по внесённым вручную транзакциям">Ручная сумма</TH>
            <TH field="auto_sum" title="Сумма по автоматически проведённым транзакциям">Авто сумма</TH>
            <TH field="difference" title="(Авто сумма - Ручная сумма)">Разница</TH>
        </template>
        <template v-slot:tbody>
            <!-- <TR v-for="financialControl in financialControls" :withInnerTable="true" :key="financialControl"
                @inner_expand="showFinance(financialControl)">
                <template v-slot:default>
                    <TD>{{ formatDate(financialControl.payment_date, "DD.MM.YYYY") }}</TD>
                    <TD>{{ financialControl.legal_entity_name }}</TD>
                    <TD>{{ financialControl.payment_method_name }}</TD>
                    <TD>{{ financialControl.manual_sum.priceFormat(true) }}</TD>
                    <TD>{{ financialControl.auto_sum.priceFormat(true) }}</TD>
                    <TD :style="{ color: difference(financialControl) !== 0 ? 'red' : '' }"> {{
                        difference(financialControl).priceFormat(true) }} </TD>
                </template>
                <template v-slot:sub-thead v-if="financialControl.finances.length !== 0">

                    <TH width="120px">Дата оплаты</TH>
                    <TH width="150px">Источник</TH>
                    <TH width="120px">Способ оплаты</TH>
                    <TH width="150px">Сумма</TH>
                    <TH width="150px">Тип транзакции</TH>
                    <TH width="180px">Поставщик/Контрагент</TH>
                    <TH width="150px">Ответственный</TH>
                    <TH width="250px">Основание</TH>
                    <TH width="120px">Тип записи</TH>
                    <TH width="120px"></TH>
                </template>
                <template v-if="!financialControl.finances || financialControl.finances.length === 0" v-slot:sub-tbody>
                    <Spinner class="h-50"></Spinner>
                </template>
                <template v-else v-slot:sub-tbody>
                    <TR v-if="financialControl.finances.financialControls.length !== 0">
                        <div class="fw-bolder">Ручные:</div>
                    </TR>
                    <TR v-for="financialControl in financialControl.finances.financialControls">
                        <TD>{{ formatDate(financialControl.payment_date, "DD.MM.YYYY") }}</TD>
                        <TD v-if="financialControl.user">{{ financialControl.user.name }}</TD>
                        <TD v-else>Не определён</TD>
                        <TD>{{ financialControl.payment_method.name }}</TD>
                        <TD v-if="financialControl.type=='In'">-{{ financialControl.sum.priceFormat(true) }}</TD>
                        <TD v-else>{{ financialControl.sum.priceFormat(true) }}</TD>
                        <TD>{{ financeTypes.find(type => type.id === financialControl.type).name }}</TD>
                        <TD>
                            <span v-if="financialControl.contractor && financialControl.contractor.name">
                                {{ financialControl.contractor.name }}
                            </span>
                            <span v-else>Без поставщика</span>
                        </TD>
                        <TD v-if="financialControl.employee">{{ financialControl.employee.name }}</TD>
                        <TD v-else>Без ответственного</TD>
                        <TD>{{ financialControl.reason ?? '—' }}</TD>
                        <TD>Ручной</TD>
                        <TD align="right">
                            <button v-if="canUserEdit" @click.stop="editFinance(financialControl)"
                                class="btn btn-outline-primary border-0 me-3">
                                <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                            </button>
                            <TrashButton v-if="canUserDelete" @click="onTrashClick(financialControl.id)"></TrashButton>
                        </TD>
                    </TR>
                    <TR v-if="financialControl.finances.transactions.length !== 0">
                        <div class="fw-bolder">Автоматические:</div>
                    </TR>
                    <TR v-for="transaction in financialControl.finances.transactions">
                        <TD>{{ formatDate(transaction.created_at, "DD.MM.YYYY HH:mm") }}</TD>
                        <TD>
                            <ProductTransactionLink :transaction="transaction" />
                        </TD>
                        <TD>{{ transaction.payment_method?.name || '—' }}</TD>
                        <TD v-if="transaction.type=='In'">-{{ transaction.sum.priceFormat(true) }}</TD>
                        <TD v-else>{{ transaction.sum.priceFormat(true) }}</TD>
                        <TD>{{ financeTypes.find(type => type.id === transaction.type).name }}</TD>
                        <TD v-if="transaction.contractor?.name">{{ transaction.contractor.name }}</TD>
                        <TD v-else-if="transaction.contragent?.name">{{ transaction.contragent.name }}</TD>
                        <TD v-else>—</TD>
                        <TD>—</TD>
                        <TD>—</TD>
                        <TD>Автоматический</TD>
                        <TD></TD>
                    </TR>
                </template>
            </TR> -->
            <TR v-for="financialControl in financialControls" :withInnerTable="true" :key="financialControl"
                @inner_expand="showFinance(financialControl)">
                <template v-slot:default>
                    <TD>{{ formatDate(financialControl.payment_date, "DD.MM.YYYY") }}</TD>
                    <TD>{{ financialControl.legal_entity_name }}</TD>
                    <TD>{{ financialControl.payment_method_name }}</TD>
                    <TD>{{ financialControl.manual_sum.priceFormat(true) }}</TD>
                    <TD>{{ financialControl.auto_sum.priceFormat(true) }}</TD>
                    <TD :style="{ color: difference(financialControl) !== 0 ? 'red' : '' }">
                        {{ difference(financialControl).priceFormat(true) }}
                    </TD>
                </template>

                <template v-slot:sub-thead v-if="financialControl.finances.length !== 0">
                    <TH>{{ tableViewMode == 'table' ? 'Дата оплаты' : '' }}</TH>
                    <TH>{{ tableViewMode == 'table' ? 'Источник' : '' }}</TH>
                    <TH>{{ tableViewMode == 'table' ? 'Способ оплаты' : '' }}</TH>
                    <TH>{{ tableViewMode == 'table' ? 'Сумма' : '' }}</TH>
                    <TH>{{ tableViewMode == 'table' ? 'Тип транзакции' : '' }}</TH>
                    <TH>{{ tableViewMode == 'table' ? 'Поставщик/Контрагент' : '' }}</TH>
                    <TH>{{ tableViewMode == 'table' ? 'Ответственный' : '' }}</TH>
                    <TH>{{ tableViewMode == 'table' ? 'Основание' : '' }}</TH>
                    <TH>{{ tableViewMode == 'table' ? 'Тип записи' : '' }}</TH>
                    <TH align='right'>
                        <button @click="toggleTableViewMode('table')" style="width: 32px; height: 32px; font-size: 14px; padding: 0"
                            class="btn me-1" :class="tableViewMode == 'table' ? 'btn-primary' : 'btn-outline-primary'">
                            <font-awesome-icon icon="fa-solid fa-table-list"/>
                        </button>
                        <button @click="toggleTableViewMode('side-by-side')" style="width: 32px; height: 32px; font-size: 14px; padding: 0"
                            class="btn" :class="tableViewMode == 'side-by-side' ? 'btn-primary' : 'btn-outline-primary'">
                            <font-awesome-icon icon="fa-solid fa-table-columns"/>
                        </button>
                    </TH>
                </template>

                <template v-if="financialControl.finances.length === 0" v-slot:sub-tbody>
                    <Spinner class="h-50"></Spinner>
                </template>

                <!-- Добавляем кнопку переключения режима -->
                <template v-slot:sub-tbody v-else>
                    <!-- Обычный режим отображения -->
                    <template v-if="tableViewMode === 'table'">
                        <!-- Проверка на наличие ручных транзакций -->
                        <template v-if="financialControl.finances.financialControls.length > 0 && financialControl.finances.financialControls.some(fc => fc !== null)">
                            <TD colspan="10" class="fw-bolder">Ручные:</TD>
                            <TR v-for="financialControl in filteredFinancialControls(financialControl.finances.financialControls)">
                                <TD>{{ formatDate(financialControl?.payment_date, "DD.MM.YYYY") }}</TD>
                                <TD v-if="financialControl?.user">{{ financialControl.user.name }}</TD>
                                <TD v-else-if="financialControl == null">—</TD>
                                <TD v-else>Не определён</TD>
                                <TD v-if="financialControl">{{ financialControl?.payment_method.name }}</TD>
                                <TD v-else>—</TD>
                                <TD v-if="financialControl?.type == 'In'">-{{ financialControl?.sum.priceFormat(true) }}</TD>
                                <TD v-else-if="financialControl">{{ financialControl?.sum.priceFormat(true) }}</TD>
                                <TD v-else>—</TD>
                                <TD>{{ financialControl ? financeTypes.find(type => type.id === financialControl.type).name : '—' }}</TD>
                                <TD v-if="financialControl?.contractor && financialControl?.contractor.name">{{ financialControl.contractor.name }}</TD>
                                <TD v-else>Без поставщика</TD>
                                <TD v-if="financialControl?.employee">{{ financialControl.employee.name }}</TD>
                                <TD v-else>Без ответственного</TD>
                                <TD>{{ financialControl?.reason ?? '—' }}</TD>
                                <TD>Ручной</TD>
                                <TD align="right">
                                    <button v-if="canUserEdit" @click.stop="editFinance(financialControl)" class="btn btn-outline-primary border-0 me-3">
                                        <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                                    </button>
                                    <TrashButton v-if="canUserDelete" @click="onTrashClick(financialControl.id)"></TrashButton>
                                </TD>
                            </TR>
                        </template>

                        <!-- Проверка на наличие автоматических транзакций -->
                        <template v-if="financialControl.finances.transactions.length > 0 && financialControl.finances.transactions.some(transaction => transaction !== null)">
                            <TD colspan="10" class="fw-bolder">Автоматические:</TD>
                            <TR v-for="transaction in filteredTransactions(financialControl.finances.transactions)">
                                <TD>{{ formatDate(transaction?.created_at, "DD.MM.YYYY HH:mm") }}</TD>
                                <TD v-if="transaction"><ProductTransactionLink :transaction="transaction" /></TD>
                                <TD v-else>—</TD>
                                <TD>{{ transaction?.payment_method?.name || '—' }}</TD>
                                <TD v-if="transaction?.type == 'In'">-{{ transaction?.sum.priceFormat(true) }}</TD>
                                <TD v-else>{{ transaction?.sum.priceFormat(true) }}</TD>
                                <TD>{{ transaction ? financeTypes.find(type => type.id === transaction?.type).name : '—' }}</TD>
                                <TD v-if="transaction?.contractor?.name">{{ transaction?.contractor.name }}</TD>
                                <TD v-else-if="transaction?.contragent?.name">{{ transaction?.contragent.name }}</TD>
                                <TD v-else>—</TD>
                                <TD>—</TD>
                                <TD>—</TD>
                                <TD>Автоматический</TD>
                                <TD></TD>
                            </TR>
                        </template>
                    </template>
                    <!-- Режим отображения "рядом" -->
                    <template v-else-if="tableViewMode === 'side-by-side'">
                        <TD colspan="10">
                            <div class="d-flex">
                                <div style="width: 50%">
                                    <h5>Автоматические:</h5>
                                    <table class="table table-hover">
                                        <thead>
                                            <TR>
                                                <TH>Дата</TH>
                                                <TH>Источник</TH>
                                                <TH>Способ оплаты</TH>
                                                <TH>Сумма</TH>
                                                <TH>Тип транзакции</TH>
                                                <TH>Поставщик/Контрагент</TH>
                                            </TR>
                                        </thead>
                                        <tbody>
                                                <TR v-for="transaction in financialControl.finances.transactions">
                                                    <TD>{{ formatDate(transaction?.created_at, "DD.MM.YYYY HH:mm") }}</TD>
                                                    <TD v-if="transaction"><ProductTransactionLink :transaction="transaction" /></TD>
                                                    <TD v-else>—</TD>
                                                    <TD>{{ transaction?.payment_method?.name || '—' }}</TD>
                                                    <TD v-if="transaction?.type == 'In'">-{{ transaction?.sum?.priceFormat(true) }}</TD>
                                                    <TD v-else>{{ transaction?.sum?.priceFormat(true) || '—' }}</TD>
                                                    <TD>{{ transaction ? financeTypes.find(type => type.id === transaction?.type).name : '—' }}</TD>
                                                    <TD v-if="transaction?.contractor?.name">{{ transaction?.contractor.name }}</TD>
                                                    <TD v-else-if="transaction?.contragent?.name">{{ transaction?.contragent.name }}</TD>
                                                    <TD v-else>—</TD>
                                                </TR>
                                        </tbody>
                                    </table>
                                </div>
                                <div style="width: 50%">
                                    <h5>Ручные:</h5>
                                    <table class="table table-hover">
                                        <thead>
                                            <TR>
                                                <TH>Дата</TH>
                                                <TH>Источник</TH>
                                                <TH>Способ оплаты</TH>
                                                <TH>Сумма</TH>
                                                <TH>Тип транзакции</TH>
                                                <TH>Поставщик/Контрагент</TH>
                                                <TH>Ответственный</TH>
                                                <TH>Основание</TH>
                                            </TR>
                                        </thead>
                                        <tbody>
                                            <TR v-for="financialControl in financialControl.finances.financialControls">
                                                <TD>{{ formatDate(financialControl?.payment_date, "DD.MM.YYYY") }}</TD>
                                                <TD v-if="financialControl?.user">{{ financialControl.user.name }}</TD>
                                                <TD v-else-if="financialControl">Не определён</TD>
                                                <TD v-else>—</TD>
                                                <TD>{{ financialControl?.payment_method.name || '—' }}</TD>
                                                <TD v-if="financialControl?.type == 'In'">-{{ financialControl?.sum.priceFormat(true) }}</TD>
                                                <TD v-else>{{ financialControl?.sum.priceFormat(true) || '—' }}</TD>
                                                <TD>{{ financialControl ? financeTypes.find(type => type.id === financialControl.type).name : '—' }}</TD>
                                                <TD v-if="financialControl?.contractor && financialControl?.contractor.name">
                                                    {{ financialControl.contractor.name }}
                                                </TD>
                                                <TD v-else-if="financialControl">Без поставщика</TD>
                                                <TD v-else>—</TD>
                                                <TD v-if="financialControl?.employee">{{ financialControl.employee.name }}</TD>
                                                <TD v-else-if="financialControl">Без ответственного</TD>
                                                <TD v-else>—</TD>
                                                <TD v-if="financialControl?.reason">{{ financialControl.reason }}</TD>
                                                <TD v-else>—</TD>
                                            </TR>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </TD>
                    </template>
                </template>
            </TR>

            <!-- конец двух режимов таблицы -->
        </template>
        <template v-slot:tfoot>
            <span v-if="totalManualSum != null" class="pe-3">Сумма по ручным: {{ totalManualSum.priceFormat(true) }}</span>
            <span v-if="totalAutoSum != null" class="pe-3">Сумма по автоматическим: {{ totalAutoSum.priceFormat(true) }}</span>
            <span v-if="totalDifference != null" class="pe-3">Сумма по разнице: {{ totalDifference.priceFormat(true) }}</span>
        </template>
        <template v-slot:info>
            <ModalWindow v-if="isEditingResponsible" @close_modal="closeResponsibleModal()" width="700px"
                title="Ответственные за проведение транзакций">
                <template v-slot>
                    <div class="d-flex p-3 flex-column justify-content-between">
                        <div style="max-height: 450px; overflow-y: auto;">
                            <div v-for="employee in employees"
                                class="d-flex bg-white mt-2 mb-2 p-2 border border-light rounded">
                                <TableEditInput style="height: 35px;" v-model:content="employee.name"
                                    @update:content="updateEmployee(employee)"></TableEditInput>
                                <TrashButton class="ms-auto" @click="deleteEmployee(employee)"></TrashButton>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button @click="addEmployee()" type="button" class="btn btn-primary mt-2"> Добавить ответственного
                            </button>
                        </div>
                    </div>
                </template>
            </ModalWindow>
            <ModalWindow v-if="isShowFincanceModal" @close_modal="closeAddFinanceModal()" width="600px"
                :title="modalWindowTitle">
                <template v-slot>
                    <form @submit.prevent="saveFinance()">
                        <div class="d-flex p-3 flex-column justify-content-between">
                            <label for="legal-entity">Юридическое лицо</label>
                            <select id="legal-entity" required v-model="editingFinance.payment_method.legal_entity_id"
                                class="form-select">
                                <option :value="legalEntity.id" v-for="legalEntity in legalEntities">{{ legalEntity.name }}
                                </option>
                            </select>
                            <label for="payment-method">Способ оплаты</label>
                            <select id="payment-method" required :disabled="!editingFinance.payment_method.legal_entity_id"
                                class="form-select" v-model="editingFinance.payment_method_id">
                                <option :value="paymentMethod.id" v-for="paymentMethod in payment_methods_options"> {{
                                    paymentMethod.name }} </option>
                            </select>
                            <label for="employee">Ответственный</label>
                            <select id="employee" class="form-select" :disabled="!employees"
                                v-model="editingFinance.employee_id">
                                <option :value="employee.id" v-for="employee in employees"> {{ employee.name }} </option>
                            </select>
                            <label for="payment-date">Дата платежа</label>
                            <input id="payment-date" type="date" class="form-control" required
                                v-model="editingFinance.payment_date" />
                            <label for="payment-type">Тип транзакции</label>
                            <select id="payment-type" required class="form-select" v-model="editingFinance.type">
                                <option :value="financeType.id" v-for="financeType in financeTypes"> {{ financeType.name }}
                                </option>
                            </select>
                            <label for="sum">Сумма платежа</label>
                            <input id="sum" type="number" min="0" step="0.01" class="form-control" required
                                v-model="editingFinance.sum" />
                            <label for="contractor">Поставщик</label>
                            <select id="contractor" class="form-select" v-model="editingFinance.contractor_id">
                                <option :value="contractor.id" v-for="contractor in contractors"> {{ contractor.name }}
                                </option>
                            </select>
                            <label for="reason">Основание</label>
                            <textarea id="reason" class="form-control" v-model="editingFinance.reason"></textarea>
                            <div class="pt-2">
                                <button type="submit" class="btn btn-primary mt-2"> {{ modalWindowSubmitButtonText }} </button>
                                <button type="button" class="btn-light btn border bg-gradient border-1 ms-2 mt-2"
                                    @click="closeAddFinanceModal()"> Отмена </button>
                            </div>
                        </div>
                    </form>
                </template>
            </ModalWindow>
            <ConfirmModal v-if="isConfirmModal" @cancel="isConfirmModal = false" @confirm="onDeleteFinance()">
            </ConfirmModal>
            <ModalWindow :width="'700px'" v-if="isShowExportModal" @close_modal="toggleExportModal()"
                title="Выгрузка возвратов ДС">
                <div class="d-flex p-3">
                    <span>Что вы хотите выгрузить?</span>
                </div>
                <div class="d-flex justify-content-start p-2 mb-0 bg-light border-top">
                    <button class="btn btn-primary m-1" @click="aggregatedExport()">
                        <!-- <font-awesome-icon icon="fa-regular fa-credit-card" class="pe-2" /> --> Саггрегированные транзакции
                    </button>
                    <button class="btn btn-primary m-1" @click="allTransactionsExport()">
                        <!-- <font-awesome-icon icon="fa-solid fa-boxes-stacked" class="pe-2" /> --> Все транзакции </button>
                    <button class="btn bg-gradient btn-light border m-1 ms-auto" style="height: 38px;"
                        @click="toggleExportModal()">Отмена</button>
                </div>
            </ModalWindow>
            <ModalWindow v-if="isShowImportModal" @close_modal="closeImportModal" width="800px" title="Импорт транзакций">
                <template v-slot>
                    <form @submit.prevent="importFinancialControls" method="post">
                        <div class="d-flex p-3 flex-column justify-content-between">
                            <!-- Ссылка на скачивание шаблона -->
                            <a href="/files/Шаблон_Транзакций.xlsx" download="Шаблон_Транзакций.xlsx"
                                class="btn btn-secondary mb-2"> Скачать Шаблон </a>
                            <input name="file" type="file" accept=".xlsx" @change="handleFileChange" />
                            <div class="d-flex flex-column mt-3 text-danger" style="max-height: 40dvh; overflow-y: auto">
                                <small>{{ importErrors.message }}</small>
                                <small v-for="error in importErrors.errors"> {{ error }} </small>
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="btn btn-primary mt-2"> Загрузить </button>
                                <button type="button" class="btn-light btn border bg-gradient border-1 ms-2 mt-2"
                                    @click="closeImportModal"> Отмена </button>
                            </div>
                        </div>
                    </form>
                </template>
            </ModalWindow>
        </template>
    </Table>
</template>
<script>
import { financialControlAPI } from '../api/financial_control_api.js';
import { employeeAPI } from '../api/employee_api.js';
import { expenseContragentAPI } from '../api/expense_contragent_api.js';
import { mapGetters } from 'vuex';

import IndexTableMixin from '../utils/indexTableMixin.js';
import ModalWindow from '../components/modals/default_modal.vue';
import SelectInput from '../components/inputs/select_input.vue';
import TrashButton from '../components/inputs/trash_button.vue'
import ConfirmModal from '../components/modals/DestroyConfirmModal.vue';
import Spinner from '../components/Tables/is_loading.vue';
import TableEditInput from '../components/inputs/table_edit_input.vue';
import ProductTransactionLink from "../components/Other/ProductTransactionLink.vue";
import FilterInput from '../ui/inputs/FilterInput.vue';
import FilterSelect from '../components/inputs/FilterSelect.vue';

export default {
    components: {
        IndexTableMixin, ModalWindow, SelectInput,
        TrashButton, ConfirmModal, Spinner, TableEditInput,
        ProductTransactionLink, FilterInput, FilterSelect },
    mixins: [IndexTableMixin],

    data() {
        return {
            isAddingFinance: false,
            isEditingFinance: false,
            isEditingResponsible: false,
            editingFinance: {},
            selectedLegalEntity: null,
            selectedPaymentMethodId: null,
            isConfirmModal: false,
            financialControls: [],
            financeTypes: [
                { id: "In", name: "Расход" },
                { id: "Out", name: "Поступление" },
            ],
            expenseContragents: [],
            totalManualSum: null,
            totalAutoSum: null,
            totalDifference: null,
            employees: null,
            isShowExportModal: false,
            isShowImportModal: false,
            selectedFile: null,
            importErrors: [],
            tableViewMode: 'table'
        }
    },
    methods: {
        initSettings() {
            this.settings.tableTitle = 'Контроль расходов';
            this.settings.createButtonText = 'Внести транзакцию';
            this.settings.localStorageKey = 'financial_controls_params';

            this.settings.withCreateButton = this.canUserCreate;
            this.settings.withHeader = false;
            this.settings.withExport = true;
            this.settings.isLoading = true;
            this.settings.saveParams = true;
            this.settings.withBottomBox = false;
            this.settings.withFilterTemplates = true;

            this.settings.indexAPI = params => financialControlAPI.index(params);

            this.onInitData = res => {
                this.financialControls = res.data.data;

                this.totalManualSum = res.data.totalManualSum;
                this.totalAutoSum = res.data.totalAutoSum;
                this.totalDifference = res.data.totalDifference;

                this.financialControls.forEach((financialControl) => {
                    financialControl.finances = []
                });
            }

            this.onExport = () => this.toggleExportModal();

            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'payment_date';
                defaultParams.sort_type = this.params.sort_type || 'desc';
            }

            this.onClickCreateButton = function () {
                this.editingFinance.payment_method = {
                    legal_entity_id: null
                };

                this.modalWindowTitle = "Внести транзакцию";
                this.modalWindowSubmitButtonText = "Внести";
                this.isAddingFinance = true;

                this.saveFinance = async () => {
                    this.isAddingFinance = false;
                    await financialControlAPI.store(this.editingFinance);
                    this.editingFinance = {};
                    this.index();
                }
            };

            // this.onExport = () => this.selectExportType = true;
        },
        async loadExpenseContragents() {
            try {
                const res = await expenseContragentAPI.index();
                this.expenseContragents = res.data;
            } catch (error) {
                console.error("Ошибка загрузки контрагентов:", error);
            }
        },
        async initEmployees() {
            const res = await employeeAPI.index();
            this.employees = res.data;
        },
        async addEmployee() {
            const employee = {
                name: "Новый сотрудник",
                is_payment_responsible: 1
            }
            await employeeAPI.store(employee);
            this.initEmployees();
        },
        async updateEmployee(employee) {
            await employeeAPI.update(employee);
            this.initEmployees();
        },
        async deleteEmployee(employee) {
            const response = await employeeAPI.destroy(employee);
            if (response.data.message) {
                this.showToast('Ошибка', response.data.message, 'warning');
            } else {
                this.initEmployees();
            }
        },
        closeAddFinanceModal() {
            this.isAddingFinance = false;
            this.isEditingFinance = false;
            this.editingFinance = {};
        },
        onTrashClick(finance_id) {
            this.isConfirmModal = true;
            this.onDeleteFinance = () => {
                financialControlAPI.destroy(finance_id);
                this.isConfirmModal = false;
                this.index();
            };
            this.deletableId = finance_id;
        },
        async showFinance(financialControl) {
            if (!financialControl.finances.length) {
                const res = await financialControlAPI.show(financialControl.payment_method_id, financialControl.payment_date);
                financialControl.finances = res.data;
                console.log(res);

                let finances = this.synchronizeArrays(financialControl.finances.financialControls, financialControl.finances.transactions, 'sum');
                console.log(finances);
                financialControl.finances.financialControls = finances.primary;
                financialControl.finances.transactions = finances.secondary;
            }
        },
        synchronizeArrays(primaryArray, secondaryArray, key) {
            const resultPrimary = [];
            const resultSecondary = [];

            // Создаем копии массивов для дальнейшей работы
            let primaryCopy = [...primaryArray];
            let secondaryCopy = [...secondaryArray];

            // Сортируем оба массива по сумме (чтобы можно было легко сравнивать)
            primaryCopy.sort((a, b) => a.sum - b.sum);
            secondaryCopy.sort((a, b) => a.sum - b.sum);

            // Индексы для обхода обоих массивов
            let i = 0;
            let j = 0;

            // Проходим по обоим массивам
            while (i < primaryCopy.length || j < secondaryCopy.length) {
                const primaryItem = primaryCopy[i];
                const secondaryItem = secondaryCopy[j];

                if (primaryItem && secondaryItem && primaryItem.sum === secondaryItem.sum) {
                    // Если суммы совпадают, добавляем оба элемента в результат
                    resultPrimary.push(primaryItem);
                    resultSecondary.push(secondaryItem);
                    i++;
                    j++;
                } else if (!primaryItem || (secondaryItem && primaryItem.sum > secondaryItem.sum)) {
                    // Если в primary нет элемента или сумма в primary больше, добавляем пустой элемент в primary
                    resultPrimary.push(null);
                    resultSecondary.push(secondaryItem);
                    j++;
                } else {
                    // Если сумма в secondary больше или secondary отсутствует, добавляем пустой элемент в secondary
                    resultPrimary.push(primaryItem);
                    resultSecondary.push(null);
                    i++;
                }
            }

            return {
                primary: resultPrimary,
                secondary: resultSecondary
            };
        },
        filteredFinancialControls(financialControls) {
            return financialControls.filter(fc => fc !== null);
        },
        filteredTransactions(transactions) {
            return transactions.filter(transaction => transaction !== null);
        },
        editFinance(financialControl) {
            this.editingFinance = JSON.parse(JSON.stringify(financialControl));

            this.modalWindowTitle = "Обновить транзакцию";
            this.modalWindowSubmitButtonText = "Обновить";
            this.isEditingFinance = true;

            this.saveFinance = async function () {
                this.isEditingFinance = false;
                await financialControlAPI.update(this.editingFinance);
                this.editingFinance = {};
                this.index();
            }
        },
        async aggregatedExport() {
            const res = await financialControlAPI.export(this.params);
            this.downloadFile(res, 'Саггрегированные транзакции')
            this.toggleExportModal();
        },
        async allTransactionsExport() {
            const res = await financialControlAPI.export_all_transactions(this.params);
            this.downloadFile(res, 'Все транзакции')
            this.toggleExportModal();
        },
        downloadFile(response, fileName) {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName + '.xlsx');
            document.body.appendChild(link);
            link.click();
        },
        toggleExportModal() {
            this.isShowExportModal = !this.isShowExportModal;
        },
        openResponsibleModal() {
            this.isEditingResponsible = true;
        },
        closeResponsibleModal() {
            this.isEditingResponsible = false;
        },
        toggleTableViewMode(mode) {
            this.tableViewMode = mode;
        },
        difference(financialControl) {
            return financialControl.manual_sum - financialControl.auto_sum;
        },
        openImportModal() {
            this.isShowImportModal = true;
        },
        closeImportModal() {
            this.isShowImportModal = false;
        },
        handleFileChange(event) {
            this.selectedFile = event.target.files[0];
        },
        async importFinancialControls() {
            this.settings.isLoading = true;
            // this.showToast('Информация', 'Начало импорта', 'info');

            if (!this.selectedFile) {
                this.showToast('Ошибка', 'Файл не выбран', 'warning');
                return;
            }

            // this.showToast('Информация', 'Файл выбран', 'info');

            // Проверка, является ли выбранный объект файлом
            if (!(this.selectedFile instanceof File)) {
                this.showToast('Ошибка', 'Выбранный объект не является файлом', 'warning');
                return;
            }

            // this.showToast('Информация', 'Выбранный объект является файлом', 'info');

            // Проверка MIME-типа файла
            if (this.selectedFile.type !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
                this.showToast('Ошибка', 'Выбранный файл не является файлом Excel', 'warning');
                return;
            }

            // this.showToast('Информация', 'Выбранный файл является файлом Excel', 'info');

            try {
                this.importErrors = [];
                let formData = new FormData();
                formData.append('file', this.selectedFile);

                // Отправка файла на сервер
                let result = await financialControlAPI.import(formData);
                console.log('Полный результат:', result);

                // Если ошибок нет, закрытие модального окна и обновление данных
                this.closeImportModal();
                this.index(); // Предполагается, что это метод для обновления данных на странице
                this.settings.isLoading = false;
                this.showToast('Успешно', 'Транзакции успешно импортированы', 'success');
            } catch (error) {
                this.settings.isLoading = false;
                this.importErrors = error.response.data
                // this.showToast('Некорректные данные', 'Возникла ошибка при импорте транзакций. Пожалуйста, обратитесь к уполномоченному лицу за подробностями.', 'danger');
            }
        },
    },
    computed: {
        ...mapGetters({ contractors: 'getContractors', legalEntities: 'getLegalEntities', paymentMethods: 'getPaymentMethods' }),
        isShowFincanceModal() {
            return this.isAddingFinance || this.isEditingFinance
        },
        payment_methods_options() {
            if (this.editingFinance.payment_method.legal_entity_id !== null) {
                // const legalEntityName = this.selectedLegalEntity;
                const legalEntity = this.legalEntities.find(
                    (entity) => entity.id === this.editingFinance.payment_method.legal_entity_id
                );

                if (legalEntity) {
                    const legalEntityId = legalEntity.id;

                    // Фильтрация методов оплаты по legal_entity_id
                    const filteredPaymentMethods = this.paymentMethods.filter(
                        (method) => method.legal_entity_id === legalEntityId
                    );

                    // Массив для вариантов выбора в селекторе
                    const options = filteredPaymentMethods.map((method) => ({
                        id: method.id,
                        name: method.name,
                    }));

                    return options;
                }
            }

            return [];
        },
        paymentMethodsWithLegalEntities() {
            this.paymentMethods.forEach(method => {
                method.name = method.legal_entity_name + " " + method.name;
            });
            return this.paymentMethods;
        },
        canUserCreate() {
            return this.checkPermission('financial_controls_create');
        },
        canUserEdit() {
            return this.checkPermission('financial_controls_update');
        },
        canUserDelete() {
            return this.checkPermission('financial_controls_delete');
        },
    },
    mounted() {
        this.$store.dispatch('loadContractorsData');
        this.$store.dispatch('loadLegalEntities');
        this.$store.dispatch('loadPaymentMethods', true);

        let localStorageFinancialControlParams = JSON.parse(localStorage.getItem('financial_controls_params'));
        if (localStorageFinancialControlParams['sort_field'] === "id") {
            localStorageFinancialControlParams['sort_field'] = "payment_date";
            localStorage.setItem('financial_controls_params', JSON.stringify(localStorageFinancialControlParams));
        }

        this.initEmployees();
    }
}
</script>
