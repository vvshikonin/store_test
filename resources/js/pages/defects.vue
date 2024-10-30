<template>
    <Table>
        <template v-slot:filters>
            <FilterInput v-model="params.orderFilter" label="Номер заказа" type="text"></FilterInput>
            <FilterInput v-model="params.productFilter" label="Товар" type="text"></FilterInput>
            <FilterMultipleSelect v-model="params.contractorsFilter" label="Поставщик" placeholder="Выбрать поставщика"
                :options="contractors"></FilterMultipleSelect>
            <FilterSelect v-model="params.contractorsTypeFilter" label="Тип поставщика" :options="contractorTypes" />
            <FilterMultipleSelect v-model="params.legal_entity_ids" label="Юр. лицо" placeholder="Выбрать юр.лицо"
                :options="legalEntities"></FilterMultipleSelect>
            <FilterMultipleSelect v-model="params.payment_method_ids" label="Способ оплаты"
                placeholder="Выбрать способ оплаты" :options="paymentMethodsWithLegalEntities"></FilterMultipleSelect>
            <FilterInput v-model="params.commentFilter" label="Комментарий" type="text"></FilterInput>
            <FilterInputBetween v-model:start="params.created_at_start" v-model:end="params.created_at_end"
                v-model:equal="params.created_at_equal" v-model:notEqual="params.created_at_not_equal"
                label="Дата создания" type="date"></FilterInputBetween>
            <FilterInput v-model="params.deliveryAddressFilter" label="Адрес доставки" type="text"></FilterInput>
            <FilterMultipleSelect v-model="params.product_location_ids" label="Где товар"
                placeholder="Выбрать где товар" :options="product_locations"></FilterMultipleSelect>
            <FilterMultipleSelect v-model="params.replacement_type_ids" label="Тип замены"
                placeholder="Выбрать тип замены" :options="replacement_types"></FilterMultipleSelect>
            <FilterMultipleSelect v-model="params.money_refund_status_ids" label="Возврат средств"
                placeholder="Выбрать статус возврата ДС" :options="money_refund_statuses"></FilterMultipleSelect>
            <FilterMultipleSelect v-model="params.refund_type_ids" label="Тип возврата"
                placeholder="Выбрать тип возврата" :options="refund_types"></FilterMultipleSelect>
            <FilterInputBetween v-model:start="params.delivery_date_start" v-model:end="params.delivery_date_end"
                v-model:equal="params.delivery_date_equal" v-model:notEqual="params.delivery_date_not_equal"
                label="Дата доставки" type="date"></FilterInputBetween>
        </template>
        <template v-slot:thead>
            <TH width="30px"> </TH>
            <TH field="created_at">Дата создания</TH>
            <TH field="number">Номер заказа</TH>
            <TH width="270px">Статус заказа</TH>
            <TH field="contractor_name">Поставщик</TH>
            <TH field="legal_entities.name">Юр. лицо</TH>
            <TH field="payment_methods.name">Способ оплаты</TH>
            <TH field="amount">Кол-во</TH>
            <TH width="100px" field="sum">Сумма</TH>
            <TH field="comment">Комментарий</TH>
            <TH field="product_location">Где товар</TH>
            <TH field="replacement_type">Тип замены</TH>
            <TH field="delivery_address">Адрес доставки</TH>
            <TH field="money_refund_status">Возврат средств</TH>
            <TH field="refund_type">Тип возврата</TH>
            <TH field="delivery_date">Дата доставки</TH>
            <TH field="delivery_date">Дата завершения</TH>
            <TH></TH>
        </template>
        <template v-slot:tbody>
            <TR v-for="defect in defects" :withInnerTable="true">
                <template v-slot:default>
                    <TD>{{ formatDate(defect.created_at) }}</TD>
                    <TD>
                        <!-- {{ defect.order_number }} -->
                        <a target="_blank"
                            :href="'https://babylissrus.retailcrm.ru/orders/' + defect.order_external_id + '/edit'"
                            style="text-decoration: none;">
                            <div class="d-flex align-items-center">
                                <img dalt="RetailCRM"
                                    src="https://s3-s1.retailcrm.tech/ru-central1/retailcrm-static/branding/retailcrm/logo/logo_icon_core.svg"
                                    style="width: 18px; height: 18px;">
                                <span class="ps-2"> {{ defect.order_number }} </span>
                            </div>
                        </a>
                    </TD>
                    <TD>
                        <OrderStatus :statusName="defect.order_status" :groupCode="defect.order_status_group" />
                    </TD>
                    <TD><span v-for="contractor in defect.contractor_names"> {{ contractor }} </span></TD>
                    <TD>{{ defect.legal_entity }}</TD>
                    <TD>{{ defect.payment_method }}</TD>
                    <TD>{{ defect.amount }}</TD>
                    <TD>{{ defect.summ }} ₽</TD>
                    <TD>{{ defect.comment }}</TD>
                    <TD>{{ productLocationName(defect.product_location) }}</TD>
                    <TD>{{ replacementTypeName(defect.replacement_type) }}</TD>
                    <TD>{{ defect.delivery_address }}</TD>
                    <TD>{{ moneyRefundStatusName(defect.money_refund_status) }}</TD>
                    <TD>{{ refundTypeName(defect.refund_type) }}</TD>
                    <TD>{{ formatDate(defect.delivery_date) }}</TD>
                    <TD v-if="defect.completed_at">{{ defect.completed_at }}</TD>
                    <TD v-else>Не завершён</TD>
                    <TD>
                        <button @click="toDefect(defect.id)" class="btn btn-outline-primary border-0">
                            <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                        </button>
                    </TD>
                </template>
                <template v-slot:sub-thead>
                    <TH>Артикул</TH>
                    <TH>Название</TH>
                    <TH>Поставщик</TH>
                    <TH>Цена</TH>
                    <TH>Количество</TH>
                </template>
                <template v-slot:sub-tbody>
            <TR v-for="order_product in defect.order_products">
                <TD>{{ order_product.product_main_sku }}</TD>
                <TD>{{ order_product.product_name }}</TD>
                <TD>{{ order_product.contractor_name }}</TD>
                <TD>{{ order_product.avg_price }} ₽ </TD>
                <TD>{{ order_product.amount }}</TD>
            </TR>
        </template>
        </TR>
</template>
<template v-slot:tfoot>
    <DefaultModal :width="'660px'" v-if="selectExportType" @close_modal="selectExportType = false"
        title="Выберите тип выгрузки">
        <div class="d-flex p-3">
            <span>Что вы хотите выгрузить?</span>
        </div>
        <div class="d-flex justify-content-end p-2 mb-0 bg-light">
            <button disabled="true" class="btn bg-gradient btn-outline-primary m-1" @click="onDefectsExport()"
                title="Выгрузить все заказы-браки, удовлетворяющие фильтру. Без товарных позиций.">Cписок
                браков</button>
            <button class="btn bg-gradient btn-outline-primary m-1" @click="onDefectProductsExport()"
                title="Выгрузить все бракованные товарные позиции, удовлетворяющие фильтру. С указанием цен и точным кол-вом.">Cписок
                бракованных товаров</button>
            <button class="btn bg-gradient btn-light border m-1" @click="selectExportType = false">Отмена</button>
        </div>
    </DefaultModal>
</template>
</Table>
</template>
<script>
import { defectAPI } from '../api/defect_api.js';
import { mapGetters } from 'vuex';
import OrderStatus from '../components/Other/OrderStatus.vue';

import IndexTableMixin from '../utils/indexTableMixin.js';
import DefaultModal from '../components/modals/default_modal.vue';

export default {
    components: { IndexTableMixin, DefaultModal, OrderStatus },
    mixins: [IndexTableMixin],
    data() {
        return {
            product_locations: [
                { id: 0, name: "Нет" },
                { id: 1, name: "В офисе" },
                { id: 2, name: "У курьера" },
                { id: 3, name: "Частично - Офис/Курьер" },
                { id: 4, name: "Частично - Офис/Поставщик" },
                { id: 5, name: "Частично - Поставщик/Курьер" },
                { id: 6, name: "Вернули поставщику" },
                { id: 7, name: "Передан в СЦ" },
                { id: 8, name: "В ТК" },
            ],
            money_refund_statuses: [
                { id: 0, name: "Без возврата средств" },
                { id: 1, name: "Требует возврата средств" },
            ],
            refund_types: [
                { id: 0, name: "Поставщику" },
                { id: 1, name: "На склад" },
                { id: 2, name: "Возврат брак" },
            ],
            replacement_types: [
                { id: 0, name: "С выкупом" },
                { id: 1, name: "Без выкупа" },
                { id: 2, name: "Ремонт" },
            ],
            contractorTypes: [
                { id: 1, name: 'Только основные' },
                { id: 0, name: 'Только дополнительные' }
            ],
            selectExportType: false
        }
    },
    computed: {
        ...mapGetters({ contractors: 'getContractors' }),
        ...mapGetters({ legalEntities: 'getLegalEntities' }),
        ...mapGetters({ paymentMethods: 'getPaymentMethods' }),
        paymentMethodsWithLegalEntities() {
            this.paymentMethods.forEach(method => {
                method.name = method.legal_entity_name + " " + method.name;
            });
            return this.paymentMethods;
        }
    },
    methods: {
        initSettings() {
            this.settings.tableTitle = 'Браки';
            this.settings.localStorageKey = 'defects_params'

            this.settings.withCreateButton = false;
            this.settings.withHeader = false;
            this.settings.withExport = true;
            this.settings.isLoading = true;
            this.settings.saveParams = true;
            this.settings.withBottomBox = false;
            this.settings.withFilterTemplates = true;

            this.settings.indexAPI = params => defectAPI.index(params);

            this.onInitData = res => {
                this.defects = res.data.data;
            }

            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'comment';
                defaultParams.sort_type = this.params.sort_type || 'desc';
            }

            this.onExport = () => this.selectExportType = true;
        },
        toDefect(id) {
            this.$router.push('/defects/' + id + '/edit');
        },
        formatDate(value) {
            if (!value) return '';

            const date = new Date(value);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${day}.${month}.${year}`;
        },
        productLocationName(id) {
            if (id !== null) {
                return this.product_locations.find(item => item.id === Number(id))?.name || '';
            }
            return null
        },
        replacementTypeName(id) {
            if (id !== null) {
                return this.replacement_types.find(item => item.id === Number(id))?.name || '';
            }
            return null
        },
        moneyRefundStatusName(id) {
            if (id !== null) {
                return this.money_refund_statuses.find(item => item.id === Number(id))?.name || '';
            }
            return null
        },
        refundTypeName(id) {
            if (id !== null) {
                return this.refund_types.find(item => item.id === Number(id))?.name || '';
            }
            return null
        },
        downloadFile(response, fileName) {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName + '.xlsx');
            document.body.appendChild(link);
            link.click();
        },
        // async onDefectsExport() {
        //     const response = await defectAPI.defects_export(this.params)
        //     this.downloadFile(response, 'Экспорт браков');
        //     this.showToast('Экспорт завершён', 'Экспорт браков завершён', 'success');
        //     this.selectExportType = false;
        // },
        async onDefectProductsExport() {
            const response = await defectAPI.defect_products_export(this.params)
            this.downloadFile(response, 'Экспорт бракованных товаров');
            this.showToast('Экспорт завершён', 'Экспорт бракованных товаров завершён', 'success');
            this.selectExportType = false;
        },
    },
    mounted() {
        this.$store.dispatch('loadContractorsData');
        this.$store.dispatch('loadLegalEntities');
        this.$store.dispatch('loadPaymentMethods');
    }
}
</script>
