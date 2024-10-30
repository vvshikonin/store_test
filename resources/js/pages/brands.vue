<template>
    <MainTable class="brands-table" :tableSettings="tableSettings" :withHeadeSection="checkPermission('brand_create')" :meta="meta" @export="onExport()"
     @add_element="addBrandModal()" @confirm_filter="onConfirmFilter()" @clear_filter="onClearFilter()"
     @current_page="onChangeCurrentPage($event)" @per_page="onChangePerPage($event)" @additional_button_trigger="handleXMLManageModalOpen()">

        <template v-slot:header></template>

        <template v-slot:filters>
            <FilterInput v-model="params.nameFilter" label="Название" placeholder="Введите название"></FilterInput>
        </template>

        <template v-slot:thead>
            <TableHeader @click="onSort('name')" :sort="setSortProp('name')" class="col-6">
                Название
            </TableHeader>
            <TableHeader @click="onSort('marginality')" :sort="setSortProp('marginality')" class="col-2">
                % наценки без подд. остатка
            </TableHeader>
            <TableHeader @click="onSort('maintained_marginality')" :sort="setSortProp('maintained_marginality')" class="col-2">
                % наценки с подд. остатком
            </TableHeader>
            <TableHeader class="col-2">
                ICML-каталог
            </TableHeader>
            <TableHeader @click="onSort('updated_at')" :sort="setSortProp('updated_at')" class="col-2">
                Дата изменения
            </TableHeader>
            <TableHeader width="50px">
                <!-- пустой хэдер для кнопки удалить-->
            </TableHeader>
        </template>

        <template v-slot:tbody>
            <TableRow v-for="brand in brands">
                <TableCell class="col-6">
                    <EditInput v-if="checkPermission('brand_update')" @update:content="setNewBrandName(brand, $event)" v-model:content="brand.name"></EditInput>
                    <span v-else>{{ brand.name }}</span>
                </TableCell>
                <TableCell class="col-2">
                    <div class="d-flex">
                        <EditInput v-if="checkPermission('brand_update')" :type="'number'" :min="0" :max="100" :step="1"  @update:content="setNewBrandMarginality(brand, $event)" v-model:content="brand.marginality"></EditInput>
                        <span v-else>{{ brand.marginality }}%</span>
                    </div>
                </TableCell>
                <TableCell class="col-2">
                    <div class="d-flex">
                        <EditInput v-if="checkPermission('brand_update')" :type="'number'" :min="0" :max="100" :step="1"  @update:content="setNewMaintainedMarginality(brand, $event)" v-model:content="brand.maintained_marginality"></EditInput>
                        <span v-else>{{ brand.maintained_marginality }}%</span>
                    </div>
                </TableCell>
                <TableCell class="col-2">
                    <a :href="brand.xml_link"> {{ brand.xml_link ? 'XML-файл' : '-'}} </a>
                </TableCell>
                <TableCell class="col-2">
                    {{ brand.updated_at }}
                </TableCell>
                <TableCell class="col-2">
                    <TrashButton v-if="checkPermission('brand_delete')"  @on_click="deleteBrandConfirmation(brand)"></TrashButton>
                </TableCell>
            </TableRow>
        </template>

        <template v-slot:tfoot>
            <span class="pe-5">Всего брендов: {{ meta.total }} </span>

            <ModalWindow v-if="isAddingBrand" @close_modal="closeModal()" width="600px" title="Добавить новый бренд">
                <template v-slot>
                    <div class="d-flex p-3 flex-column justify-content-between">
                        <div>Введите название бренда</div>
                        <div><input class="form-control" v-model="newBrandName" /></div>
                        <div>Введите процент наценки бренда</div>
                        <div><input class="form-control" v-model="newBrandMarginality" type="number" step="1" min="0" max="100" /></div>
                        <div>
                            <button type="button" class="btn btn-primary mt-2" @click="createBrand()">Добавить</button>
                            <button type="button" class="btn-light btn border bg-gradient border-1 ms-2 mt-2" @click="closeModal()">Отмена</button>
                        </div>
                    </div>
                </template>
            </ModalWindow>

            <ModalWindow v-if="isDeletingBrand" @close_modal="closeModal()" width="600px" title="Удалить бренд">
                <template v-slot>
                    <div class="d-flex p-3 flex-column justify-content-between">
                        <div>Вы действительно хотите удалить бренд {{ deletingBrand.name }}?</div>
                        <div>
                            <button type="button" class="btn btn-danger mt-2" @click="deleteBrand(deletingBrand)">Удалить</button>
                            <button type="button" class="btn-light btn border bg-gradient border-1 ms-2 mt-2" @click="closeModal()">Отмена</button>
                        </div>
                    </div>
                </template>
            </ModalWindow>

            <XMLManageModal v-if="isXMLManageModalOpen"
                @close_modal="isXMLManageModalOpen = false"
                :brands="allBrands" title="Управление XML-каталогами">
            </XMLManageModal>
        </template>

    </MainTable>
</template>

<style>
    .brands-table tbody .edit-input-field * {
        font-size: 13px;
    }
</style>

<script>
import { brandsAPI } from '../api/brand_api';

import MainTable from '../components/Tables/main_table.vue';
import TableHeader from '../components/Tables/th.vue';
import TableRow from '../components/Tables/tr.vue';
import TableCell from '../components/Tables/td.vue';
import FilterInput from '../components/inputs/filter_input.vue';
import EditInput from '../components/inputs/table_edit_input.vue';
import DefaultInput from '../components/inputs/default_input.vue';
import ModalWindow from '../components/modals/default_modal.vue';
import XMLManageModal from '../components/modals/XMLManageModal.vue';
import TrashButton from '../components/inputs/trash_button.vue';

export default {
    components:{ MainTable, TableHeader, TableRow, TableCell, FilterInput, EditInput, ModalWindow, DefaultInput, TrashButton, XMLManageModal },
    data() {
        return {
            brands:[],
            allBrands: [],
            meta: [],
            params: {
                page: 1,
                perPage: 25,
                nameFilter: null,
                sortField: 'name',
                sortType: 'asc',
            },
            tableSettings: {
                isLoading: true,
                tableTitle: 'Бренды',
                withExport: true,
                withFilters: true,
                withAddButton: true,
                withFooter: true,
                isCover: false,
                withAdditionalButton: true,
                additionalButtonTitle: 'Управление XML'
            },
            newBrandName: null,
            newBrandMarginality: null,
            isAddingBrand: false,
            isDeletingBrand: false,
            isXMLManageModalOpen: false,
            deletingBrand: {},
        }
    },
    methods: {
        onChangeCurrentPage(page) {
            this.params.page = page;
            this.tableSettings.isCover = true;
            this.loadBrands();
        },
        onChangePerPage(perPage) {
            this.params.perPage = perPage;
            this.tableSettings.isCover = true;
            this.loadBrands();
        },
        onConfirmFilter() {
            this.tableSettings.isCover = true;
            this.loadBrands();
        },
        onClearFilter() {
            this.params.nameFilter = null;
            this.tableSettings.isCover = true;
            this.loadBrands();
        },
        onSort: function(field){
            this.tableSettings.isCover = true;
            if(field == this.params.sortField){
                this.params.sortType = this.params.sortType == 'desc' ? 'asc' : 'desc';
            }else{
                this.params.sortField = field;
                this.params.sortType = 'asc';
            }
            this.loadBrands();
        },
        setSortProp: function(field){
            let sortProp = {}
            if(field == this.params.sortField){
                sortProp.isActive = true;
                sortProp.type = this.params.sortType;
            }else{
                sortProp.isActive = false;
            }
            return sortProp;
        },
        setNewBrandName(brand, data) {
            brand.name = data;
            this.updateBrand(brand);
        },
        setNewBrandMarginality(brand, data) {
            brand.marginality = data;
            this.updateBrand(brand);
        },
        setNewMaintainedMarginality(brand, data) {
            brand.maintained_marginality = data;
            this.updateBrand(brand);
        },
        updateBrand(brand) {
            this.tableSettings.isLoading = true;
            brandsAPI.update(brand).then(() => {
                this.showToast('Обновление бренда', 'Бренд успешно обновлен!', 'success');
                this.loadBrands();
            });
        },
        addBrandModal() {
            this.isAddingBrand = true;
        },
        closeModal() {
            this.isAddingBrand = false;
            this.isDeletingBrand = false;
            this.newBrandName = null;
        },
        createBrand() {
            this.tableSettings.isLoading = true;
            let newBrand = {};
            newBrand.name = this.newBrandName;
            newBrand.marginality = this.newBrandMarginality;
            brandsAPI.store(newBrand).then(() => {
                this.showToast('Создание бренда', 'Бренд успешно создан!', 'success');
                this.closeModal();
                this.loadBrands();
            });
        },
        deleteBrandConfirmation(brand) {
            this.isDeletingBrand = true;
            this.deletingBrand.name = brand.name;
            this.deletingBrand.id = brand.id;
        },
        deleteBrand(brand) {
            this.tableSettings.isLoading = true;
            brandsAPI.destroy(brand).then(() => {
                this.showToast('Удаление бренда', 'Бренд успешно удален!', 'success');
                this.closeModal();
                this.loadBrands();
            });
        },
        async loadBrands() {
            await brandsAPI.index().then(res => {
                this.allBrands = res.data.data;
            })
            await brandsAPI.index(this.params).then(res => {
                this.brands = res.data.data;
                this.meta = res.data.meta;
                this.tableSettings.isLoading = false;
                this.tableSettings.isCover = false;
            });
        },
        onExport() {
            this.showToast('Экспорт', 'Экспорт таблицы Бренды начат', 'info');

            brandsAPI.export(this.params).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'Выгрузка Бренды.xlsx');
                document.body.appendChild(link);
                link.click();

                this.showToast('Экспорт завершен', 'Экспорт таблицы Поставщики завершен', 'success');
            });
        },
        handleXMLManageModalOpen() {
            if (this.checkPermission('product_xml_manage')) {
                this.isXMLManageModalOpen = true;
            } else {
                this.showToast('Ошибка 403', 'У вас нет прав на управление XML-каталогами!', 'danger');
            }
        }
    },
    mounted() {
        this.loadBrands();
    }
}
</script>
