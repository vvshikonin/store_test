<template>
    <MainTable :tableSettings="tableSettings" :withHeadeSection="checkPermission('price_monitoring_create')" @add_element="isShowProductModal = true"
        @confirm_filter="confirmFilers()" @clear_filter="clearFilers()">
        <template v-slot:filters>
            <FilterInput v-model="params.product_filter" label="Товар" placeholder="Артикул или название"></FilterInput>
            <FilterSelect v-model="params.delta_filter" label="Дельта статус" :options="[{id: 1, name: 'Да'}]"></FilterSelect>
        </template>
        <template v-slot:info>
            <ProductSelectModal v-if="isShowProductModal" @close_modal="isShowProductModal = false"
                @select_product="store($event)">
            </ProductSelectModal>
            <DefaultModal v-if="isShowEditModal" @close_modal="isShowEditModal = false" width="800px"
                title="Редактирование мониторинга цены" >
                <div class="d-flex flex-column w-100 pt-3 pb-3" style="overflow-x: hidden;">
                    <DefaultInput v-model="editingMonitoringProduct.url" class="w-75" label="URL" placeholder="Введите URL">
                    </DefaultInput>
                    <DefaultInput v-model="editingMonitoringProduct.xpath" class="w-75"  label="XPath" placeholder="Введите XPath" >
                    </DefaultInput>
                </div>
                <div class="bg-light border-top p-3">
                    <button @click="update()" type="button" class="btn btn-sm btn-primary">Сохранить</button>
                </div>
            </DefaultModal>
        </template>
        <template v-slot:thead>
            <TableHeader width="100">Артикул</TableHeader>
            <TableHeader width="250">Название </TableHeader>
            <TableHeader align="center" width="100">РРЦ</TableHeader>
            <TableHeader align="center" width="150">Парсинг</TableHeader>
            <TableHeader align="center" width="100">Дельта</TableHeader>
            <TableHeader>URL</TableHeader>
            <TableHeader width="50"></TableHeader>
        </template>
        <template v-slot:tbody>
            <TableRow v-for="monitoring in priceMonitorings">
                <TableCell class="fw-bolder">
                    {{ monitoring.main_sku }}
                </TableCell>
                <TableCell>
                    <a :href="product_url(monitoring)" target="_blank" title="Открыть товар в новой вкладке">{{ monitoring.name }}</a>
                </TableCell>
                <TableCell align="center">{{ monitoring.rrp ? monitoring.rrp.priceFormat(true) : '-' }}</TableCell>
                <TableCell align="center" class="bg-light border-1 border-start border-end">{{ monitoring.parsed_price ? monitoring.parsed_price.priceFormat(true) : '-' }}</TableCell>
                <TableCell align="center" class="fw-bold" :class="{'text-success': parseInt(delta(monitoring)) >= 10}">{{ delta(monitoring) }}</TableCell>
                <TableCell>
                    <div class="text-truncate" :title="monitoring.url" style="max-width: 325px;">
                        <a :href="monitoring.url">{{ monitoring.url }}</a>
                    </div>
                </TableCell>
                <TableCell>
                    <div class="d-flex justify-content-between" style="width:100px">
                        <button v-if="checkPermission('price_monitoring_update')"  @click="editMonitoringProduct(monitoring)"
                            class="btn btn-outline-primary bg-gradient border-0">
                            <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                        </button>
                        <TrashButton v-if="checkPermission('price_monitoring_delete')"  @click="destroy(monitoring)"></TrashButton>
                    </div>
                </TableCell>
            </TableRow>
        </template>
    </MainTable>
</template>
<script>
import { priceMonitoringAPI } from '../api/price_monitoring_api'


import MainTable from '../components/Tables/main_table.vue';
import TableHeader from '../components/Tables/th.vue';
import TableRow from '../components/Tables/tr.vue';
import TableCell from '../components/Tables/td.vue';
import TrashButton from '../components/inputs/trash_button.vue';

import ProductSelectModal from '../components/modals/product_select_modal.vue';
import DefaultModal from '../components/modals/default_modal.vue';

import FilterSelect from '../components/inputs/FilterSelect.vue';
import FilterInput from '../components/inputs/filter_input.vue';
import DefaultInput from '../components/inputs/default_input.vue';
export default {
    components: { MainTable, TableHeader, TableRow, TableCell, TrashButton, ProductSelectModal, DefaultModal, DefaultInput, FilterInput, FilterSelect },
    data() {
        return {
            tableSettings: {
                isLoading: true,
                tableTitle: 'Мониторинг цен',
                withExport: false,
                withFilters: true,
                withAddButton: true,
                withFooter: true,
                isCover: false,
                isNoEntries: false,
            },
            isShowProductModal: false,
            isShowEditModal: false,
            priceMonitorings: [],
            editingMonitoringProduct: {},
            params: {}
        }
    },
    methods: {
        async index() {
            const res = await priceMonitoringAPI.index(this.params);
            this.initData(res);
            this.tableSettings.isLoading = false;
        },
        async store(product) {
            this.isShowProductModal = false;
            const new_monitoring = {
                product_id: product.id,
                url: null,
                xpath: null,
            }
            const res = await priceMonitoringAPI.store(new_monitoring);
            this.priceMonitorings = this.priceMonitorings.concat([res.data.data])
        },
        async update() {
            this.isShowEditModal = false;
            const id = this.editingMonitoringProduct.id;
            const data = {
                product_id: this.editingMonitoringProduct.product_id,
                url: this.editingMonitoringProduct.url,
                xpath: this.editingMonitoringProduct.xpath,
            }
            const res = await priceMonitoringAPI.update(id, data);

            this.editingMonitoringProduct.xpath = res.data.data.xpath;
            this.editingMonitoringProduct.url = res.data.data.url;
            this.editingMonitoringProduct.parsed_price = res.data.data.parsed_price;
        },
        async destroy(monitoringProduct) {
            await priceMonitoringAPI.destroy(monitoringProduct.id);
            this.removeItemFromArray(this.priceMonitorings, monitoringProduct);
        },

        async confirmFilers() {
            this.tableSettings.isCover = true;
            await this.index();
            this.tableSettings.isCover = false;
        },

        async clearFilers() {
            this.tableSettings.isCover = true;
            this.initParamsDefault();
            await this.index();
            this.tableSettings.isCover = false;
        },

        initParamsDefault() {
            this.params = {
                delta_filter: null
            };
        },

        initData(res) {
            this.priceMonitorings = res.data.data;
            this.tableSettings.isNoEntries = this.priceMonitorings.length ? false : true;

        },

        delta(monitoringProduct){
            let rrp = monitoringProduct.rrp ;
            let parsed_price = monitoringProduct.parsed_price;
            if(rrp && parsed_price){
                return (((rrp - parsed_price)/parsed_price) * 100).toFixed() +"%";
            }
            return "-";
        },

        editMonitoringProduct(monitoringProduct) {
            this.isShowEditModal = true;
            this.editingMonitoringProduct = monitoringProduct;
        },

        product_url(product) {
            let base_url = window.location.origin;
            return base_url + "/#/products/" + product.product_id + "/edit"
        }
    },
    mounted() {
        this.initParamsDefault();
        this.index();
    }
}
</script>