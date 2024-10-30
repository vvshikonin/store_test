<template>
    <Table>
        <template v-slot:filters>
            <FilterInput v-model="params.product_filter" label="Товар" type="text" placeholder="Артикул или название">
            </FilterInput>
            <FilterInputBetween v-model="params.product_price_filter" type="number" step="0.01" label="Цена товара"
                style="width: 16.6%;" />
            <FilterInput v-model="params.invoice_number_filter" label="Номер счета" type="text"></FilterInput>
            <FilterInputBetween v-model="params.invoice_date_filter" label="Дата счета" type="date"
                style="width: 16.6%;" />
            <FilterMultipleSelect v-model="params.contractors_filter" label="Поставщик" placeholder="Выбрать поставщика"
                :options="contractors"></FilterMultipleSelect>
            <FilterSelect v-model="params.contractors_type_filter" label="Тип поставщика" :options="contractorTypes" />
            <FilterInputBetween v-model="params.sum_filter" type="number" step="0.01" label="Сумма"
                style="width: 16.6%;" />
            <FilterMultipleSelect v-model="params.status_filter" :options="invoiceStatuses" placeholder="Выбрать статус"
                label="Статус"></FilterMultipleSelect>
            <FilterInputBetween v-model="params.received_at_filter" label="Оприходован" type="date"
                style="width: 16.6%;" />
            <FilterInputBetween v-model="params.planned_delivery_date_filter" label="Пл.дата доставки" type="date"
                style="width: 16.6%;" />
            <FilterInput v-model="params.comment_filter" label="Комментарий" type="text"></FilterInput>
            <FilterSelect v-model="params.delivery_type_filter" :options="deliveryTypes"
                placeholder="Выбрать способ доставки" label="Способ доставки"></FilterSelect>
            <FilterMultipleSelect v-model="params.payment_method_filter" :options="paymentMethodsWithLegalEntities"
                placeholder="Выбрать способ выкупа" label="Способ выкупа"></FilterMultipleSelect>
            <FilterInputBetween v-model="params.created_at_filter" label="Дата создания" type="date"
                style="width: 16.6%;" />
            <FilterSelect v-model="params.payment_status_filter" :options="paymentStatuses" placeholder="Статус оплаты"
                label="Оплачен"></FilterSelect>
            <FilterSelect v-model="params.legal_entity_filter" :options="legalEntities" label="Юр. лицо"></FilterSelect>
            <!-- <FilterSelect v-model="params.payment_confirm_filter" :options="paymentConfirmStatuses"
                placeholder="Статус подтверждения оплаты" label="Оплата подтверждена директором"></FilterSelect> -->
            <FilterInputBetween v-model="params.payment_date_filter" label="Дата оплаты" type="date"
                style="width: 16.6%;" />
            <FilterInputBetween v-model="params.status_set_at_filter" label="Дата установки статуса" type="date"
                style="width: 16.6%;" />
            <FilterSelect v-model="params.has_invoice_file" :options="hasFileOptions" label="Файл счёта" />
            <FilterSelect v-model="params.has_receipt_file" :options="hasFileOptions" label="Файл чека" />
            <FilterInputBetween v-model="params.payment_order_date_filter" label="Дата заведения пл. поручения" type="date"
                style="width: 16.6%;" />
        </template>
        <template v-slot:thead>
            <TH v-if="isShowBulkCheckBoxes" width="10px" style="font-size: 13px;">
                <input @change="toggleAllBulkCheck()" type="checkbox" class="form-check-input"
                    v-bind="allBulkCheckAttributes">
            </TH>
            <TH field="number">Номер</TH>
            <TH field="date">Дата счета</TH>
            <TH field="contractor_name">Поставщик</TH>
            <TH field="total_sum" align="center" width="113px">Сумма</TH>
            <TH field="status">Статус счета</TH>
            <TH field="received_at"> Оприходован</TH>
            <TH field="is_edo">Чек в ЭДО</TH>
            <TH field="planned_delivery_date">Дата доставки</TH>
            <TH field="delivery_type">Способ доставки</TH>
            <TH field="comment">Комментарий</TH>
            <TH field="legal_entity_name">Юр. лицо</TH>
            <TH field="payment_method_id">Способ выкупа</TH>
            <TH field="payment_status" align="center">Оплачен</TH>
            <!-- <TH field="payment_confirm" align="center">Оплата подтверждена</TH> -->
            <TH field="payment_date" align="center">Дата оплаты</TH>
            <TH field="created_at">Дата и время</TH>
        </template>
        <template v-slot:tbody>
            <TR v-for="invoice in invoices" @click_row="toInvoice(invoice.id)" :key="invoice.id">
                <TD v-if="isShowBulkCheckBoxes" @click.stop style="padding-left: 15px;">
                    <input v-model="bulkParams.ids" :value="invoice.id" type="checkbox" class="form-check-input">
                </TD>
                <TD fw="bold"><a :href="'#/invoices/' + invoice.id + '/edit'">{{ invoice.number }}</a></TD>
                <TD>{{ moment(invoice.date) }}</TD>
                <TD>{{ invoice.contractor_name }}</TD>
                <TD align="center" fw="bold" v-if="invoice.total_sum">{{ invoice.total_sum.priceFormat(true) }}</TD>
                <TD align="center" fw="bold" v-else>0,00 ₽</TD>
                <TD>
                    <InvoiceStatus :statusCode="invoice.status" style="width: 195px; font-size: 12px;"></InvoiceStatus>
                </TD>
                <TD>{{ formatDate(invoice.received_at, 'DD.MM.YYYY HH:mm:ss') }}</TD>
                <TD align="center">{{ invoice.is_edo ? 'Да' : '—' }}</TD>
                <!-- <TD>{{ formatDate(invoice.planned_delivery_date) }}</TD> -->
                <TD
                    v-if="(invoice.min_delivery_date == invoice.max_delivery_date) || (invoice.min_delivery_date && !invoice.max_delivery_date) || (!invoice.min_delivery_date && invoice.max_delivery_date)">
                    {{ formatDate(invoice.min_delivery_date) }} </TD>
                <TD v-else>
                    <div>{{ formatDate(invoice.min_delivery_date) }}</div>
                    <div>{{ formatDate(invoice.max_delivery_date) }}</div>
                </TD>
                <TD>{{ deliveryType(invoice.delivery_type) }}</TD>
                <TD>{{ formatNullValue(invoice.comment) }}</TD>
                <TD>{{ formatNullValue(invoice.legal_entity_name) }}</TD>
                <TD>{{ invoice.payment_method_name }}</TD>
                <TD :class="{ 'text-danger': invoice.payment_status === 2 }" align="center">{{ paymentStatus(invoice.payment_status) }}</TD>
                <!-- <TD align="center">{{ paymentConfirmStatus(invoice.payment_confirm) }}</TD> -->
                <TD align="center">{{ formatDate(invoice.payment_date) }}</TD>
                <TD>{{ formatDate(invoice.created_at, 'DD.MM.YYYY HH:mm:ss') }}</TD>
            </TR>
        </template>
        <template v-slot:tfoot>
            <div v-if="invoices.length" class="d-inline">
                <span class="pe-3">Сумма по ожидаемым: {{ summary.expected_sum.priceFormat(true) }}</span>
                <span class="pe-3">Сумма по оприходу: {{ summary.received_sum.priceFormat(true) }}</span>
                <span class="pe-3">Сумма по отказам: {{ summary.refused_sum.priceFormat(true) }}</span>
                <span class="pe-3">Сумма по счетам: {{ summary.total_sum.priceFormat(true) }}</span>
                <span>Всего счетов: {{ meta.total }}</span>
            </div>
            <!-- Экспорты -->
            <DefaultModal :width="'500px'" v-if="isShowExportModal" @close_modal="toggleExportModal()"
                title="Выгрузка счетов">
                <div class="d-flex justify-content-center m-3 flex-wrap">
                    <a @click.prevent="handleExport(API.export, 'Выгрузка счетов')" href="#" class="link-primary me-3">
                        Счета </a>
                    <a @click.prevent="handleExport(API.exportProducts, 'Выгрузка товаров в счетах')" href="#"
                        class="link-primary me-3"> Товары в счетах </a>
                    <a @click.prevent="handleExport(API.exportForControl, 'Выгрузка товаров для контролирующих')"
                        href="#" class="link-primary me-3"> Контроль закрывающих </a>
                    <a @click.prevent="handleExport(API.exportForReceive, 'Выгрузка товаров для оприходования')"
                        href="#" class="link-primary me-3"> Для оприходования </a>
                </div>
                <div class="d-flex justify-content-start p-2 mb-0 bg-light border-top">
                    <button class="btn bg-gradient btn-light border ms-auto"
                        @click="toggleExportModal()">Отмена</button>
                </div>
            </DefaultModal>
        </template>
        <template v-slot:bottom-box>
            <div class="ps-5">
                <small>Выделено {{ bulkParams.ids.length }} элементов</small>
                <button v-if="canUserBulkEdit" @click="toggleBulkEditModal()"
                    class="btn btn-light border ms-4">Редактировать</button>
                <button v-if="canUserBulkDestroy" @click="toggleBulkDestroyModal()"
                    class="btn btn-danger ms-3">Удалить</button>
            </div>
            <!-- Массовое редактирование -->
            <DefaultModal v-if="isShowBulkEdit" title="Массовое редактирование" width="800px"
                @close_modal="toggleBulkEditModal()">
                <div class="w-100">
                    <SelectInput v-model="bulkParams.payment_status" class="w-75" label="Оплачен"
                        placeholder="Выберете статус оплаты" :options="paymentStatuses"
                        :disabled="!checkPermission('invoice_update')" />

                    <DefaultInput v-model="bulkParams.payment_date" class="w-75" label="Дата оплаты"
                        type="date" :disabled="!checkPermission('invoice_update')"/>
                    <hr class="text-muted">
                    <div class="form-check ms-3 pb-3" style="font-size: 13px;">
                        <input v-model="bulkParams.receive_all" class="form-check-input" type="checkbox" id="receive-check">
                        <label class="form-check-label text-muted" for="receive-check"> Оприходовать товары </label>
                    </div>
                    <div class="form-check ms-3 pb-3" style="font-size: 13px;">
                        <input v-model="bulkParams.is_edo_all" class="form-check-input" type="checkbox" id="edo-check">
                        <label class="form-check-label text-muted" for="edo-check"> Проставить чек в ЭДО </label>
                    </div>
                </div>
                <div class="w-100 p-3 border-top rounded-bottom bg-light">
                    <button @click="bulkEdit()" class="btn btn-primary">Сохранить</button>
                    <button @click="toggleBulkEditModal()" class="ms-2 btn btn-light border">Отмена</button>
                </div>
            </DefaultModal>
            <DestroyConfirmModal v-if="isShowBulkDestroyConfirm" entityName="выделенные счета"
                @cancel="toggleBulkDestroyModal()" @confirm="bulkDestroy()"></DestroyConfirmModal>
        </template>
    </Table>
</template>
<script>
import { invoiceAPI } from '../api/invoice_api.js';
import { mapGetters } from 'vuex';

import IndexTableMixin from '../utils/indexTableMixin.js'
import FilterInputBetween from '../ui/inputs/FilterInputBetween.vue'

import InvoiceStatus from '../components/Other/invoice_status.vue'
import DestroyConfirmModal from '../components/modals/DestroyConfirmModal.vue';
import DefaultModal from '../components/modals/default_modal.vue';
import SelectInput from '../components/inputs/select_input.vue';
import DefaultInput from '../components/inputs/default_input.vue';

export default {
    components: { InvoiceStatus, DefaultModal, DestroyConfirmModal, SelectInput, FilterInputBetween, DefaultInput },
    mixins: [IndexTableMixin],
    data() {
        return {
            invoiceStatuses: [
                { id: 0, name: 'Ожидается' },
                { id: 1, name: 'Частично оприходован' },
                { id: 2, name: 'Оприходован' },
                { id: 3, name: 'Отменён' }
            ],
            deliveryTypes: [
                { id: 0, name: 'Курьером' },
                { id: 1, name: 'Самовывоз' },
                { id: 2, name: 'Смешанный' }
            ],
            paymentTypes: [
                { id: 0, name: 'Наличными' },
                { id: 1, name: 'Безналичными' }
            ],
            cashlessPaymentTypes: [
                { id: 'РС Альфа', name: 'РС Альфа' },
                { id: 'Карта ТКС', name: 'Карта ТКС' },
                { id: 'Карта Райффайзен', name: 'Карта Райффайзен' },
                { id: 'РС Сбер', name: 'РС Сбер' },
            ],
            paymentStatuses: [
                { id: 0, name: 'Не платить (оплата не требуется)' },
                { id: 1, name: 'Оплачен' },
                { id: 2, name: 'Требует оплаты' }
            ],
            paymentConfirmStatuses: [
                { id: 0, name: 'Нет' },
                { id: 1, name: 'Да' }
            ],
            legalEntities: [
                { id: 1, name: 'ИП Грибанов' },
                { id: 2, name: 'ИП Шиконин' }
            ],
            contractorTypes: [
                { id: 1, name: 'Только основные' },
                { id: 0, name: 'Только дополнительные' }
            ],

            invoices: [],
            summary: {},

            isShowBulkEdit: false,
            isShowBulkDestroyConfirm: false,

            isShowExportModal: false,

            bulkParams: {
                ids: []
            },

            hasFileOptions: [
                { id: '1', name: 'Есть' },
                { id: '0', name: 'Нет' }
            ],
        }
    },
    computed: {
        ...mapGetters({ contractors: 'getContractors', paymentMethods: 'getPaymentMethods', legalEntities: 'getLegalEntities' }),

        /**
         * Вычислает атрибуты checked и indeterminate для чекбокса выбора всех записей на странице.
         */
        allBulkCheckAttributes() {
            const selectedInvoicesCount = this.bulkParams.ids.length;
            const invoicesCount = this.invoices.length;

            const indeterminate = selectedInvoicesCount > 0 && selectedInvoicesCount < invoicesCount;
            const checked = selectedInvoicesCount == invoicesCount;
            return { indeterminate, checked }
        },

        /**
         * Проверяет наличие разрешений у пользователя на массовое редактирование.
         */
        canUserBulkEdit() {
            return this.checkPermission('invoice_update') || this.checkPermission('invoice_payment_confirm_update') || this.checkPermission('invoice_credited_update')
        },

        /**
         * Проверяет наличие разрешений у пользователя на массовое удаление.
         */
        canUserBulkDestroy() {
            return this.checkPermission('invoice_delete')
        },

        /**
         * Вычислает необходимость отображения чекбоксов для выбора записей.
         */
        isShowBulkCheckBoxes() {
            return this.canUserBulkEdit || this.canUserBulkDestroy;
        },

        paymentMethodsWithLegalEntities() {
            this.paymentMethods.forEach(method => {
                method.name = method.legal_entity_name + " " + method.name;
            });
            return this.paymentMethods;
        },

        /**
         * Возвращает `API` инстанс счетов.
         */
        API() {
            return invoiceAPI;
        }

    },

    watch: {
        /**
         * Проверяет наличие записей в параметрах запроса для массового редактирования для отображения интерфейсов редактирования.
         */
        bulkParams: {
            handler(params) {
                if (params.ids.length)
                    this.settings.withBottomBox = true
                else this.settings.withBottomBox = false;
            },
            deep: true
        }
    },
    methods: {
        /**
         * Инициализация таблицы.
         */
        initSettings() {
            this.settings.tableTitle = 'Счета';
            this.settings.createButtonText = 'Создать счет';
            this.settings.localStorageKey = 'invoices_params'

            this.settings.withCreateButton = this.checkPermission('invoice_create');
            this.settings.withAdditionalHeaderButton = false;
            this.settings.withHeader = false;
            this.settings.withExport = true;
            this.settings.isLoading = true;
            this.settings.saveParams = true;
            this.settings.withBottomBox = false;
            this.settings.withFilterTemplates = true;

            this.settings.indexAPI = params => invoiceAPI.index(params);

            this.onInitData = res => {
                this.invoices = res.data.data;
                this.summary = res.data.summary;
                this.initBulkParamsDefault();
            }

            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'created_at';
                defaultParams.sort_type = this.params.sort_type || 'desc';
            }

            this.onClickCreateButton = () => this.$router.push('/invoices/new');

            this.onExport = () => this.toggleExportModal();
        },

        /**
         * Выделает/снимает выделение всех записей на странице.
         */
        toggleAllBulkCheck() {
            const invoicesIds = this.invoices.map(invoice => invoice.id)
            const selectedInvoices = this.bulkParams.ids;

            if (selectedInvoices.equals(invoicesIds)) {
                this.bulkParams.ids = [];
            } else {
                this.bulkParams.ids = invoicesIds;
            }
        },

        /**
         * Отправляет запрос на массовое редактирование.
         */
        async bulkEdit() {
            this.toggleBulkEditModal();
            this.settings.isCover = true;
            await invoiceAPI.bulkUpdate(this.bulkParams);
            this.settings.isCover = false;
            this.index();
        },
        /**
         * Скрывает/отображает окно массового редактирования.
         */
        toggleBulkEditModal() {
            this.isShowBulkEdit = !this.isShowBulkEdit;
        },

        /**
         * Отправляет запрос на массовое удаление.
         */
        async bulkDestroy() {
            this.toggleBulkDestroyModal();
            this.settings.isCover = true;
            await invoiceAPI.bulkDestroy({ ids: this.bulkParams.ids });
            this.settings.isCover = false;
            this.index();
        },

        /**
         * Скрывает/отображает окно подтверждения массового удаления.
         */
        toggleBulkDestroyModal() {
            this.isShowBulkDestroyConfirm = !this.isShowBulkDestroyConfirm;
        },

        /**
         * Иницыализирует параметры запроса массового редактирования.
         */
        initBulkParamsDefault() {
            this.bulkParams = {
                ids: []
            }
        },

        /**
         * Переключает состояние отображения окна выбора экспорта.
         */
        toggleExportModal() {
            this.isShowExportModal = !this.isShowExportModal;
        },

        /**
         * Обрабатывает вызов `API` методов экспорта.
         * @param {Callback} apiExport
         */
        async handleExport(apiExport = async () => null, fileName = 'Экспорт') {
            this.toggleExportModal();

            this.settings.isCover = true;
            const res = await apiExport(this.params);
            this.settings.isCover = false;

            if (res) this.downloadFile(res, fileName)
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

        /**
         * Форматирует delivery_type.
         *
         * @param {number} type
         * @returns {string}
         */
        deliveryType(type) {
            if (type !== null)
                return this.deliveryTypes[type].name;
            return "—";
        },

        /**
         * Форматирует payment_type.
         *
         * @param {number} type
         * @returns {string}
         */
        paymentType(type) {
            if (type !== null)
                return this.paymentTypes[type].name;
            return "—";
        },


        /**
         * Форматирует payment_status.
         *
         * @param {number} status
         * @returns {string}
         */
        paymentStatus(status) {
            return this.paymentStatuses[status].name
        },

        /**
         * Форматирует payment_confirm_status.
         *
         * @param {number} status
         * @returns {string}
         */
        paymentConfirmStatus(status) {
            return this.paymentConfirmStatuses[status].name
        },

        /**
         * Обрабатывает поля со значением null.
         *
         * @param {any} value
         * @returns {string}
         */
        formatNullValue(value) {
            return value || '—';
        },
        /**
         * Переходит на страницу редактирования счета.
         *
         * @param {number} id
         */
        toInvoice(id) {
            this.$router.push('/invoices/' + id + '/edit');
        },
    },
    mounted() {
        this.$store.dispatch('loadContractorsData');
        this.$store.dispatch('loadLegalEntities');
        this.$store.dispatch('loadPaymentMethods');
    }
}
</script>
