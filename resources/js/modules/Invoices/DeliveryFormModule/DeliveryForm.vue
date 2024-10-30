<template>
    <Card title="Доставка">
        <FormInput v-model="deliveryDateFrom" :max="maxDate"
            :disabled="!canUserEditInvoice" class="w-100" type="date" label="Дата доставки от" />
        <FormInput v-model="deliveryDateTo" :min="minDate"
            :disabled="!canUserEditInvoice" class="w-100" type="date" label="Дата доставки до" />
        <FormSelect v-model="deliveryType" :options="deliveryOptions" :disabled="!canUserEditInvoice" class="w-100"
            placeholder="Выбрать способ доставки" label="Способ доставки" />
    </Card>
</template>

<script>
import InvoiceMixin from '../../../mixins/InvoiceMixin';

import Card from '../../../ui/containers/Card.vue'
import FormInput from '../../../ui/inputs/FormInput.vue';
import FormSelect from '../../../ui/selects/FormSelect.vue';

export default {
    components: { Card, FormInput, FormSelect },
    mixins: [InvoiceMixin],
    data() {
        return {
            deliveryOptions: [
                { id: 0, name: 'Курьером' },
                { id: 1, name: 'Самовывоз' },
                { id: 2, name: 'Смешанный' }
            ],
        }
    },
    computed: {
        minDate(){
            return this.$store.getters['invoiceModule/getMinDeliveryDate'];
        },
        maxDate(){
            return this.$store.getters['invoiceModule/getMaxDeliveryDate'];
        },
        deliveryDateFrom: {
            get() {
                return this.minDate
            },
            set(value) {
                this.$store.commit('invoiceModule/setProductsDeliveryDateFrom', value)
            },
        },
        deliveryDateTo: {
            get() {
                return this.maxDate
            },
            set(value) {
                this.$store.commit('invoiceModule/setProductsDeliveryDateTo', value)
            },
        },
        deliveryType: {
            get() {
                return this.invoice.delivery_type
            },
            set(value) {
                this.$store.commit('invoiceModule/setDeliveryType', value)
            },
        },
    }
}
</script>
