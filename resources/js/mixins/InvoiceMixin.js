export default {
    computed: {
        /**
         * Возвращает данные о счете из `vuex`.
         */
        invoice() {
            return this.$store.state.invoiceModule;
        },

        /**
         * Определает может ли пользователь копировать счёт.
         */
        canUserCopyInvoice() {
            return this.checkPermission('invoice_create');
        },

        /**
         * Определяет, может ли пользователь просматривать возврат поставщику.
         */
        canUserReadContractorRefund() {
            return this.checkPermission('contractor_refund_read');
        },

        /**
         * Определяет, может ли пользователь создавать возврат поставщику.
         */
        canUserCreateContractorRefund() {
            return this.checkPermission('contractor_refund_create');
        },

        /**
         * Определает может ли пользователь сохранить счёт.
         */
        canUserSaveInvoice() {
            return this.canUserEditInvoice || this.canUserUpdateComfirm || this.canUserAddProduct ||
                this.canUserEditProduct || this.canUserDeleteProducts || this.canUserReceiveProduct ||
                this.canUserRefuseProduct
        },

        /**
         * Определает может ли пользователь удалить счёт.
         */
        canUserDeleteInvoice() {
            return this.checkPermission('invoice_delete') && !this.invoice.isNew;
        },

        /**
         *  Определает есть ли у пользователя права на редактирование данных.
         */
        canUserEditInvoice() {
            return (this.checkPermission('invoice_create') && this.invoice.isNew) || this.checkPermission('invoice_update');
        },

        /**
         * Валидация запрещающая редактирование данных, если счёт оплачен, если у пользователя нет разрешения на игнорирование её.
         */
        paymentValidation() {
            return this.canUserEditInvoice && (this.checkPermission('invoice_ignore_payment_validation') || !this.invoice.original.payment_status);
        },

        /**
         * Определяет есть ли у пользователя доступ к редактированию поля `payment_confirm`.
         */
        canUserUpdateComfirm() {
            return this.checkPermission('invoice_payment_confirm_update')
        },

        /**
         * Определяет может ли пользователь добавить товар в счёт.
         */
        canUserAddProduct() {
            return this.checkPermission('invoice_position_create') || (this.checkPermission('invoice_create') && this.invoice.isNew);
        },

        /**
         * Определяет может ли пользователь редактировать товар в счёте.
         */
        canUserEditProduct() {
            return this.checkPermission('invoice_position_update') || (this.checkPermission('invoice_create') && this.invoice.isNew)
        },

        /**
         * Определяет может ли пользователь удалять товары в счёте.
         */
        canUserDeleteProducts() {
            return this.checkPermission('invoice_position_delete') || (this.checkPermission('invoice_create') && this.invoice.isNew)
        },

        /**
         * Валидация для товаров запрещающая редактирование данных, если счёт оплачен, если у пользователя нет разрешения на игнорирование её.
         */
        productPaymentValidation() {
            return this.canUserEditProduct && (this.checkPermission('invoice_ignore_payment_validation') || !this.invoice.original.payment_status) ||
                (this.checkPermission('invoice_create') && this.invoice.isNew);
        },

        /**
         * Определяет может ли пользовательредактировать поле "Оприходовано" у товара счёта.
         */
        canUserReceiveProduct() {
            return this.checkPermission('invoice_credited_update')
        },

        /**
         * Определяет может ли пользовательредактировать поле "Отказ" у товара счёта.
         */
        canUserRefuseProduct() {
            return this.checkPermission('invoice_refund_update')
        },
    }
}

