<template>
    <EntityLayout :loadingCover="isCover" :isLoaded="isLoaded" :entityName="'брак ' + defect.order_number" :withSaveButton="true"
        :withDeleteButton="false" @save="onSave()" @exit="onExit()">
        <template v-slot:header>
            <div class="d-flex justify-content-between w-100">
                <div class="d-flex flex-column bg-primary w-100 p-3 rounded border text-white bg-gradient shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3>Брак {{ defect.order_number }}</h3>
                        <div>
                            <button v-if="JSON.parse(defect.files)" @click="filesListModal = true" type="button" class="btn btn-light me-2">Прикрепленные файлы</button>
                            <button type="button" @click="uploadFilesModal = true" class="btn btn-success me-2">Добавить файлы</button>
                        </div>
                    </div>
                    <div id="header-info">
                        <span v-if="defect.creator">
                            Создал:
                            <strong>{{ defect.creator.name }}</strong>
                        </span>
                        <span v-if="defect.updater">
                            Изменил:
                            <strong>{{ defect.updater.name }}</strong>
                        </span>
                        <span v-if="defect.updated_at">
                            Дата изменения:
                            <strong>{{ formatDate(defect.updated_at, 'DD.MM.YYYY HH:mm:ss')}}</strong>
                        </span>
                    </div>
                </div>
            </div>
        </template>
        <template v-slot:content>
            <Card title="Сведения о браке">
                <inputDefault label="Комментарий" placeholder="Введите комментарий" v-model="defect.comment"
                    :required="false" />
                <Selector label="Возврат средств" :disabled="moneyRefund" :options="money_refund_statuses"
                    v-model="defect.money_refund_status" :placeholder="'Статус не выбран'" />
                <Selector label="Где товар" :options="product_locations" crmSync v-model="defect.product_location"
                    :placeholder="'Укажите нахождение товара'" />
                <Selector label="Тип возврата" :options="refund_types" crmSync v-model="defect.refund_type"
                    :placeholder="'Укажите тип возврата'" />
                <Selector label="Тип замены" :options="replacement_types" crmSync v-model="defect.replacement_type"
                    :placeholder="'Укажите тип замены'" />
                <inputDefault type="date" label="Дата доставки" placeholder="Выберите дату доставки"
                    v-model="defect.delivery_date" :required="true" />
                <inputDefault label="Адрес доставки" placeholder="Введите адрес доставки" v-model="defect.delivery_address"
                    :required="false" />
                <Selector @change="defect.payment_method_id = null" label="Юр. лицо" required :options="legalEntities" v-model="defect.legal_entity_id"
                    :placeholder="'Укажите юр. лицо'" />
                <Selector :disabled="!defect.legal_entity_id" label="Способ оплаты" :options="payment_types_options" v-model="defect.payment_method_id"
                    :placeholder="'Укажите способ оплаты'" />
                <Selector :disabled="!defect.legal_entity_id" label="Статус завершения" crmSync :options="is_completed" v-model="defect.is_completed"
                    :placeholder="'Укажите статус завершения брака'" />
                <inputDefault label="Дата завершения" placeholder="Не завершён" v-model="defect.completed_at"
                    :required="false" :disabled="true" />
            </Card>
            <Card title="Список товаров">
                <Table class="w-100">
                    <template v-slot:thead>
                        <TH>Артикул</TH>
                        <TH>Название</TH>
                        <TH>Поставщик</TH>
                        <TH>Цена</TH>
                        <TH>Количество</TH>
                    </template>
                    <template v-slot:tbody>
                        <TR v-for="order_product in defect.order_products">
                            <TD>{{ order_product.product_main_sku }}</TD>
                            <TD>{{ order_product.product_name }}</TD>
                            <TD>{{ order_product.contractor_name }}</TD>
                            <TD>{{ order_product.avg_price.priceFormat(true) }} </TD>
                            <TD>{{ order_product.amount }}</TD>
                        </TR>
                    </template>
                </Table>
            </Card>
            <DefectMultipleFilesModal @upload_files="uploadDefectFiles($event)" @cancel_upload="uploadFilesModal = false" v-if="uploadFilesModal"></DefectMultipleFilesModal>
            <DefaultModal width="600px" @close_modal="filesListModal = false" title="Загруженные файлы" v-if="filesListModal">
                <template v-slot:default>
                    <div class="position-relative">
                        <LoadingCover style="position: absolute!important; margin: 0!important" :loadingCover="isModalCover"></LoadingCover>
                        <div class="p-3 d-flex flex-column">
                            <span class="w-100 d-flex align-item-center flex-row pb-2" v-for="(fileName, fileLink) in JSON.parse(defect.files)" :key="fileLink">
                                <span class="text-primary me-1"><font-awesome-icon icon="fa-solid fa-download" /></span>
                                <a :title="fileName" class="text-truncate d-block w-100" :href="'storage/' + fileLink"> {{ fileName }} </a>
                                <a style="cursor: pointer" @click="deleteFile(fileLink)" class="text-danger ps-2"><font-awesome-icon icon="fa-solid fa-trash" /></a>
                            </span>
                        </div>
                    </div>
                </template>
            </DefaultModal>
        </template>
    </EntityLayout>
</template>
<style scoped>
#header-info span {
    padding-right: 10px;
}
</style>
<script>
import { defectAPI } from '../api/defect_api.js';
import { mapGetters } from 'vuex';

import IndexTableMixin from '../utils/indexTableMixin.js';
import EntityLayout from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';
import inputDefault from '../components/inputs/default_input.vue';
import Selector from '../components/inputs/select_input.vue';

import DefaultModal from '../components/modals/default_modal.vue';
import DefectMultipleFilesModal from '../components/modals/DefectMultipleFilesModal.vue';

import LoadingCover from '../components/Layout/LoadingCover.vue';

export default {
    components: { EntityLayout, Card, inputDefault, Selector, DefaultModal, DefectMultipleFilesModal, LoadingCover },
    mixins: [IndexTableMixin],
    data() {
        return {
            defect: {},
            isLoaded: false,
            isCover: false,
            isModalCover: false,
            uploadFilesModal: false,
            filesListModal: false,
            product_locations: [
                { id: 0, name: "Нет" },
                { id: 1, name: "В офисе" },
                { id: 2, name: "У курьера" },
                { id: 3, name: "Частично - Офис/Курьер" },
                { id: 4, name: "Частично - Офис/Поставщик" },
                { id: 5, name: "Частично - Поставщик/Курьер" },
                { id: 6, name: "Вернули поставщику" },
                { id: 7, name: "Передан в СЦ"},
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
            is_completed: [
                { id: 0, name: "Не завершён" },
                { id: 1, name: "Завершён" },
            ]
        }
    },
    methods: {
        initSettings() {
            this.settings.tableTitle = 'Товары из заказа-брака';
            this.settings.localStorageKey = 'defects_params'

            this.settings.withCreateButton = false;
            this.settings.withHeader = false;
            this.settings.withExport = false;
            this.settings.isLoading = false;
            this.settings.saveParams = false;
            this.settings.withBottomBox = false;
            this.settings.withFooter = false;
            this.settings.withPagination = false;
            this.settings.withFilters = false;
            this.settings.withTitle = false;

            this.onInitData = res => {
                this.defects = res.data.data;
            }

            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'comment';
                defaultParams.sort_type = this.params.sort_type || 'desc';
            }
        },
        async uploadDefectFiles(files) {
            this.isCover = true;
            const formFileData = new FormData();
            files.forEach(file => {
                formFileData.append('files[]', file);
            })
            await defectAPI.loadFiles(formFileData, this.defect.id).then(res => {
                this.defect = res.data.data;
                this.showToast("OK", "Файлы загружены", "success");
            });
            this.isCover = false;
        },
        async deleteFile(link) {
            this.isModalCover = true;
            await defectAPI.deleteFile(link, this.defect.id).then(res => {
                this.showToast("ОК", "Файл удален", "success");
                this.defect = res.data.data;
                if (this.defect.files == null) {
                    this.filesListModal = false;
                }
            });
            this.isModalCover = false;
        },
        async show() {
            const id = this.$route.params.defect_id;
            const res = await defectAPI.show(id);
            if (res) this.defect = res.data.data;
            this.isLoaded = true;
        },
        async onSave() {
            this.isLoaded = false;
            const response = await defectAPI.update({ defect: this.defect });
            this.showToast("ОК", "Брак изменён", "success");
            this.show();
        },
        onExit() {
            this.$router.push('/defects');
        },
    },
    mounted() {
        this.show();
        this.$store.dispatch('loadLegalEntities');
        this.$store.dispatch('loadPaymentMethods');
    },
    computed: {
        ...mapGetters({ legalEntities: 'getLegalEntities' }),
        ...mapGetters({ paymentMethods: 'getPaymentMethods' }),
        moneyRefund() {
            if (this.defect.product_location == 6 || this.defect.refund_type == 2 || this.defect.replacement_type == 0) {
                return false;
            } else {
                this.defect.money_refund_status = 0;
                return true;
            }
        },
        payment_types_options() {
            if (this.defect.legal_entity_id !== null) {
                return this.paymentMethods.filter((element) => {
                    return element.legal_entity_id == this.defect.legal_entity_id;
                });
            } else {
                return [];
            }
        }
    }
}
</script>
