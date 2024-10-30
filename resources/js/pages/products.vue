<template>
    <Table>
        <template v-slot:header></template>
        <template v-slot:filters>
            <FilterInput v-model="params.product_filter" type="text" placeholder="Артикул или название" label="Товар">
            </FilterInput>
            <FilterMultipleSelect v-model="params.contractors_filter" label="Поставщики" placeholder="Выбрать поставщика"
                :options="contractors">
            </FilterMultipleSelect>
            <FilterMultipleSelect v-model="params.brand_filter" label="Бренды" placeholder="Выбрать бренд"
                :options="brands">
            </FilterMultipleSelect>
            <FilterInputBetween v-model:start="params.price_from_filter" v-model:end="params.price_to_filter"
                v-model:equal="params.price_equal_filter" v-model:notEqual="params.price_notEqual_filter"
                label="Закупочная цена" type="number" step="0.01">
            </FilterInputBetween>
            <FilterInputBetween v-model:start="params.avg_price_from_filter" v-model:end="params.avg_price_to_filter"
                v-model:equal="params.avg_price_equal_filter" v-model:notEqual="params.avg_price_notEqual_filter"
                label="Ср. взвеш. цена" type="number" step="0.01">
            </FilterInputBetween>
            <FilterInputBetween v-model:start="params.stock_count_from_filter" v-model:end="params.stock_count_to_filter"
                v-model:equal="params.stock_count_equal_filter" v-model:notEqual="params.stock_count_notEqual_filter"
                label="Общий остаток" type="number">
            </FilterInputBetween>
            <div class="mb-2 filter" style="width: 16.6%;">
                <label class="form-label text-muted mb-0 ps-1" style="font-size: 13px;">Отметки</label>
                <div class="d-flex flex-row" style="width: 230px;">
                    <MarkIcon v-model="params.has_sale" icon="fa-solid fa-tag" title="Показывать товары с распродажей">
                    </MarkIcon>
                    <MarkIcon v-model="params.has_expected" icon="fa-solid fa-clipboard"
                        title="Показывать товары, где есть ожидается (Неоприходованные товары по счетам)"></MarkIcon>
                    <MarkIcon v-model="params.has_warning" icon="fa-solid fa-triangle-exclamation"
                        active-class="text-danger"
                        title="Показывать товары, где требуется закуп. ( Резерв > (Реальный остаток + Ожидается) ИЛИ Поддерживаемый остаток > (Реальный остаток + Ожидается) )">
                    </MarkIcon>
                    <MarkIcon v-model="params.maintained_balance_state" icon="fa-solid fa-arrows-spin"
                        title="Показывать товары, где есть поддерживаемый остаток" :isTriState="true"></MarkIcon>
                    <MarkIcon v-model="params.has_orders" icon="fa-solid fa-cart-shopping"
                        title="Показывать товары, где есть заказы"></MarkIcon>
                    <MarkIcon v-model="params.has_real_stock" icon="fa-solid fa-boxes-stacked"
                        title="Показывать товары, где есть реальный остаток"></MarkIcon>
                    <MarkIcon v-model="params.has_free_balance" icon="fa-solid fa-box"
                        title="Показывать товары, где есть свободный остаток (Реальный остаток - Резерв)."></MarkIcon>
                    <MarkIcon v-model="params.is_profitable_purchase" icon="fa-solid fa-money-bill"
                        title="Показывать товары, которые выгодно купили"></MarkIcon>
                </div>
            </div>
            <FilterInput v-model="params.comment_filter" type="text" label="Комментарий"></FilterInput>
            <FilterInput v-model="params.order_filter" type="text" placeholder="Номер заказа или CRM ID" label="Заказ CRM">
            </FilterInput>
            <FilterInputBetween v-model:start="params.delivery_date_start_filter"
                v-model:end="params.delivery_date_end_filter" v-model:equal="params.delivery_date_equal_filter"
                v-model:notEqual="params.delivery_date_notEqual_filter" label="План. дата доставки" type="date">
            </FilterInputBetween>
            <FilterInputBetween v-model:start="params.updated_at_start_filter" v-model:end="params.updated_at_end_filter"
                v-model:equal="params.updated_at_equal_filter" v-model:notEqual="params.updated_at_notEqual_filter"
                label="Обновлено" type="date">
            </FilterInputBetween>
            <FilterSelect v-model="params.payment_status" :options="paymentStatuses" placeholder="Выбрать статус оплаты"
                label="Статус оплаты"></FilterSelect>
        </template>
        <template v-slot:info>
            <div class="d-flex justify-content-center w-100 border-top bg-light p-2" style="font-size: 14px;">
                <span class="ps-2 pe-2">
                    <font-awesome-icon class="text-primary" icon="fa-solid fa-boxes-stacked" />
                    реальный остаток
                </span>
                <span class="ps-2 pe-2">
                    <font-awesome-icon class="text-primary" icon="fa-solid fa-box" />
                    свободный остаток
                </span>
                <span class="ps-2 pe-2">
                    <font-awesome-icon class="text-primary" icon="fa-solid fa-arrows-spin" />
                    поддерживаемый остаток на 10 дней
                </span>
                <span class="ps-2 pe-2">
                    <font-awesome-icon class="text-danger" icon="fa-solid fa-triangle-exclamation" />
                    недостаточно остатка
                </span>
            </div>
        </template>
        <template v-slot:thead>
            <TH width="50px"></TH>
            <TH field="main_sku" width="80px">Артикул</TH>
            <TH field="contractors_name" width="130px">Поставщик</TH>
            <TH field="avg_price" align="center" title="Сумм(Цена * Кол-во) / Сумм(Кол-во)" width="130px">Ср. взв.
                цена</TH>
            <TH field="name" width="250px">Название</TH>
            <TH field="amount"
                title="Реальный, свободный и поддерживаемый остаток на складе. (Свободный остаток = Реальный остаток - Резерв)."
                width="165px" align="">Остаток</TH>
            <TH field="received" title="Количество товара, которое ожидается по счетам." align="center" width="110px">
                Ожидается</TH>
            <TH field="reserved" title="Количество товаров зарезервированных под заказы." align="center" width="85px">Резерв
            </TH>
            <TH width="124px">
                <font-awesome-icon icon="fa-solid fa-cart-shopping" size="lg" class="text-primary pe-1" />
                Заказы
            </TH>
            <TH field="saled" align="center" width="90px">Продано</TH>
            <TH field="updated_at" width="105px">Обновлено</TH>
            <TH align="center" width="115px">
                <font-awesome-icon icon="fa-solid fa-tag" size="lg" class="text-primary pe-1" />
                Распродажа
            </TH>
        </template>

        <template v-slot:tbody>
            <TR v-for="product in products" :withInnerTable="true" :key="product">
                <template v-slot:default>
                    <TD fw="bold">
                        <a @click.stop
                            v-bind="checkPermission('product_show') ? { href: '/#/products/' + product.id + '/edit' } : {}">{{
                                product.main_sku }}</a>
                    </TD>
                    <TD><span v-for="contractor in product.contractor_names"> {{ contractor }} </span></TD>
                    <TD :currency="true" fw="bold" align="center">{{ product.avg_price }}</TD>
                    <TD>{{ product.name }}</TD>
                    <TD>
                        <span class="real-balance">
                            {{ product.real_stock }}
                            <font-awesome-icon icon="fa-solid fa-boxes-stacked" size="sm"
                                :class="{ 'text-muted': product.real_stock == 0 }" title="Реальный остаток" />
                        </span>
                        <span v-if="(product.real_stock - product.reserved > 0) && product.orderPositions.length"
                            class="free-balance">
                            <span class="text-primary p-1"></span>
                            {{ product.real_stock - product.reserved }}
                            <font-awesome-icon icon="fa-solid fa-box" size="sm" class="text-primary"
                                title="Свободный остаток" />
                        </span>
                        <span v-if="product.maintained_balance > 0" class="free-maintained-balance">
                            <span class="text-primary p-1"></span>
                            {{ product.maintained_balance }}
                            <font-awesome-icon icon="fa-solid fa-arrows-spin" size="sm" class="text-primary"
                                title="Поддерживаемый остаток на 10 дней" />
                            <font-awesome-icon v-if="(product.real_stock + product.expected) < product.maintained_balance"
                                icon="fa-solid fa-triangle-exclamation" size="sm" class="text-danger"
                                title="Остатка меньше, чем должен поддерживатся" />
                        </span>
                    </TD>
                    <TD align="center">{{ product.expected }}</TD>
                    <TD align="center">
                        {{ product.reserved }}
                        <font-awesome-icon v-if="(product.real_stock + parseInt(product.expected) - product.reserved) < 0"
                            icon="fa-solid fa-triangle-exclamation" size="sm" class="text-danger"
                            title="Остатка меньше, чем зарезервированно под заказ." />
                    </TD>
                    <TD> <OrderPopup :content="product.orderPositions"></OrderPopup> </TD>
                    <TD align="center"> {{ product.saled }} </TD>
                    <TD> {{ product.updated_at }} </TD>
                    <TD align="center"> {{ saleTypeCellPrint(product) }} </TD>
                </template>

                <template v-slot:sub-thead>
                    <TH width="131px">Поставщик</TH>
                    <TH align="center" width="130px">Цена</TH>
                    <TH align="center" width="130px">Выгодно купили</TH>
                    <TH title="Реальный остаток на складе." align="center" width="80px">Остаток</TH>
                    <TH title="Количество товара, которое ожидается по счетам." align="center" width="100px">Ожидается</TH>
                    <TH align="center" width="90">Продано</TH>
                    <TH title="Ближайшая планируемая дата доставки." width="105px">Планируемая дата доставки</TH>
                    <TH width="210px">Комментарий</TH>
                </template>

                <template v-slot:sub-tbody>
            <tr v-for="store_position in product.store_positions" :key="store_position"
                style="vertical-align: middle; border-style: none!important;">
                <TD> {{ store_position.contractor_name }} </TD>
                <TD :currency="true" fw="bold" align="center"> {{ store_position.price }} </TD>
                <TD align="center"> 
                    <input :disabled="!checkPermission('product_is_profitable_purchase_update')" type="checkbox" 
                        v-model="store_position.is_profitable_purchase" @change="onChangeStock(store_position)">
                </TD>
                <TD align="center"> {{ store_position.real_stock }} </TD>
                <TD align="center"> {{ store_position.expected }} </TD>
                <TD align="center"> {{ store_position.saled_amount }} </TD>
                <TD> {{ store_position.planed_delivery_date }} </TD>
                <TD>
                    <TextAreaPopup width="350px" v-model="store_position.user_comment" :disabled="!checkPermission('product_update')"
                        @change="onChangeStock(store_position)" placeholder="Добавить комментарий">
                    </TextAreaPopup>
                </TD>
            </tr>
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
                    <button class="btn bg-gradient btn-outline-primary m-1" @click="onProductsExport()"
                        title="Выгрузить все товары, удовлетворяющие фильтру. Без указания цен и точного кол-ва от поставщиков.">Выгрузить
                        список товаров</button>
                    <button class="btn bg-gradient btn-outline-primary m-1" @click="onStorePositionsExport()"
                        title="Выгрузить все товарные позиции, удовлетворяющие фильтру. С указанием цен и точным кол-вом от поставщиков.">Выгрузить
                        список товарных позиций</button>
                    <button class="btn bg-gradient btn-light border m-1" @click="selectExportType = false">Отмена</button>
                </div>
            </DefaultModal>
            <template v-if="meta.total">
                <span v-if="meta.total">Кол-во товаров: {{ meta.total }}</span>
                <span class="ps-5" v-if="total_stocks">Кол-во остатков: {{ total_stocks }}</span>
                <span class="ps-5" v-if="total_free_stocks">Кол-во свободных остатков: {{ total_free_stocks }}</span>
                <span class="ps-5" v-if="maintained_balance_amount">Должно поддерживаться остатка: {{ maintained_balance_amount }}</span>
                <span class="ps-5" v-if="total_reserved_sum">Сумма по резервам: ~{{ total_reserved_sum }} ₽</span>
                <span class="ps-5" v-if="total_sum">Сумма по остаткам: {{ total_sum }} ₽</span>
                <span class="ps-5" v-if="total_profitable_sum">Выгодно купили на сумму: {{ total_profitable_sum }} ₽</span>
                <!-- <span class="ps-5" v-if="total_free_sum">Сумма по свободным остаткам: {{ total_free_sum }} ₽</span> -->
            </template>
        </template>
    </Table>
</template>
<style>
.real-balance svg {
    color: #0d6efd;
}

.free-balance svg,
.free-maintained-balance svg,
.real-balance svg {
    margin-top: 3px;
    margin-left: 3px;
    margin-right: 3px;
}
</style>
<script>
import { mapGetters } from 'vuex';
import { productAPI } from '../api/products_api';
import { brandsAPI } from "../api/brand_api.js";
import { storePositionAPI } from '../api/store_positions_api';

import CRMLogo from '../components/Other/CRM_logo.vue';
import OrderPopup from '../components/popups/orders_table_popup.vue';
import TextAreaPopup from '../components/popups/table_textarea_popup.vue';
import DefaultModal from '../components/modals/default_modal.vue';

import TableCheckbox from '../components/inputs/table_checkbox.vue'
import MarkIcon from '../components/inputs/MarkIcon.vue';

import indexTableMixin from '../utils/indexTableMixin';

export default {
    data() {
        return {
            products: [],
            brands: [],

            total_sum: null,
            total_free_sum: null,
            total_stocks: null,
            maintained_balance_amount: null,
            total_profitable_sum: null,
            total_free_stocks: null,
            selectExportType: false,

            paymentStatuses: [
                { id: 0, name: 'Не оплачен' },
                { id: 1, name: 'Оплачен' },
                { id: 2, name: 'Оплачен и подтверждён' },
            ],
        }
    },
    mixins: [indexTableMixin],
    components: { MarkIcon, DefaultModal, CRMLogo, OrderPopup, TableCheckbox, TextAreaPopup },
    methods: {
        initSettings() {
            this.settings.tableTitle = 'Склад';
            this.settings.createButtonText = 'Создать товар';
            this.settings.localStorageKey = 'products_params'

            this.settings.withCreateButton = this.checkPermission('product_create');
            this.settings.withHeader = false;
            this.settings.withInfo = true;
            this.settings.isLoading = true;
            this.settings.saveParams = true;
            this.settings.withFilterTemplates = true;

            this.settings.indexAPI = params => productAPI.index(params);

            this.onInitData = res => {
                this.products = res.data.data;
                this.total_sum = res.data.totalSum;
                this.total_reserved_sum = res.data.totalReservedSum;
                this.total_stocks = res.data.totalStocks;
                this.total_free_stocks = res.data.totalFreeStocks;
                this.maintained_balance_amount = res.data.totalMaintainedBalance;
                this.total_profitable_sum = res.data.totalProfitableSum;
            }

            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'main_sku';
                defaultParams.sort_type = this.params.sort_type || 'asc';
            }

            this.onClickCreateButton = () => this.$router.push('/products/new');

            this.onClickAdditionalHeaderButton = () => this.handleXMLManageModal();

            this.onExport = () => this.selectExportType = true;
        },

        async loadBrands() {
            const response = await brandsAPI.index();
            this.brands = response.data.data;
            this.isBrandsLoaded = true;
        },

        async onChangeProductSale(product) {
            await productAPI.update({ product });
        },

        async onChangeStock(stock) {
            const data = await storePositionAPI.update(stock);
        },

        async onProductsExport() {
            const response = await productAPI.products_export(this.params)
            this.downloadFile(response, 'Экспорт товаров');
            this.showToast('Экспорт завершён', 'Экспорт товаров завершён', 'success');
            this.selectExportType = false;
        },

        async onStorePositionsExport() {
            const response = await productAPI.store_positions_export(this.params)
            this.downloadFile(response, 'Экспорт товарных позиций');
            this.showToast('Экспорт завершён', 'Экспорт товарных позиций завершён', 'success');
            this.selectExportType = false;
        },

        downloadFile(response, fileName) {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName + '.xlsx');
            document.body.appendChild(link);
            link.click();
        },

        showProduct: function (id) {
            this.$router.push('/products/' + id + '/edit');
        },
        saleTypeCellPrint(product) {
            if (product.sale_type == 'auto' && product.is_sale) {
                return 'Авто';
            } else if (product.sale_type == 'multiplier' && product.is_sale) {
                return 'x' + product.sale_multiplier;
            } else {
                return '-';
            }
        }
    },
    computed: {
        ...mapGetters({ contractors: 'getContractors' }),
        canUserChangeIsProfitablePurchase() {
            return this.checkPermission('product_is_profitable_purchase_update') && this.isEditing;
        }
    },
    mounted() {
        this.loadBrands();
        this.$store.dispatch('loadContractorsData');
    }
}
</script>
