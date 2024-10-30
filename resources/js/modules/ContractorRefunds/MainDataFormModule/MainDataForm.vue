<template>
    <FormSelect :disabled="!canUserUpdateContractorRefund" v-model="is_complete" :options="statusOptions" label="Статус возврата" placeholder="Выберите" required />
    <FormInput :disabled="!canUserUpdateContractorRefund" v-model="comment" type="text" label="Комментарий" />
    <FormInput :disabled="!canUserUpdateContractorRefund" v-model="delivery_date" type="date" label="Дата доставки" required />
    <FormInput :disabled="!canUserUpdateContractorRefund" v-model="delivery_address" type="text" label="Адрес доставки" autocomplete="shipping" />
    <FormSelect :disabled="!canUserUpdateContractorRefund" v-model="delivery_status" :options="deliveryOptions" label="Статус доставки" placeholder="Выберите" />
    <FormInputFile :disabled="!canUserUpdateContractorRefund" @change="handleRefundDocuments" :fileURL="this.$store.state.contractorRefundModule.refund_documents"
        accept=".xlsx,.xls,.csv,.pdf,.doc,.docx,.docm" label="Возвратные документы" />
</template>

<script>
import ContractorRefundMixin from '../../../mixins/ContractorRefundMixin';

import FormSelect from '../../../ui/selects/FormSelect.vue';
import FormInput from '../../../ui/inputs/FormInput.vue';
import FormInputFile from '../../../ui/inputs/FormInputFile.vue';
export default {
    components: { FormSelect, FormInput, FormInputFile },
    mixins: [ContractorRefundMixin],
    data() {
        return {
            statusOptions: [
                { id: 0, name: 'Не завершён' },
                { id: 1, name: 'Завершён' }
            ],
            deliveryOptions: [
                { id: 'complete', name: 'Доставлено' },
                { id: 'at_courier', name: 'У курьера' },
                { id: 'in_tc', name: 'В ТК' }
            ],
        };
    },
    computed: {
        is_complete: {
            get() {
                return this.$store.state.contractorRefundModule.is_complete;
            },
            set(value) {
                this.$store.commit('contractorRefundModule/setIsComplete', value);
            }
        },
        comment: {
            get() {
                return this.$store.state.contractorRefundModule.comment;
            },
            set(value) {
                this.$store.commit('contractorRefundModule/setIsComment', value)
            },
        },
        delivery_date: {
            get() {
                return this.$store.state.contractorRefundModule.delivery_date;
            },
            set(value) {
                this.$store.commit('contractorRefundModule/setIsDeliveryDate', value)
            },
        },
        delivery_address: {
            get() {
                return this.$store.state.contractorRefundModule.delivery_address;
            },
            set(value) {
                this.$store.commit('contractorRefundModule/setIsDeliveryAddress', value)
            },
        },
        delivery_status: {
            get() {
                return this.$store.state.contractorRefundModule.delivery_status;
            },
            set(value) {
                this.$store.commit('contractorRefundModule/setIsDeliveryStatus', value)
            },
        },
    },
    methods: {
        handleRefundDocuments(event) {
            const file = event.target.files[0];
            this.$store.commit('contractorRefundModule/setRefundDocuments', file);
        }
    }
}
</script>
