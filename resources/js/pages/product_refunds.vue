<template>
    <Table>
        <template v-slot:filters>
            <FilterInput v-model="params.product_filter" label="Товар" placeholder="Артикул или название"></FilterInput>
            <FilterInputBetween v-model="params.amount_filter" label="Количество" type="number"></FilterInputBetween>
            <FilterInputBetween v-model="params.price_filter" label="Цена" type="number"></FilterInputBetween>
            <FilterMultipleSelect v-model="params.contractor_filter" placeholder="Выбрать поставщика" label="Поставщик" :options="contractors">
            </FilterMultipleSelect>
            <FilterSelect v-model="params.contractors_type_filter" label="Тип поставщика" :options="contractorTypes" />
            <FilterSelectSearch v-model="params.status_filter" placeholder="Выбрать статус возврата" label="Статус возврата" :options="refundStatusOptions"></FilterSelectSearch>
            <FilterSelectSearch v-model="params.product_location_filter" label="Где товар" :options="productLocationOptions"></FilterSelectSearch>
            <FilterInput v-model="params.order_number_filter" label="Заказ" placeholder="Номер заказа"></FilterInput>
            <FilterInput v-model="params.comment_filter" label="Комментарий"></FilterInput>
            <FilterInput v-model="params.address_filter" label="Адрес возврата" placeholder="Адрес или часть адреса"></FilterInput>
            <FilterInputBetween v-model="params.delivery_date_filter" label="Дата возврата" type="date"></FilterInputBetween>
            <FilterInputBetween v-model="params.created_at_filter" label="Создан" type="date"></FilterInputBetween>
            <FilterInputBetween v-model="params.completed_at_filter" label="Завершен" type="date"></FilterInputBetween>
            <FilterSelectSearch v-model="params.order_status_filter" label="Статус заказа" :options="orderStatuses"></FilterSelectSearch>
        </template>

        <template v-slot:thead>
            <TH width="30px"> </TH>
            <TH field="order_number" width="70px" align="center">Номер заказа</TH>
            <TH align="center">Статус заказа</TH>
            <TH width="70px" align="center">Поставщик</TH>
            <TH field="status" width="70px" align="center">Статус возврата</TH>
            <TH field="delivery_date" align="center">Дата возврата</TH>
            <TH field="delivery_address" align="center">Адрес возврата</TH>
            <TH field="product_location" align="center">Где товар</TH>
            <TH field="comment" align="center">Комментарий</TH>
            <TH field="created_at" align="center">Создан</TH>
            <TH field="completed_at" align="center">Завершен</TH>
            <TH v-if="canUserEdit" width="30px"> </TH>
        </template>

        <template v-slot:tbody>
            <TR v-for="refund in refunds" :withInnerTable="true" :key="refund">
                <template v-slot:default>
                    <TD align="center">
                        <a target="_blank" :href="'https://babylissrus.retailcrm.ru/orders/' + refund.external_id + '/edit'"
                            style="text-decoration: none;">
                            <div class="d-flex align-items-center justify-content-center">
                                <img dalt="RetailCRM"
                                    src="https://s3-s1.retailcrm.tech/ru-central1/retailcrm-static/branding/retailcrm/logo/logo_icon_core.svg"
                                    style="width: 18px; height: 18px;">
                                <span class="ps-2"> {{ refund.order_number }} </span>
                            </div>
                        </a>
                    </TD>
                    <TD align="center">
                        <OrderStatus style="font-size: 12px;" :statusName="refund.order_status" :groupCode="refund.order_status_group"/>
                    </TD>
                    <TD align="center"> {{ contractorsList(refund) }} </TD>
                    <TD align="center"> {{ refund.status ? 'Завершен' : 'Не завершен' }} </TD>
                    <TD align="center"> {{ formatDate(refund.delivery_date, 'DD.MM.YYYY') }} </TD>
                    <TD align="center" class="text-wrap" style="max-width: 250px;"> {{ refund.delivery_address ?
                        refund.delivery_address : '—' }} </TD>
                    <TD align="center"> {{ refund.product_location }} </TD>
                    <TD align="left" :title="refund.comment">
                        <span class="d-inline-block refund-comment-wrapper"
                            style="max-width: 250px; text-overflow: ellipsis; white-space: nowrap; overflow: hidden;">
                            {{ refund.comment }}
                        </span>
                    </TD>
                    <TD align="center"> {{ formatDate(refund.created_at) }} </TD>
                    <TD align="center"> {{ refund.completed_at ? refund.completed_at : '—' }} </TD>
                    <TD v-if="canUserEdit" align="center">
                        <button @click.stop="showRefund(refund.id)" type="button" class="btn btn-outline-primary border-0">
                            <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                        </button>
                    </TD>
                </template>

                <template v-slot:sub-thead>
                    <TH width="80px" align="center"> Артикул </TH>
                    <TH width="300px" align="left"> Название </TH>
                    <TH width="80px" align="center"> Поставщик </TH>
                    <TH width="80px" align="center"> Количество </TH>
                    <TH width="80px" align="center"> Цена </TH>
                </template>

                <template v-slot:sub-tbody>
                    <tr v-for="position in refund.positions" :key="position" style="vertical-align: middle; border-style: none!important;">
                        <TD align="center"> {{ position.product_main_sku }} </TD>
                        <TD align="left"  > {{ position.product_name }} </TD>
                        <TD align="center"> {{ position.contractor_name }} </TD>
                        <TD align="center"> {{ position.amount }} шт. </TD>
                        <TD align="center"> {{ position.avg_price ? position.avg_price.priceFormat(true) : '0,00 ₽' }}</TD>
                    </tr>
                </template>
            </TR>

            <DefaultModal :width="'500px'" v-if="isShowExportModal" @close_modal="toggleExportModal()"
                title="Выгрузка возвратов ДС">
                <div class="d-flex p-3">
                    <span>Что вы хотите выгрузить?</span>
                </div>
                <div class="d-flex justify-content-start p-2 mb-0 bg-light border-top">
                    <button class="btn btn-primary m-1" @click="productRefundsExport()">
                        <font-awesome-icon icon="fa-solid fa-rotate-left" class="pe-2" />
                        Возвраты
                    </button>
                    <button class="btn btn-primary m-1" @click="productRefundPositionsExport()">
                        <font-awesome-icon icon="fa-solid fa-boxes-stacked" class="pe-2" />
                        Товары
                    </button>
                    <button class="btn bg-gradient btn-light border m-1 ms-auto" style="height: 38px;"
                        @click="toggleExportModal()">Отмена</button>
                </div>
            </DefaultModal>
        </template>
    </Table>
</template>

<style>
.refund-comment-wrapper .textarea-table-popup {
    position: relative;
    margin-top: 0;
}

.refund-comment-wrapper .textarea-table-popup textarea {
    width: 100%;
    position: relative;
}
</style>

<script>
import { mapGetters } from 'vuex';
import { productRefundAPI } from '../api/product_refund_api';
import { orderStatusAPI } from '../api/order_status_api';

import indexTableMixin from '../utils/indexTableMixin';
import OrderStatus from "../components/Other/OrderStatus.vue";
import FilterInputBetween from '../ui/inputs/FilterInputBetween.vue'
import DefaultModal from '../components/modals/default_modal.vue';
import TextAreaPopup from '../components/popups/table_textarea_popup.vue';

export default {
    mixins: [indexTableMixin],
    components: { TextAreaPopup, DefaultModal, FilterInputBetween, OrderStatus },
    data() {
        return {
            refunds: [],
            refundStatusOptions: [
                { id: 0, name: 'Не завершен' },
                { id: 1, name: 'Завершен' }
            ],
            productLocationOptions: [
                { id: 'Нет', name: 'Нет' },
                { id: 'В офисе', name: 'В офисе' },
                { id: 'У курьера', name: 'У курьера' },
                { id: 'Частично - Офис/Курьер', name: 'Частично - Офис/Курьер' },
                { id: 'Частично - Офис/Поставщик', name: 'Частично - Офис/Поставщик' },
                { id: 'Частично - Поставщик/Курьер', name: 'Частично - Поставщик/Курьер' },
                { id: 'Вернули поставщику', name: 'Вернули поставщику' },
                { id: 'В ТК', name: 'В ТК' },
            ],
            contractorTypes: [
                { id: 1, name: 'Только основные' },
                { id: 0, name: 'Только дополнительные' }
            ],
            isShowExportModal: false,
            orderStatuses: []
        }
    },

    mounted() {
        this.$store.dispatch('loadContractorsData');
    },
    methods: {
        /**
         * Инициализация таблицы.
         */
        async initSettings() {
            this.settings.tableTitle = 'Возвраты на склад';
            this.settings.localStorageKey = 'product_refunds_params'
            this.settings.withCreateButton = false;
            this.settings.withHeader = false;
            this.settings.withInfo = false;
            this.settings.withFooter = true;
            this.settings.isLoading = true;
            this.settings.saveParams = true;
            this.settings.withExport = true;
            this.settings.withFilterTemplates = true;

            this.settings.indexAPI = params => productRefundAPI.index(params);

            this.onInitData = res => {
                this.refunds = res.data.data;
                this.loadOrderStatuses();
            }

            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'created_at';
                defaultParams.sort_type = this.params.sort_type || 'desc';
            }

            this.onExport = () => this.toggleExportModal();
        },

        async loadOrderStatuses() {
            const res = await orderStatusAPI.index();
            this.orderStatuses = res.data;
        },

        /**
         * Переключение состояния модального окна с выбором типа экспорта.
         */
        toggleExportModal() {
            this.isShowExportModal = !this.isShowExportModal;
        },

        /**
         * Выгрузка Excel-таблицы с возвратами на компьютер.
         */
        async productRefundsExport() {
            const res = await productRefundAPI.export(this.params);
            this.downloadFile(res, 'Выгрузка возвратов товаров')
            this.toggleExportModal();
        },

        /**
         * Выгрузка Excel-таблицы с товарами в возвратах на компьютер.
         */
        async productRefundPositionsExport() {
            const res = await productRefundAPI.exportProducts(this.params);
            this.downloadFile(res, 'Выгрузка товарных позиций в возвратах')
            this.toggleExportModal();
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
         * Открывает страницу выбранного возврата.
         */
        showRefund(id) {
            this.$router.push('/product_refunds/' + id + '/edit');
        },
        exchangeType(exchange_type){
            if(exchange_type === 1)
                return 'Со склада';
            else if(exchange_type === 0)
                return 'От клиента';

            return  null;
        },
        contractorsList(refund) {
            // Получаем массив всех имен поставщиков
            let contractors = refund.positions.map(position => position.contractor_name);

            // Удаляем дубликаты из массива
            let uniqueContractors = [...new Set(contractors)];

            // Объединяем массив имен в одну строку, разделенную запятыми
            return uniqueContractors.join(', ');
        }
    },
    computed: {
        ...mapGetters({ contractors: 'getContractors' }),
        canUserEdit() {
            return this.checkPermission('product_refund_update');
        },
    }
}
</script>
