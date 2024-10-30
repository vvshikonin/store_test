<template>
    <EntityLayout @save="onSave()" @exit="isExiting = true" @destroy="destroy()" :isLoaded="isLoaded"
        :loadingCover="loadingCover" :withSaveButton="canUserUpdateContractorRefund" :withDeleteButton="canUserDeleteContractorRefund">>
        <template v-slot:header>
            <div class="d-flex flex-column bg-primary w-100 p-3 rounded border text-white bg-gradient shadow-sm">
                <div>
                    <h3>
                        Возврат товара поставщику <strong>№{{ $route.params.contractor_refund_id }}</strong> от
                        <strong>{{ formatDate($store.state.contractorRefundModule.created_at, 'DD.MM.YYYY HH:mm:ss')
                        }}</strong>

                    </h3>
                </div>

                <div id="header-info">
                    <span>
                        Счёт создания:
                        <a class="p-0 link-light fw-bold" title="открыть счёт" target="_blank"
                            :href="'#/invoices/' + $store.state.contractorRefundModule.invoice.id + '/edit'">
                            {{ $store.state.contractorRefundModule.invoice.number }}
                        </a>
                    </span>
                    <span>
                        Поставщик:
                        <strong>{{ $store.state.contractorRefundModule.invoice.contractor.name }}</strong>
                    </span>
                    <span>
                        Создал:
                        <strong>{{ $store.state.contractorRefundModule.creator.name }}</strong>
                    </span>
                    <span>
                        Изменил:
                        <strong>{{ $store.state.contractorRefundModule.updater.name }}</strong>
                    </span>
                    <span>
                        Дата изменения:
                        <strong>{{ formatDate($store.state.contractorRefundModule.updated_at, 'DD.MM.YYYY HH:mm:ss')
                        }}</strong>
                    </span>
                </div>
            </div>

        </template>
        <template v-slot:content>
            <Card title="Данные возврата">
                <MainDataForm></MainDataForm>
            </Card>
            <Card title="Товары">
                <RefundProductsEditTable />
            </Card>
            <Modal v-if="isSavingModalOpen" width="40%" top="20%" title="Сохранение возврата" @close="isSavingModalOpen = false">
                <template v-slot>
                    <div class="p-3 pb-0">
                        После подтверждения товар зачислится в остатки. Вы уверены?
                    </div>
                    <div class="p-3">
                        <button @click="save()" type="button" class="btn btn-primary me-2">Сохранить</button>
                        <button @click="isSavingModalOpen = false" type="button" class="btn btn-light border">Отменить</button>
                    </div>
                </template>
            </Modal>
        </template>
    </EntityLayout>
</template>
<style scoped>
#header-info span {
    padding-right: 10px;
}
</style>
<script>
import ContractorRefundMixin from '../mixins/ContractorRefundMixin';

import Card from '../ui/containers/Card.vue';

import MainDataForm from '../modules/ContractorRefunds/MainDataFormModule/MainDataForm.vue';
import EntityLayout from '../components/Layout/entity_edit_page.vue';
import Modal from '../ui/modals/modal.vue';
import RefundProductsEditTable from '../modules/ContractorRefunds/RefundProductsEditTableModule/RefundProductsEditTable.vue';

export default {
    components: { EntityLayout, MainDataForm, Card, RefundProductsEditTable, Modal },
    mixins: [ContractorRefundMixin],
    data() {
        return {
            isLoaded: false,
            loadingCover: false,
            isSavingModalOpen: false,
            isExiting: false,
            initialStatus: null,
        }
    },
    async mounted() {
        await this.$store.dispatch('contractorRefundModule/load', this.$route.params.contractor_refund_id).then(res => {
            this.setInitialStatus();
        });
        this.isLoaded = true;
    },
    methods: {
        onSave() {
            let currentStatus = this.$store.state.contractorRefundModule.is_complete;

            if (!this.initialStatus && currentStatus)
            {
                this.isSavingModalOpen = true;
            } else {
                this.save();
            }
        },

        setInitialStatus() {
            this.initialStatus = this.$store.state.contractorRefundModule.is_complete;
        },

        async save() {
            this.isSavingModalOpen = false;
            if (!this.$store.state.contractorRefundModule.contractor_refund_products.length) {
                this.showToast("Не удалось сохранить изменения", "В возврате должен быть хотя бы один товар!", "warning");
                return;
            }

            this.loadingCover = true;
            await this.$store.dispatch('contractorRefundModule/save').then(res => {
                this.setInitialStatus();
            });
            this.loadingCover = false;
            if (this.isExiting) {
                this.$router.push('/contractor_refunds');
            }
        },
        async destroy() {
            await this.$store.dispatch('contractorRefundModule/delete');
            this.$router.push('/contractor_refunds');
        }
    }
}
</script>
