<template>
    <EntityLayout @save="onSave()" @exit="$router.push('/invoices')" @destroy="onDelete()" :isLoaded="isLoaded"
        :loadingCover="isSaveCover" :withSaveButton="canUserSaveInvoice" :withDeleteButton="canUserDeleteInvoice">
        <template v-slot:header>
            <Header />
        </template>
        <template v-slot:content>
            <div class="d-flex flex-column">
                <MainDataForm class="w-100" />
            </div>
            <div class="d-flex flex-column">
                <div class="w-100 d-flex flex-row">
                    <PaymentForm class="w-75" />
                    <DeliveryForm class="ms-3 w-25 h-auto" />
                </div>
            </div>
            <ProductsTable />
            <Modal v-if="hasZeroPrice" @close="hasZeroPrice = false" title="Сохранение" width="500px">
                <div class="p-3"> В счёте есть товар с ценой {{ parseFloat(0).priceFormat(true) }}. <br> Вы уверены что
                    хотите сохранить? </div>
                <div class="p-3  bg-light border-top">
                    <button @click="save(); hasZeroPrice = false" class="me-2 btn btn-danger">Сохранить</button>
                    <button @click="hasZeroPrice = false" class="btn btn-light border">Отмена</button>
                </div>
            </Modal>
        </template>
    </EntityLayout>
</template>
<script>
import InvoiceMixin from '../mixins/InvoiceMixin'
import EntityLayout from '../components/Layout/entity_edit_page.vue';
import Modal from '../ui/modals/modal.vue';
import { Header } from '../modules/Invoices/HeaderModule';
import { MainDataForm } from '../modules/Invoices/MainDataFormModule';
import { PaymentForm } from '../modules/Invoices/PaymentFormModule';
import { DeliveryForm } from '../modules/Invoices/DeliveryFormModule';
import { ProductsTable } from '../modules/Invoices/ProductsTableModule';

export default {
    components: { EntityLayout, Header, MainDataForm, PaymentForm, DeliveryForm, ProductsTable, Modal },
    mixins: [InvoiceMixin],
    data() {
        return {
            isLoaded: false,
            isSaveCover: false,
            hasZeroPrice: false,
        }
    },
    async mounted() {
        const invoiceID = this.$route.params.invoice_id;
        if (!!invoiceID) {
            await this.$store.dispatch('invoiceModule/load', invoiceID);
        } else if (!this.$route.fullPath.includes('copy')) {
            this.$store.commit('invoiceModule/setAsNew')
        }

        this.isLoaded = true;
    },
    methods: {
        async onSave() {
            this.checkZeroPrice();

            if (!this.hasZeroPrice) {
                await this.save();
            }
        },
        async save() {
            try {
                this.isSaveCover = true;
                if (this.invoice.isNew) {
                    await this.$store.dispatch('invoiceModule/create')
                } else {
                    await this.$store.dispatch('invoiceModule/save');
                }
                this.isSaveCover = false;
            } catch {
                this.isSaveCover = false;
            }
        },
        async onDelete() {
            try {
                this.isSaveCover = true;
                await this.$store.dispatch('invoiceModule/delete');
                this.$router.push('/invoices');
                this.showToast("ОК", "Счёт удалён", "success");
                this.isSaveCover = false;
            } catch {
                this.isSaveCover = false;
            }

        },
        checkZeroPrice() {
            this.invoice.products.forEach(product => {
                if (parseFloat(product.price) == 0) {
                    this.hasZeroPrice = true;
                }
            });
        }
    }
}
</script>
