<template>
    <EntityLayout :isLoaded="isLoaded" :loadingCover="loadingCover" :SaveDisabled="complectionDisable"
        :withSaveButton="canUserSave" :withDeleteButton="canUserDelete">

        <template v-slot:header>
            <div class="bg-gradiend p-3 rounded shadow-sm text-white w-100 d-flex flex-row justify-content-between align-items-center"
                :class="inventory.is_completed ? 'bg-success' : 'bg-primary'">
                <div>
                    <div>
                        <h3 class="d-flex flex-row m-0">
                            Инвентаризация №{{ inventory.id }}
                        </h3>
                    </div>
                    <div v-if="inventory.username" class="d-inline me-1">
                        <small>Создал:</small>
                        <strong class="ps-1">{{ inventory.username }}</strong>
                    </div>
                    <div class="d-inline me-1">
                        <small>Создан:</small>
                        <strong class="ps-1">{{ inventory.created_at }}</strong>
                    </div>
                    <div v-if="inventory.updated_by" class="d-inline me-1">
                        <small>Обновил:</small>
                        <strong class="ps-1">{{ inventory.updated_by }}</strong>
                    </div>
                    <div class="d-inline me-1">
                        <small>Обновлён:</small>
                        <strong class="ps-1">{{ inventory.updated_at }}</strong>
                    </div>
                </div>
                <h5 v-if="inventory.is_completed">
                    Инвентаризация завершена
                </h5>
            </div>
        </template>

        <template v-slot:content>
            <Card title="Товары для инвентаризации">
                <template v-slot:top>
                    <button v-if="canUserCorrect" @click="completedInventoryExport()" type="button" style="font-size: 14px"
                        class="ms-3 btn btn-light border border-secondary bg-opacity text-primary">
                        Экспорт
                        <font-awesome-icon icon="fa-solid fa-download" />
                    </button>
                </template>
                <div class="d-flex p-3 w-100 flex-row justify-content-between">
                    <div class="d-flex w-75 justify-content-start align-items-center">
                        <FilterInput v-model="productFilter" type="text" placeholder="Артикул или название" label="Товар"
                            style="width: fit-content; padding-right: 1em">
                        </FilterInput>
                        <FilterMultipleSelect v-model="brandFilter" label="Бренды" placeholder="Выбрать бренды"
                            :options="brands" style="width: fit-content; padding-right: 1em">
                        </FilterMultipleSelect>
                        <div v-if="canUserCorrect" class="d-flex flex-row align-items-center ps-3">
                            <input id="differ" type="checkbox" class="me-1" :disabled="needCorrect" v-model="isDiffer" style="cursor: pointer">
                            <label for="differ" style="cursor: pointer">Есть расхождение</label>
                        </div>
                        <div v-if="canUserCorrect" class="d-flex flex-row align-items-center ps-3">
                            <input id="correction" type="checkbox" class="me-1" v-model="needCorrect"
                                style="cursor: pointer">
                            <label for="correction" style="cursor: pointer">Нужна корректировка</label>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-end" v-if="canUserCorrect">
                        <span class="d-flex align-items-center"> Расхождения в минус: {{ minusOverall.priceFormat(true) }}</span>
                        <span class="d-flex align-items-center"> Расхождения в плюс: {{ plusOverall.priceFormat(true) }}</span>
                        <span class="d-flex align-items-center"> Общее Расхождение: {{ overallDifference.priceFormat(true) }}</span>
                    </div>
                    <div>
                        <button v-if="!inventory.is_completed" @click="inventoryProductsExport()"
                            title="Экспорт для проведения инвентаризации. Содержит данные из этой таблицы и пустую колонку для заполнения остатков. Файл является информативным и его нельзя импортировать обратно с заполненными остатками."
                            type="button" style="font-size: 14px"
                            class="ms-3 btn btn-light border border-secondary bg-opacity text-primary">
                            Экспорт
                            <font-awesome-icon icon="fa-solid fa-download" />
                        </button>

                        <AddButton v-if="canUserAddProduct" @click="addInventoryProduct()" label="Добавить товар">
                        </AddButton>
                    </div>
                </div>

                <MainTable class="border-0 w-100 border-start-0 border-end-0 border-bottom-0" stlye="min-width: 0">
                    <template v-slot:thead>
                        <TableHeader width="20" @click="onSort('sku')" :sort="setSortProp('sku')" class="col-2">
                            Артикул
                        </TableHeader>
                        <TableHeader width="900" @click="onSort('name')" :sort="setSortProp('name')">
                            Название товара
                        </TableHeader>
                        <TableHeader width="100" @click="onSort('brand_name')" :sort="setSortProp('brand_name')">
                            Бренд
                        </TableHeader>
                        <TableHeader width="100" @click="onSort('original_stock')" :sort="setSortProp('original_stock')"
                            v-if="canUserCorrect">
                            Реальный остаток
                        </TableHeader>
                        <TableHeader width="200" @click="onSort('revision_stock')" :sort="setSortProp('revision_stock')">
                            Подсчитанный остаток
                        </TableHeader>
                        <TableHeader width="20" v-if="!inventory.is_completed">

                        </TableHeader>
                        <TableHeader v-if="canUserCorrect" @click="onSort('difference')" :sort="setSortProp('difference')">
                            Расхождение (шт.)
                        </TableHeader>
                        <TableHeader width="20" v-if="canUserCorrect">
                            Корр.
                        </TableHeader>
                    </template>

                    <template v-slot:tbody>
                        <TableRow v-for="product in filteredProducts">
                            <TableCell class="col-2">
                                <inputDefault v-if="product.is_manually_added && !inventory.is_completed"
                                    @blur="checkSku(product)"
                                    :disabled="!canUserEdit || this.loadingCover || product.detected"
                                    class="inventory-rev-input" v-model="product.manual_sku" type="text"></inputDefault>
                                <span v-else>{{ product.is_manually_added ? product.manual_sku : product.sku }}</span>
                            </TableCell>
                            <TableCell>
                                <inputDefault v-if="product.is_manually_added && !inventory.is_completed"
                                    @blur="updateInventoryProduct(product)"
                                    :disabled="!canUserEdit || this.loadingCover || product.detected"
                                    class="inventory-rev-input" v-model="product.manual_name" type="text"></inputDefault>
                                <span v-else>{{ product.is_manually_added ? product.manual_name : product.name }}</span>
                            </TableCell>
                            <TableCell>
                                <select style="height: 60%; width: 100%;" v-if="product.is_manually_added && !inventory.is_completed" class="form-select"
                                    v-model="product.brand_id" @blur="updateInventoryProduct(product)">
                                    <option :value="brand.id" v-for="brand in brands">
                                        {{ brand.name }}
                                    </option>
                                </select>
                                <span v-else>{{ product.brand_id ? product.brand_name : 'Без бренда' }}</span>
                            </TableCell>
                            <TableCell align="center" v-if="canUserCorrect">
                                <span>{{ product.original_stock }}</span>
                            </TableCell>
                            <TableCell>
                                <inputDefault @blur="updateInventoryProduct(product)"
                                    :disabled="!canUserEdit || this.loadingCover" class="inventory-rev-input w-75" :min="0"
                                    v-model="product.revision_stock" type="number">
                                </inputDefault>
                            </TableCell>
                            <TableCell v-if="!inventory.is_completed">
                                <TrashButton v-if="canUserDeleteProducts && product.is_manually_added"
                                    @click="deleteInventoryProduct(product)"></TrashButton>
                            </TableCell>
                            <TableCell v-if="canUserCorrect">
                                <div class="d-flex justify-content-center" v-bind:class="resultColor(product)">
                                    {{ result(product) }}
                                </div>
                            </TableCell>
                            <TableCell v-if="canUserCorrect">
                                <button v-if="absoluteDifference(product) && !product.is_corrected"
                                    @click="showCorrectionModal(product)" class="btn btn-outline-primary border-0">
                                    <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                                </button>
                            </TableCell>
                        </TableRow>
                        <NoEntries v-if="!filteredProducts.length"></NoEntries>
                    </template>

                    <template v-slot:tfoot>
                        Всего товаров показано: {{ filteredProducts.length }}
                    </template>

                </MainTable>
            </Card>

            <DefaultModal @close_modal="completeInventoryModal = false" width="560px" title="Завершение инвентаризации"
                v-if="completeInventoryModal">
                <template v-slot>
                    <div class="p-3">
                        <span>Вы действительно хотите завершить инвентаризацию?<br></span>
                        <span class="text-danger font-weight-bold">После завершения инвентаризации внести изменения будет
                            нельзя!</span>
                        <div class="mt-3">
                            <button @click="completeInventory()" class="btn btn-primary me-2"
                                type="button">Завершить</button>
                            <button @click="completeInventoryModal = false" class="btn btn-light border"
                                type="button">Отмена</button>
                        </div>
                    </div>
                </template>
            </DefaultModal>

            <DefaultModal v-if="correctionModal && !currentCorrectionProduct.is_manually_added"
                @close_modal="closeCorrectionModal()" :title="getCorrectSKU(currentCorrectionProduct)" width="800px">
                <template
                    v-if="stockDifference(currentCorrectionProduct) < 0 && !currentCorrectionProduct.is_manually_added">
                    <div class="p-3">
                        <div>
                            <span>Вы спишите разницу ({{ absoluteDifference(currentCorrectionProduct) }} шт.) со
                                склада.</span>
                            <span class="text-danger"> Изменения отменить не получится!</span>
                        </div>
                        <div>
                            <SelectInput style="font-size: 13px" :options="contractors"
                                v-model="currentCorrectionProduct.contractor_id" class="w-75 ms-0" label="Поставщик"
                                placeholder="Выбрать поставщика для списания (необязательно)"></SelectInput>
                        </div>
                        <div>
                            <button type="button" @click="onConfirmCorrection(currentCorrectionProduct)"
                                class="btn btn-primary">Подтвердить</button>
                            <button type="button" @click="closeCorrectionModal()"
                                class="ms-2 btn btn-light border">Отмена</button>
                        </div>
                    </div>
                </template>
                <template
                    v-if="stockDifference(currentCorrectionProduct) > 0 && !currentCorrectionProduct.is_manually_added">
                    <div class="p-3">
                        <div>
                            <span>Вы занесете подсчитанный остаток ({{ absoluteDifference(currentCorrectionProduct) }} шт.)
                                на склад.</span>
                            <span class="text-danger"> Изменения отменить не получится!</span>
                        </div>
                        <div class="d-flex flex-wrap">
                            <SelectInput style="font-size: 13px" :required="true" label="Поставщик"
                                v-model="currentCorrectionProduct.contractor_id" :options="contractors" class="ms-0"
                                placeholder="Выберите поставщика"></SelectInput>
                            <inputDefault label="Цена" :required="true" v-model="currentCorrectionProduct.price"
                                placeholder="Введите закупочную цену"></inputDefault>
                        </div>
                        <div>
                            <button type="button" @click="onConfirmCorrection(currentCorrectionProduct)"
                                class="btn btn-primary">Подтвердить</button>
                            <button type="button" @click="closeCorrectionModal()"
                                class="ms-2 btn btn-light border">Отмена</button>
                        </div>
                    </div>
                </template>
            </DefaultModal>

            <AddProductModal v-if="correctionModal && currentCorrectionProduct.is_manually_added"
                @close="closeCorrectionModal()" @add="onConfirmCorrection($event)" :product="currentCorrectionProduct">
            </AddProductModal>

            <DestroyConfirmModal v-if="deletingModal" :entityName="'инвентаризацию'" @confirm='deleteInventory()'
                @cancel="deletingModal = false">
            </DestroyConfirmModal>
        </template>

        <template v-slot:save-box>
            <button v-if="canUserSave" type="button" class="btn btn-light me-2 border border-1"
                @click="completeInventoryModal = true" style="margin-left:40px" :disabled="this.loadingCover">
                <font-awesome-icon class="me-2 text-success" icon="fa-solid fa-check" />
                Завершить инвентаризацию
            </button>
            <button v-if="canUserDelete" :disabled="this.loadingCover" type="button" class="btn btn-danger ms-auto"
                style="margin-right:40px" @click="onDelete()">
                Удалить
            </button>
        </template>

    </EntityLayout>
</template>

<style>
.inventory-rev-input input {
    width: 100% !important;
}
</style>

<script>
import { inventoryAPI } from '../api/inventory_api.js';
import { brandsAPI } from "../api/brand_api.js";
import { productAPI } from '../api/products_api';
import { mapGetters } from 'vuex';

import EntityLayout from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';

import MainTable from '../components/Tables/main_table.vue';
import TableHeader from '../components/Tables/th.vue';
import TableRow from '../components/Tables/tr.vue';
import TableCell from '../components/Tables/td.vue';
import NoEntries from '../components/Tables/no_entries.vue';
import AddButton from '../components/inputs/add_button.vue';
import TrashButton from '../components/inputs/trash_button.vue';
import FilterMultipleSelect from "../components/inputs/filter_select_multiple.vue"
import FilterInput from "../components/inputs/filter_input.vue"

import inputDefault from '../components/inputs/default_input.vue';
import SelectInput from '../components/inputs/select_input.vue';

import ProductSelectModal from '../components/modals/product_select_modal.vue';
import DefaultModal from '../components/modals/default_modal.vue';
import AddProductModal from '../components/modals/InventoryAddProductModal.vue'
import DestroyConfirmModal from '../components/modals/DestroyConfirmModal.vue';

export default {
    data() {
        return {
            originalInventory: {},
            inventory: { is_completed: null },
            originalInventoryProducts: [],
            inventoryProducts: [],
            brands: [],
            isDiffer: false,
            needCorrect: false,
            productFilter: '',
            brandFilter: [],
            sortField: 'sku',
            sortType: 'asc',
            addProductModal: false,
            completeInventoryModal: false,
            isLoaded: false,
            complectionDisable: false,
            exitOnSave: false,
            loadingCover: false,
            deletingModal: false,
            correctionModal: false,
            currentCorrectionProduct: null,
        }
    },
    components: {
        inputDefault, MainTable, TableHeader,
        TableRow, TableCell, EntityLayout,
        Card, ProductSelectModal, DefaultModal,
        AddButton, TrashButton, NoEntries,
        DestroyConfirmModal, SelectInput,
        AddProductModal, FilterMultipleSelect,
        FilterInput
    },

    methods: {
        showCorrectionModal(product) {
            this.correctionModal = true;
            this.currentCorrectionProduct = product;
        },
        closeCorrectionModal() {
            this.correctionModal = false;
            this.currentCorrectionProduct = null;
        },

        async loadBrands() {
            const response = await brandsAPI.index();
            this.brands = response.data.data;
            this.brands.unshift({id: null, name: "Без бренда"});
            this.isBrandsLoaded = true;
        },

        async loadInventory() {
            const id = this.$route.params.inventory_id;
            await inventoryAPI.show(id).then((res) => {
                this.initInventory(res);
                this.loadingCover = false;
                this.isLoaded = true;
                this.productFilter = '';
                this.brandFilter = [];
            });
        },

        initInventory(res) {
            this.inventory = res.data.inventory;
            this.originalInventory = JSON.parse(JSON.stringify(this.inventory));
            this.inventoryProducts = res.data.inventoryProducts;
            this.originalInventoryProducts = JSON.parse(JSON.stringify(this.inventoryProducts));
        },

        updateInventoryProduct(product) {
            let changedProduct = {
                id: product.id,
                product_id: product.product_id,
                inventory_id: this.inventory.id,
                revision_stock: (product.revision_stock == '' || product.revision_stock < 0) ? 0 : product.revision_stock,
                is_manually_added: product.is_manually_added,
                manual_name: product.manual_name,
                manual_sku: product.manual_sku,
                brand_id: product.brand_id,
            };

            this.saveInventory({
                inventory_products: [changedProduct]
            });
        },

        async checkSku(product) {
            if (product.sku || product.manual_sku) {
                const isBackSku = await this.backCheckSku(product);
                if (!isBackSku) {
                    this.frontCheckSku(product);
                }
            }
        },

        async backCheckSku(product) {
            const res = await productAPI.checkSku({ sku: product.manual_sku })
            if (res.data) {
                const findedProduct = res.data;

                const originalSearchSku = product.sku || product.manual_sku;
                if (findedProduct) {
                    product.sku = findedProduct.main_sku;
                    product.manual_sku = findedProduct.main_sku;
                }

                const findedInventoryProduct = this.inventoryProducts.filter(product => (product.sku == findedProduct.main_sku) || (product.manual_sku == findedProduct.main_sku));

                if (findedInventoryProduct.length > 1) {
                    this.showToast('Ошибка', 'Товар с артикулом ' + originalSearchSku + ' уже существует в этой инвентаризации под артикулом '
                        + (findedInventoryProduct[0].sku || findedInventoryProduct[0].manual_sku), 'danger');

                    product.sku = null;
                    product.manual_sku = null;
                    product.manual_name = null;
                    product.brand_id = null;
                    return true;
                } else {
                    this.showToast('Товар найден', 'Товар с артиуклом ' + originalSearchSku
                        + ' найден на складе под артикулом ' + findedProduct.main_sku + '. Данные записаны автоматически', 'info')

                    product.manual_name = findedProduct.name;
                    product.brand_id = findedProduct.brand_id;
                    product.manual_sku = findedProduct.main_sku;

                    this.updateInventoryProduct(product);
                    return true;
                }
            } else {
                this.updateInventoryProduct(product);
                return false;
            }
        },

        async frontCheckSku(product) {
            const findedInventoryProduct = this.inventoryProducts.filter(prod => (prod.sku == product.manual_sku) || (prod.manual_sku == product.manual_sku));

            if (findedInventoryProduct.length > 1) {
                this.showToast('Ошибка', 'Товар с артикулом ' + product.manual_sku + ' уже был добавлен в эту инвентаризацию под артикулом '
                    + (findedInventoryProduct[0].sku || findedInventoryProduct[0].manual_sku), 'danger');

                product.sku = null;
                product.manual_sku = null;
                product.manual_name = null;
                product.brand_id = null;

                this.updateInventoryProduct(product);
            }
        },

        addInventoryProduct() {
            let new_product = {
                id: null,
                product_id: null,
                inventory_id: this.inventory.id,
                revision_stock: 0,
                is_manually_added: true,
                manual_name: null,
                manual_sku: null,
            };

            this.inventoryProducts.push(new_product);

            this.saveInventory({
                inventory_products: [new_product]
            });
        },

        deleteInventoryProduct(product) {
            this.inventoryProducts = this.removeItemFromArray(this.inventoryProducts, product)

            this.saveInventory({
                deleted_products_ids: [product.id]
            });
        },

        completeInventory() {
            this.inventory.is_completed = true;
            this.saveInventory({
                complete_status: true
            })
            this.completeInventoryModal = false;
        },


        result(product) {
            let result = this.absoluteDifference(product);
            let difference = this.stockDifference(product);

            if (difference == 0) {
                result = '—'
            }

            return result;
        },
        resultColor(product) {
            let sign = this.stockDifference(product);

            if (sign > 0) {
                return 'text-success';
            }
            if (sign < 0) {
                return 'text-danger';
            }
        },

        stockDifference(product) {
            return product.revision_stock - product.original_stock;
        },
        absoluteDifference(product) {
            let differ = this.stockDifference(product);
            if (differ < 0) {
                differ = -differ;
            }
            return differ;
        },
        onSort(field) {
            if (field == this.sortField) {
                this.sortType = this.sortType == 'desc' ? 'asc' : 'desc';
            } else {
                this.sortField = field;
                this.sortType = 'asc';
            }
        },
        setSortProp(field) {
            let sortProp = {}
            if (field == this.sortField) {
                sortProp.isActive = true;
                sortProp.type = this.sortType;
            } else {
                sortProp.isActive = false;
            }
            return sortProp;
        },

        getCorrectSKU(product) {
            if (product.is_manually_added) {
                return 'Подтвердить корректировку товара арт. ' + product.manual_sku;
            } else {
                return 'Подтвердить корректировку товара арт. ' + product.sku;
            }
        },

        async inventoryProductsExport() {
            let params = {
                id: this.$route.params.inventory_id,
                product: this.productFilter,
                brands: this.brandFilter,
                sort_field: this.sortField,
                sort_type: this.sortType
            }
            const res = await inventoryAPI.export(params);
            this.downloadFile(res, 'Выгрузка товаров инвентаризации');
        },
        async completedInventoryExport() {
            let params = {
                id: this.$route.params.inventory_id,
                product: this.productFilter,
                brands: this.brandFilter,
                sort_field: this.sortField,
                sort_type: this.sortType
            }
            const res = await inventoryAPI.completedExport(params);
            this.downloadFile(res, 'Выгрузка товаров завершенной инвентаризации');
        },

        downloadFile(response, fileName) {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName + '.xlsx');
            document.body.appendChild(link);
            link.click();
        },

        onConfirmCorrection(product) {
            this.sendTransactionRequest(product);
            this.closeCorrectionModal();
        },

        async sendTransactionRequest(product) {
            let difference = this.stockDifference(product);
            let data = {
                id: product.id,
                inventory_id: this.inventory.id,
                product_id: product.product_id,
                name: product.name,
                contractor_id: product.contractor_id ? product.contractor_id : null,
                price: product.price ? product.price : null,
                revision_stock: product.revision_stock,
                original_stock: product.original_stock,
                is_manually_added: product.is_manually_added,
                difference: difference,
            }
            console.log(data);
            await inventoryAPI.correct(data, this.inventory.id).then((res) => {
                if (res.data.message)
                {
                    this.showToast('Ошибка', res.data.message, 'danger')
                } else {
                    this.loadingCover = true;
                    this.loadInventory();
                }
            });
        },

        async saveInventory(data) {
            if (
                ((JSON.stringify(this.inventoryProducts) !== JSON.stringify(this.originalInventoryProducts)) ||
                    (JSON.stringify(this.inventory) !== JSON.stringify(this.originalInventory))) && !this.originalInventory.is_completed
            ) {
                this.loadingCover = true;
                const res = await inventoryAPI.update(this.inventory.id, data);
                if (res.data.message) {
                    this.showToast('Ошибка', res.data.message, 'warning');
                    this.inventory.is_completed = false;
                } else {
                    this.initInventory(res);
                }
                this.loadingCover = false;
            }
        },

        onDelete() {
            this.deletingModal = true;
        },

        async deleteInventory() {
            await inventoryAPI.destroy(this.inventory.id).then(() => {
                this.$router.push('/inventories/');
                this.showToast('Удаление инвентаризации', 'Удаление инвентаризации произведено успешно!', 'success');
            });
        },
        productFilterClosure(product)
        {
            let manual_name = '';
            if (product.manual_name){
                manual_name = product.manual_name.toLowerCase().trim();
            }

            let name = '';
            if (product.name)
            {
                name = product.name.toLowerCase().trim();
            }

            let manual_sku = '';
            if (product.manual_sku)
            {
                manual_sku = product.manual_sku.toLowerCase().trim();
            }

            let sku = '';
            if (product.sku)
            {
                sku = product.sku.toLowerCase().trim();
            }

            let productFilter = this.productFilter.toLowerCase().trim();
            return manual_name.includes(productFilter) || name.includes(productFilter) || manual_sku.includes(productFilter) || sku.includes(productFilter);
        },
        brandFilterClosure(product)
        {
            if(this.brandFilter.length){
                return this.brandFilter.includes(product.brand_id);
            }

            return true;
        },
        differenceFilterClosure(product)
        {
            if (!this.isDiffer)
            {
                return true;
            }
            return (product.revision_stock - product.original_stock != 0);
        },
        needToCorrectFilterClosure(product)
        {
            if (!this.needCorrect)
            {
                return true;
            }
            return !product.is_corrected && (product.revision_stock - product.original_stock != 0);
        }
    },
    mounted() {
        this.loadBrands();
        this.loadInventory();
        this.$store.dispatch('loadContractorsData');
    },
    computed: {
        ...mapGetters({ contractors: 'getContractors' }),
        filteredProducts() {
            let sortField = this.sortField;
            let sortType = this.sortType;

            return this.inventoryProducts
                .filter(this.productFilterClosure)
                .filter(this.brandFilterClosure)
                .filter(this.differenceFilterClosure)
                .filter(this.needToCorrectFilterClosure)
                .sort((a, b) => {
                    let fieldA;
                    let fieldB;
                    if (sortField === 'difference') {
                        fieldA = this.stockDifference(a);
                        fieldB = this.stockDifference(b);
                    } else if (sortField === 'name' && this.inventory.is_completed) {
                        fieldA = a['name'] || a['manual_name'];
                        fieldB = b['name'] || b['manual_name'];
                    } else if (sortField === 'sku' && this.inventory.is_completed) {
                        fieldA = a['sku'] || a['manual_sku'];
                        fieldB = b['sku'] || b['manual_sku'];
                    } else {
                        fieldA = a[sortField];
                        fieldB = b[sortField];
                    }

                    let sortFactor = sortType === 'asc' ? 1 : -1;

                    if (fieldA < fieldB) {
                        return -1 * sortFactor;
                    }
                    if (fieldA > fieldB) {
                        return 1 * sortFactor;
                    }
                    return 0;
                });
        },
        minusOverall() {
            let costDifference = 0;
            this.inventoryProducts.forEach(e => {
                let amountDifference = e.original_stock - e.revision_stock;
                costDifference += (amountDifference > 0 ? amountDifference : 0) * e.max_price;
            })
            return Math.abs(costDifference);
        },
        plusOverall() {
            let costDifference = 0;
            this.inventoryProducts.forEach(e => {
                let amountDifference = e.original_stock - e.revision_stock;
                costDifference += (amountDifference < 0 ? amountDifference : 0) * e.max_price;
            })
            return Math.abs(costDifference);
        },
        overallDifference() {
            let overallCostDifference = this.plusOverall - this.minusOverall;
            return Math.abs(overallCostDifference);
        },

        canUserEdit() {
            return this.checkPermission('inventory_update') && !this.inventory.is_completed && this.isLoaded;
        },
        canUserDelete() {
            return this.checkPermission('inventory_delete') && this.isLoaded;
        },
        canUserAddProduct() {
            return this.canUserEdit;
        },
        canUserDeleteProducts() {
            return this.canUserEdit;
        },
        canUserComplete() {
            return this.canUserEdit;
        },
        canUserSave() {
            return this.canUserEdit;
        },
        canUserCorrect() {
            return this.checkPermission('inventory_correction') && this.inventory.is_completed && this.isLoaded
        }
    }
}
</script>
