<template>
    <div v-if="transaction">
        <a v-if="transaction?.transactionable_type === 'App\\Models\\V1\\OrderProduct' && transactionableLink"
            :href="transactionableLink" target="_blank" style="text-decoration: none;">
            <div class="d-flex align-items-center">
                <img alt="RetailCRM"
                    src="https://s3-s1.retailcrm.tech/ru-central1/retailcrm-static/branding/retailcrm/logo/logo_icon_core.svg"
                    style="width: 18px; height: 18px;">
                <span class="ps-2"> {{ transactionableName }} </span>
            </div>
        </a>
        <a v-else-if="(transaction?.transactionable_type === 'App\\Models\\V1\\InvoiceProduct' || transaction?.transactionable_type === 'App\\Models\\V1\\Invoice') && transactionableLink"
            :href="transactionableLink" target="_blank" style="text-decoration: none;">
            <div class="d-flex align-items-center">
                <font-awesome-icon icon="fa-solid fa-clipboard" size="sm" style="width: 18px; height: 18px;" />
                <span class="ps-2"> {{ transactionableName }} </span>
            </div>
        </a>
        <a v-else-if="transaction?.transactionable_type === 'App\\Models\\V1\\InventoryProduct' && transactionableLink"
            :href="transactionableLink" target="_blank" style="text-decoration: none;">
            <div class="d-flex align-items-center">
                <font-awesome-icon icon="fa-solid fa-boxes-packing" size="sm" style="width: 18px; height: 18px;" />
                <span class="ps-2"> {{ transactionableName }} </span>
            </div>
        </a>

        <a v-else-if="(transaction?.transactionable_type === 'App\\Models\\V1\\ContractorRefundProduct' || transaction?.transactionable_type === 'App\\Models\\V1\\ContractorRefund') && transactionableLink"
            :href="transactionableLink" target="_blank" style="text-decoration: none;">
            <div class="d-flex align-items-center">
                <font-awesome-icon icon="fa-solid fa-arrow-rotate-right" size="sm" style="width: 18px; height: 18px;" />
                <span class="ps-2"> {{ transactionableName }} </span>
            </div>
        </a>

        <a v-else-if="transaction?.transactionable_type === 'App\\Models\\V1\\DebtPayment' && transactionableLink"
            :href="transactionableLink" target="_blank" style="text-decoration: none;">
            <div class="d-flex align-items-center">
                <font-awesome-icon icon="fa-solid fa-clipboard" size="sm" style="width: 18px; height: 18px;" />
                <span class="ps-2"> {{ transactionableName }} </span>
            </div>
        </a>

        <a v-else-if="transaction?.transactionable_type === 'App\\Models\\V1\\MoneyRefundable' && transactionableLink"
            :href="transactionableLink" target="_blank" style="text-decoration: none;">
            <div class="d-flex align-items-center">
                <font-awesome-icon class="pe-1" icon="fa-regular fa-credit-card" size="sm"
                    style="width: 18px; height: 18px;" />
                <font-awesome-icon v-if="transaction?.transactionable.refundable_type === 'App\\Models\\V1\\Invoice'"
                    icon="fa-solid fa-clipboard" size="sm" style="width: 18px; height: 18px;" />
                <font-awesome-icon
                    v-if="transaction?.transactionable.refundable_type === 'App\\Models\\V1\\ProductRefund'"
                    icon="fa-solid fa-rotate-left" size="sm" style="width: 18px; height: 18px;" />
                <font-awesome-icon
                    v-if="transaction?.transactionable.refundable_type === 'App\\Models\\V1\\ContractorRefund'"
                    icon="fa-solid fa-arrow-rotate-right" size="sm" style="width: 18px; height: 18px;" />
                <font-awesome-icon v-if="transaction?.transactionable.refundable_type === 'App\\Models\\V1\\Defect'"
                    icon="fa-solid fa-box-open" size="sm" style="width: 18px; height: 18px;" />
                <span class="ps-2"> {{ transactionableName }} </span>
            </div>
        </a>

        <a v-else-if="transaction?.transactionable_type === 'App\\Models\\V1\\Expenses\\Expense' && transactionableLink"
            :href="transactionableLink" target="_blank" style="text-decoration: none;">
            <div class="d-flex align-items-center">
                <font-awesome-icon icon="fa-solid fa-bag-shopping" size="sm" style="width: 18px; height: 18px;" />
                <span class="ps-2"> {{ transactionableName }} </span>
            </div>
        </a>

        <a v-else-if="transaction?.transactionable_type === 'App\\Models\\MoneyRefundIncome' && transactionableLink"
            :href="transactionableLink" target="_blank" style="text-decoration: none;">
            <div class="d-flex align-items-center">
                <font-awesome-icon class="pe-1" icon="fa-regular fa-credit-card" size="sm"
                    style="width: 18px; height: 18px;" />
                <span class="ps-2"> {{ transactionableName }} </span>
            </div>
        </a>

        <span v-else>{{ transactionableName }}</span>
    </div>
</template>


<script>
export default {
    props: {
        transaction: {
            type: Object,
            required: true
        },
    },
    computed: {
        transactionableLink() {
            if (this.transaction?.transactionable && (this.transaction?.transactionable_type === 'App\\Models\\V1\\InvoiceProduct' || this.transaction?.transactionable_type === 'App\\Models\\V1\\Invoice')) {
                if (this.transaction?.transactionable.invoice_id) {
                    return `/#/invoices/${this.transaction?.transactionable.invoice_id}/edit`;
                } else {
                    return `/#/invoices/${this.transaction?.transactionable.id}/edit`
                }
            }
            else if (this.transaction?.transactionable && this.transaction?.transactionable_type === 'App\\Models\\V1\\InventoryProduct') {
                return `/#/inventories/${this.transaction?.transactionable.inventory_id}/edit`;
            }
            else if (this.transaction?.transactionable && this.transaction?.transactionable_type === 'App\\Models\\V1\\DebtPayment') {
                return `/#/invoices/${this.transaction?.transactionable.invoice_id}/edit`;
            }
            else if (this.transaction?.transactionable && (this.transaction?.transactionable_type === 'App\\Models\\V1\\ContractorRefundProduct' || this.transaction?.transactionable_type === 'App\\Models\\V1\\ContractorRefund')) {
                return `/#/contractor_refunds/${this.transaction?.transactionable.contractor_refund_id}/edit`;
            }
            else if (this.transaction?.transactionable && this.transaction?.transactionable_type === 'App\\Models\\V1\\OrderProduct') {
                return `https://babylissrus.retailcrm.ru/orders/${this.transaction?.transactionable.order.external_id}/edit`;
            }
            else if (this.transaction?.transactionable && this.transaction?.transactionable_type === 'App\\Models\\V1\\MoneyRefundable') {
                return `/#/money-refunds/${this.transaction?.transactionable_id}/edit`;
            }
            else if (this.transaction?.transactionable && this.transaction?.transactionable_type === 'App\\Models\\V1\\Expenses\\Expense') {
                return `/#/expenses/${this.transaction?.transactionable_id}/edit`;
            }
            else if (this.transaction?.transactionable && this.transaction?.transactionable_type === 'App\\Models\\MoneyRefundIncome') {
                return `/#/money-refunds/${this.transaction?.transactionable.money_refundable_id}/edit`;
            }
            else {
                return null;
            }
        },
        transactionableName() {
            if (this.transaction?.transactionable_type === 'App\\Models\\V1\\InvoiceProduct' || this.transaction?.transactionable_type === 'App\\Models\\V1\\Invoice') {
                if (this.transaction?.transactionable) {
                    if (this.transaction?.transactionable.invoice) {
                        return this.transaction?.transactionable.invoice.number;
                    } else {
                        return `${this.transaction?.transactionable.number}`;
                    }
                } else {
                    return 'Счёт (товар удалён)';
                }
            }
            else if (this.transaction?.transactionable_type === 'App\\Models\\V1\\InventoryProduct') {
                if (this.transaction?.transactionable) {
                    return `Инвентаризация №${this.transaction?.transactionable.inventory.id}`;
                } else {
                    return 'Инвентаризация (товар удалён)';
                }
            }
            else if (this.transaction?.transactionable_type === 'App\\Models\\V1\\ContractorRefundProduct' || this.transaction?.transactionable_type === 'App\\Models\\V1\\ContractorRefund') {
                if (this.transaction?.transactionable) {
                    return `Возврат поставщику №${this.transaction?.transactionable.id}`;
                } else {
                    return 'Возврат поставщику (товар удалён)';
                }
            }
            else if (this.transaction?.transactionable_type === 'App\\Models\\V1\\OrderProduct') {
                if (this.transaction?.transactionable) {
                    return `${this.transaction?.transactionable.order.number}`;
                } else {
                    return `Заказ (товар удален)`;
                }
            }
            else if (this.transaction?.transactionable_type === 'App\\Models\\V1\\Product') {
                return 'Корректировка в товаре';
            }
            else if (this.transaction?.transactionable_type === 'App\\Models\\V1\\DebtPayment') {
                return 'Оплата за счёт долга';
            }
            else if (this.transaction?.transactionable_type === 'App\\Models\\V1\\Stock') {
                return 'Исходный остаток';
            }
            else if (this.transaction?.transactionable_type === 'App\\Models\\V1\\MoneyRefundable') {
                return 'возврат ДС №' + this.transaction?.transactionable.id;
            }
            else if (this.transaction?.transactionable_type === 'App\\Models\\V1\\Expenses\\Expense') {
                return `Хоз. расход №${this.transaction?.transactionable_id}`;
            }
            else if (this.transaction?.transactionable_type === 'App\\Models\\MoneyRefundIncome') {
                return 'возврат ДС №' + this.transaction?.transactionable.money_refundable_id;
            }
            else {
                return `${this.transaction?.transactionable_type}`;
            }
        },
    },
}
</script>
