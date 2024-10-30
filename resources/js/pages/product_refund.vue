<template>
    <EntityLayout :loadingCover="isCover" :CancelDisabled="!isLoaded" :isLoaded="isLoaded" :withSaveButton="true"
        :withDeleteButton="false" @save="sendSaveRequest()" @exit="onExit()">
        <template v-slot:header>
            <div class="d-flex flex-row bg-primary bg-gradient p-3 rounded text-white w-100">
                <div class="d-flex flex-column w-75">
                    <div class="d-flex flex-row">
                        <h3 class="d-flex flex-row align-items-center justify-content-center">
                            <span>Возврат товара</span>
                            <a target="_blank" class="d-flex align-items-center link-light"
                                :href="'https://babylissrus.retailcrm.ru/orders/' + refund.external_id + '/edit'">
                                <img class="ms-2" dalt="RetailCRM"
                                    src="https://s3-s1.retailcrm.tech/ru-central1/retailcrm-static/branding/retailcrm/logo/logo_icon_core.svg"
                                    style="width: 24px; height: 24px;">
                                <span class="ps-1 text-white"> {{ refund.order_number }} </span>
                            </a>
                        </h3>
                    </div>
                    <div>
                        <div v-if="refund.creator?.name" class="d-inline me-1">
                            <small>Создал:</small>
                            <strong class="ps-1">{{ refund.creator.name }}</strong>
                        </div>
                        <div class="d-inline me-1">
                            <small>Создан:</small>
                            <strong class="ps-1">{{ formatDate(refund.created_at, 'DD.MM.YYYY HH:mm:ss') }}</strong>
                        </div>
                        <div v-if="refund.updater?.name" class="d-inline me-1">
                            <small>Обновил:</small>
                            <strong class="ps-1">{{ refund.updater.name }}</strong>
                        </div>
                        <div class="d-inline me-1">
                            <small>Обновлён:</small>
                            <strong class="ps-1">{{ formatDate(refund.updated_at, 'DD.MM.YYYY HH:mm:ss') }}</strong>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center w-25">
                    <OrderStatus class="ms-auto" :statusName="refund.order_status" :groupCode="refund.order_status_group"
                        style=" min-width: 270px;" />
                </div>

            </div>
        </template>
        
        <template v-slot:content>
            <Card title="Данные о заказе" class="mb-0">
                <template v-slot:top>
                    <div class="d-flex flex-row">
                        <a v-if="refund.refund_file" :href="'storage/' + refund.refund_file" target="_blank" type="button"
                            class="btn btn-outline-white text-primary me-2" download><font-awesome-icon class="pe-1"
                                icon="fa-solid fa-download" />Файл</a>
                    </div>
                </template>
                <div class="d-flex flex-column w-100">
                    <div class="d-flex flex-wrap">
                        <Selector placeholder="Укажите статус возврата" label="Статус возврата" v-model="refund.status"
                            :options="refundStatusOptions" crmSync required></Selector>
                        <Selector placeholder="Укажите местоположение товара" label="Где товар" crmSync 
                            v-model="refund.product_location" :options="productLocationOptions" required></Selector>
                        <inputDefault type="file" label="Файл возврата" v-model="loadFile" ref="productRefundFileInput">
                        </inputDefault>
                        <inputDefault placeholder="Введите адрес доставки возврата" autocomplete="street-address"
                            label="Адрес доставки" v-model="refund.delivery_address"></inputDefault>
                        <inputDefault type="date" label="Дата возврата" required v-model="refund.delivery_date">
                        </inputDefault>
                        <inputDefault placeholder="Не завершен" label="Дата завершения" v-model="refund.completed_at"
                            :disabled="true"></inputDefault>
                    </div>
                    <div>
                        <TextAreaInput label="Комментарий" placeholder="Введите комментарий (необязательно)"
                            v-model="refund.comment"></TextAreaInput>
                    </div>
                </div>
            </Card>
            <Card title="Товары" :titleBorder="false">
                <Table class="w-100" style="border: 0!important">
                    <template v-slot:thead>
                        <TH width="80px" align="center"> Артикул </TH>
                        <TH width="360px" align="left"> Название </TH>
                        <TH width="80px" align="center"> Поставщик </TH>
                        <TH width="80px" align="center"> Количество </TH>
                        <TH width="80px" align="center"> Цена </TH>
                    </template>
                    <template v-slot:tbody>
                        <TR v-for="position in refund.positions" :key="position">
                            <TD align="center"> {{ position.product_main_sku }} </TD>
                            <TD align="left"> {{ position.product_name }} </TD>
                            <TD align="center"> {{ position.contractor_name }} </TD>
                            <TD align="center"> {{ position.amount }} шт. </TD>
                            <TD align="center"> {{ position.avg_price ? position.avg_price.priceFormat(true) : '0,00 ₽' }}
                            </TD>
                        </TR>
                    </template>
                </Table>
            </Card>
        </template>
    </EntityLayout>
</template>


<script>
import { productRefundAPI } from "../api/product_refund_api";

import indexTableMixin from '../utils/indexTableMixin';

import PageWrapper from "../components/Layout/page_wrapper.vue";
import TextInput from '../components/inputs/text_input.vue';
import TextAreaInput from "../components/inputs/DefaultTextarea.vue";
import inputDefault from '../components/inputs/default_input.vue';
import EntityLayout from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';
import Selector from '../components/inputs/select_input.vue';
import OrderStatus from "../components/Other/OrderStatus.vue";

export default {
    mixins: [indexTableMixin],
    components: {
        PageWrapper,
        TextInput,
        TextAreaInput,
        inputDefault,
        EntityLayout,
        Card,
        Selector,
        OrderStatus
    },

    data() {
        return {
            id: this.$route.params.product_refund_id,
            refund: null,
            isCover: false,
            isLoaded: false,
            loadFile: null,
            refundStatusOptions: [
                { id: 0, name: 'Не завершен' },
                { id: 1, name: 'Завершен' }
            ],
            productLocationOptions: [
                { id: 'Нет', name: 'Нет' },
                { id: 'В офисе', name: 'В офисе' },
                { id: 'У курьера', name: 'У курьера' },
                { id: 'Частично - Офис/Курьер', name: 'Частично - Офис/Курьер' },
                { id: 'В ТК', name: 'В ТК' },
            ],
        }
    },
    methods: {
        initSettings() {
            this.settings.isLoading = false;
            this.settings.isCover = false;
            this.settings.isNoEntries = false;
            this.settings.isStickyFooter = false;
            this.settings.isFilterClearable = false;
            this.settings.saveParams = false;
            this.settings.withTitle = false;
            this.settings.withHeader = false;
            this.settings.withCreateButton = false;
            this.settings.withFilters = false;
            this.settings.withInfo = false;
            this.settings.withThead = true;
            this.settings.withFooter = false;
            this.settings.withPagination = false;
            this.settings.withExport = false;
        },
        async initRefundData() {
            await productRefundAPI.show(this.id).then((res) => {
                this.refund = res.data.data;
                this.isCover = false;
                this.isLoaded = true;
            })
        },
        async sendSaveRequest() {
            this.isCover = true;
            const requestData = this.makeRequest();
            const formData = this.makeFormData(requestData, true);
            if (this.$refs.productRefundFileInput.getRef().files.length) {
                formData.append('refundFile', this.$refs.productRefundFileInput.getRef().files[0]);
            }
            await productRefundAPI.update(this.id, formData).then((res) => {
                this.refund = res.data.data;
                this.isCover = false;
                this.showToast('OK', 'Сохранение завершено.', 'success');
            })
        },
        onExit() {
            this.$router.push('/product_refunds');
        },
        makeRequest() {
            return {
                id: this.refund.id,
                status: this.refund.status,
                product_location: this.refund.product_location,
                delivery_date: this.refund.delivery_date,
                delivery_address: this.refund.delivery_address,
                comment: this.refund.comment,
            }
        },
    },
    mounted() {
        this.initRefundData();
    },
}

</script>
