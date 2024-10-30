<template>
    <Card title="Оплата">
        <div v-if='!invoice.is_edo' class="p-3 d-flex flex-row  w-100">
            <button @click="isShowFilesModal = true" type="button" class="btn btn-sm btn-outline-primary">
                <font-awesome-icon class="me-2" :icon="['fas', 'file']" />
                Прикрепить/скачать файлы чека/накладной
            </button>
        </div>
        <FormSelect v-model="paymentMethodID" :options="currentPaymentMethods" :disabled="!paymentValidation"
            :required="true" placeholder="Выбрать способ оплаты" label="Способ оплаты" />
        <FormSelect v-model="paymentStatus" :options="statusOptions" :disabled="!paymentValidation"
            placeholder="Выбрать статус" label="Статус оплаты" required />
        <FormSelect v-model="paymentConfirm" :options="confirmOptions" :disabled="true" placeholder="Выбрать статус"
            label="Статус подтверждение оплаты директором" required />
        <FormInput v-model="paymentDate" :disabled="!paymentValidation" type="date" label="Дата оплаты" />
        <FormInput v-model="paymentOrderDate" :disabled="!paymentValidation" type="date"
            label="Дата заведения платёжного поручения" />
        <FormSelect v-model="isUseDebt" @change="useDebtHandle()" :disabled="!paymentValidation"
            :options="useDebtOptions" placeholder="Выбрать" label="Использовать долг поставщика" required />
        <DebtSumInput v-if="isUseDebt == 1" v-model="debtPayment" :disabled="!paymentValidation"
            :sum="$store.getters['invoiceModule/getInvoiceSum']" />

        <div class="w-25">
            <Checkbox v-model="invoice.is_edo" class="w-100" title="Чек в ЭДО" :disabled="!canUserSaveInvoice"/>
        </div>


        <PaymentFilesModal v-if="isShowFilesModal" @close_modal="isShowFilesModal = false" />
    </Card>

</template>

<script>
import { mapGetters } from 'vuex';

import InvoiceMixin from '../../../mixins/InvoiceMixin';
import Checkbox from '../../../ui/checkboxes/DefaultCheckbox.vue'
import Card from '../../../ui/containers/Card';
import FormInput from '../../../ui/inputs/FormInput.vue';
import FormInputFile from '../../../ui/inputs/FormInputFile.vue';
import FormSelect from '../../../ui/selects/FormSelect.vue';
import DebtSumInput from './ui/DebtSumInput.vue'
import PaymentFilesModal from './PaymentFilesModal.vue'

export default {
    components: { Card, FormInput, FormInputFile, FormSelect, DebtSumInput, PaymentFilesModal, Checkbox },
    mixins: [InvoiceMixin],
    data() {
        return {
            statusOptions: [
                { id: 0, name: 'Не платить (оплата не требуется)' },
                { id: 1, name: 'Оплачен' },
                { id: 2, name: 'Требует оплаты' },
            ],
            confirmOptions: [
                { id: 0, name: 'Не подтверждён' },
                { id: 1, name: 'Подтверждён' }
            ],
            useDebtOptions: [
                { id: 0, name: 'Не использовать' },
                { id: 1, name: 'Использовать' }
            ],
            isUseDebt: 0,
            isShowFilesModal: false,
        }
    },
    computed: {
        ...mapGetters({ paymentMethods: 'getPaymentMethods' }),

        /**
         * Возвращает список способов оплаты выбранного в счёте юр.лица.
         */
        currentPaymentMethods() {
            return this.$store.getters['invoiceModule/getCurrentPaymentMethods'];
        },

        /**
         * Определяет обязательно ли поле `payment_method`.
         */
        isMethodRequired() {
            return this.invoice.payment_status == 1 || this.$store.getters['invoiceModule/getReceivedSum'] > 0;
        },

        /**
         * Возвращает файл чека из формы если он загружен.
         */
        getReceiptFile() {
            const files = this.$refs.receiptFileInput.getRef().files;
            if (files.length)
                return files[0];
            else return null;
        },

        /**
         * ID способа оплаты.
         */
        paymentMethodID: {
            get() {
                return this.invoice.payment_method_id;
            },
            set(value) {
                this.$store.commit('invoiceModule/setPaymentMethodID', value);
            }
        },

        /**
         * Статус оплаты.
         */
        paymentStatus: {
            get() {
                return this.invoice.payment_status;
            },
            set(value) {
                this.$store.commit('invoiceModule/setPaymentStatus', value);
            }
        },

        /**
         * Подтверждение оплаты.
         */
        paymentConfirm: {
            get() {
                return this.invoice.payment_confirm;
            },
            set(value) {
                this.$store.commit('invoiceModule/setPaymentConfirm', value);
            }
        },

        /**
         * Дата оплаты.
         */
        paymentDate: {
            get() {
                return this.invoice.payment_date;
            },
            set(value) {
                this.$store.commit('invoiceModule/setPaymentDate', value);
            }
        },

        /**
         * Дата заведения платёжного поручения.
         */
        paymentOrderDate: {
            get() {
                return this.invoice.payment_order_date; // Получаем значение из Vuex store
            },
            set(value) {
                this.$store.commit('invoiceModule/setPaymentOrderDate', value); // Обновляем значение в Vuex store
            }
        },

        /**
         *  Оплата долгом
         */
        debtPayment: {
            get() {
                return this.invoice.debt_payment;
            },
            set(value) {
                this.$store.commit('invoiceModule/setDebtPayment', value);
            }
        },
    },
    async mounted() {
        await this.$store.dispatch('loadPaymentMethods');
        this.initUseDebt();
    },
    methods: {
        /**
         * Инициализирует состояние отображения поля "суммы оплаты долгом".
         */
        initUseDebt() {
            this.isUseDebt = parseFloat(this.debtPayment) > 0 ? 1 : 0;
        },

        /**
         *
         */
        useDebtHandle() {
            if (this.isUseDebt == 0) {
                this.$store.commit('invoiceModule/setDebtPayment', 0);
            }
        },

        /**
         * Обрабатывает запись файла в `state`.
         * @param {Event} event
         */
        handleReceiptFile(event) {
            const file = event.target.files[0];
            this.$store.commit('invoiceModule/setReceiptFile', file);
        },
    },
}
</script>
