export default {
    computed: {

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
         * Определяет, может ли пользователь редактировать возврат поставщику.
         */
        canUserUpdateContractorRefund() {
            return this.checkPermission('contractor_refund_update');
        },

        /**
         * Определяет, может ли пользователь удалять возврат поставщику.
         */
        canUserDeleteContractorRefund() {
            return this.checkPermission('contractor_refund_delete');
        }

    }
}