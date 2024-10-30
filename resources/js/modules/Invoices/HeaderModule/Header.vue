<template>
    <div class="d-flex flex-column w-100 border-1 border rounded  overflow-hidden shadow-sm">
        <div class="d-flex flex-row w-100 p-3" :class="headerClasses">
            <div class="d-flex flex-column w-75">
                <div class="d-flex flex-row">
                    <h3 style="text-overflow: ellipsis;">
                        Счет <strong>{{ invoiceTitle }}</strong>
                    </h3>
                    <InvoiceHistoryModal v-if="isShowHistory" @close="toggleHistoryModal()"
                        :transactions="invoice.transactions"
                        :refused="invoice.refuses_history.slice().reverse()"
                        :payments="invoice.payment_history.slice().reverse()"
                        title="История по счёту" />
                </div>
                <div>
                    <div v-if="invoice.creator?.name" class="d-inline me-3">
                        <small>Создал(а):</small>
                        <strong class="ps-1">{{ invoice.creator.name}}</strong>
                    </div>
                    <timestamp v-if="invoice?.created_at" class="me-3" :datetime="invoice.created_at" label="Создан" />
                    <div v-if="invoice.updater?.name" class="d-inline me-3">
                        <small>Обновил(а):</small>
                        <strong class="ps-1">{{ invoice.updater.name}}</strong>
                    </div>
                    <timestamp v-if="invoice?.updated_at" :datetime="invoice.updated_at" label="Обновлен" />
                </div>

            </div>

            <div v-if="!invoice.isNew" class="ms-auto d-flex flex-row align-items-center w-25">
                <strong class="ms-auto">
                    <InvoiceStatus :statusCode="invoice.status" style="width: unset!important;" />
                </strong>
                <time>{{ formatDate(invoice.status_set_at, 'DD.MM.YYYY HH:mm:ss') }}</time>
            </div>

            <RefundCreateModal v-if="isShowRefundCreateModal" @close="isShowRefundCreateModal = false" width="60%" />
            <RefundsListingModal v-if="isShowRefundsListingModal" @close="isShowRefundsListingModal = false" width="65%" />
        </div>

        <div v-if="!invoice.isNew" class=" bg-gradient p-3 pt-2 pb-2 border-top" :class="headerClasses">
            <abutton v-if="isHistoryEnabled" @click="toggleHistoryModal()" class="me-3"
                icon="fa-regular fa-clock" text="История" />
            <abutton v-if="canUserReadContractorRefund" @click="isShowRefundsListingModal = true" class="me-3"
                icon="fa-solid fa-arrow-rotate-right" text="Возвраты" />

            <div class="border-start border-2 d-inline p-1"></div>

            <abutton v-if="canUserCopyInvoice" @click="copyInvoice()" class="me-3" icon="fa-regular fa-copy"
                text="Копировать" />
            <abutton v-if="canUserCreateContractorRefund" @click="isShowRefundCreateModal = true" class="me-3"
                icon="fa-solid fa-plus" text="Создать возврат" />

            <!-- <div class="border-start border-2 d-inline p-1"></div>

            <abutton class="me-3"
                icon="fa-solid fa-paperclip" text="Файлы" /> -->
        </div>
    </div>
</template>

<script>
import InvoiceMixin from '../../../mixins/InvoiceMixin';

import timestamp from './ui/timestamp.vue';
import abutton from './ui/abutton.vue';
import InvoiceStatus from '../../../shared/invoices/StatusBadge.vue';
import InvoiceHistoryModal from './components/InvoiceHistoryModal.vue';
import RefundCreateModal from './components/RefundCreateModal.vue';
import RefundsListingModal from './components/RefundsListingModal.vue';

export default {
    components: { timestamp, abutton, InvoiceStatus, InvoiceHistoryModal, RefundCreateModal, RefundsListingModal },
    mixins: [InvoiceMixin],
    data() {
        return {
            isShowHistory: false,
            isShowRefundsListingModal: false,
            isShowRefundCreateModal: false,
        }
    },
    computed: {
        /**
         * Возвращает заголовок счёта.
         */
        invoiceTitle() {
            return this.invoice.isNew ? 'новый' : this.invoice.original.number;
        },

        /**
         *  Возвращает классы для блока заголовка в зависимости от значение `invoice.status`.
         */
        headerClasses() {
            switch (this.invoice.status) {
                case 0: return 'invoice-status-expect-bg text-white';
                case 1: return 'invoice-status-part-bg text-white';
                case 2: return 'invoice-status-complete-bg text-white';
                case 3: return 'invoice-status-cancel-bg text-white';
                case null: return 'text-dark';
            }
        },
        isHistoryEnabled() {
            return this.invoice.transactions.length ||
                this.invoice.payment_history.length ||
                this.invoice.refuses_history.length;
        }
    },
    methods: {
        /**
         * Создаёт несохранённую копию текущего счёта.
         */
        copyInvoice() {
            this.$store.commit('invoiceModule/setAsCopy');
            this.$router.push('/invoices/copy')
        },

        /**
         * Переключает отображение модального окна истории.
         */
        toggleHistoryModal() {
            this.isShowHistory = !this.isShowHistory;
        },
    }
}
</script>
