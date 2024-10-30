<template>
    <Card title="Основные данные">
        <div class="p-3 d-flex flex-row  w-100">
            <button @click="isShowFilesModal = true" type="button" class="btn btn-sm btn-outline-primary">
                <font-awesome-icon class="me-2" :icon="['fas', 'file']" />
                Прикрепить/скачать файлы счёта
            </button>
        </div>

        <div class="d-flex flex-row flex-wrap w-100">
            <FormInput v-model="number" :disabled="!paymentValidation" label="Номер счёта" required />
            <ContractorFormSelect v-model="contractorID" :disabled="!paymentValidation" required />
            <FormInput v-model="date" :disabled="!paymentValidation" type="date" label="Дата счёта" required />
            <LegalEnityFormSelect v-model="legalEntityID" :disabled="!paymentValidation" required />
        </div>

        <div class="w-25 d-flex flex-row">
            <div class="pt-3 pe-3 ps-3 w-100" style="font-size: 13px;">
                <label class="text-muted pb-2" for="comment">Комментарий</label>
                <textarea v-model="comment" :disabled="!canUserSaveInvoice" id="comment" class="form-control" cols="20"
                    rows="5" style="height: 150px;"></textarea>
            </div>
        </div>
    </Card>
    <InvoiceFilesModal v-if="isShowFilesModal" @close_modal="isShowFilesModal = false" />

</template>



<script>
import InvoiceMixin from '../../../mixins/InvoiceMixin';

import Card from '../../../ui/containers/Card';
import FormInput from '../../../ui/inputs/FormInput.vue';
import FormInputFile from '../../../ui/inputs/FormInputFile.vue';
import FormSelect from '../../../ui/selects/FormSelect.vue';
import ContractorFormSelect from '../../../shared/contractors/FormSelect.vue';
import LegalEnityFormSelect from '../../../shared/legal_entities/FormSelect.vue';
import InvoiceFilesModal from './InvoiceFilesModal.vue';

export default {
    components: { Card, FormInput, FormInputFile, FormSelect, ContractorFormSelect, LegalEnityFormSelect, InvoiceFilesModal },
    mixins: [InvoiceMixin],
    data() {
        return {
            isShowFilesModal: false,
        }
    },
    computed: {
        number: {
            get() {
                return this.invoice.number;
            },
            set(value) {
                this.$store.commit('invoiceModule/setNumber', value);
            }
        },
        contractorID: {
            get() {
                return this.invoice.contractor_id
            },
            set(value) {
                this.$store.commit('invoiceModule/setContractorID', value)
            },
        },
        date: {
            get() {
                return this.invoice.date
            },
            set(value) {
                this.$store.commit('invoiceModule/setDate', value)
            },
        },
        comment: {
            get() {
                return this.invoice.comment
            },
            set(value) {
                this.$store.commit('invoiceModule/setComment', value)
            },
        },
        legalEntityID: {
            get() {
                return this.invoice.legal_entity_id
            },
            set(value) {
                this.$store.commit('invoiceModule/setLegalEntityID', value)
            },
        }
    },
    methods: {
        handleInvoiceFile(event) {
            const file = event.target.files[0];
            this.$store.commit('invoiceModule/setInvoiceFile', file);
        }
    },
}
</script>
