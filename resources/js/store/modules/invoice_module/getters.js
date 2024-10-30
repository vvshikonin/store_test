export default {
    /**
    * Возвращает общее количество оприходованного товара по счёту.
    * @param {object} state
    * @returns
    */
    getReceivedSum: state => state.products.reduce((sum, product) => sum + product.received, 0),

    /**
     * Возвращает список способов оплаты выбранного в счёте юр.лица.
     * @param {object} state
     * @param {object} rootState
     */
    getCurrentPaymentMethods(state, getters, rootState) {
        let legalEntityPaymentMethods = [];
        rootState.paymentMethodModule.paymentMethods.forEach(method => {
            if (method.legal_entity_id == state.legal_entity_id)
                legalEntityPaymentMethods.push(method);
        });
        return legalEntityPaymentMethods;
    },

    /**
     * Возвращает минимальную дату доставку по товарам по полю `planned_delivery_date_from`.
     * @param {object} state
     */
    getMinDeliveryDate(state) {
        if (!state.products.length)
            return null;

        return state.products.reduce((min, product) => {
            if (product.planned_delivery_date_from < min)
                return product.planned_delivery_date_from;
            return min;
        }, state.products[0].planned_delivery_date_from);
    },

    /**
     * Возвращает максимальную дату доставку по товарам по полю `planned_delivery_date_to`.
     * @param {object} state
     */
    getMaxDeliveryDate(state) {
        if (!state.products.length)
            return null;

        return state.products.reduce((max, product) => {
            if (product.planned_delivery_date_to > max)
                return product.planned_delivery_date_to;
            return max;
        }, state.products[0].planned_delivery_date_to);
    },

    /**
     * Возвращает индекс товара в массиве товаров в `state` по переданному ID товара.
     * @param {object} state
     * @returns {function(number): number}
     */
    getProductIndexByProductID: (state) => (productID) => {
        return state.products.findIndex(product => product.product_id == productID);
    },

    /**
     * Возвращает количество ожидаемого товара, по переданному ID товара. 
     * @param {object} state 
     * @param {object} getters 
     * @returns {function(number): number}
     */
    getProductExpected: (state, getters) => (productID) => {
        const index = getters.getProductIndexByProductID(productID);
        const amount = state.products[index].amount;
        const received = state.products[index].received;
        const refused = state.products[index].refused;
        return amount - received - refused;
    },

    /**
     * Возвращает товар по переданному ID товара.
     * @param {object} state 
     * @param {object} getters 
     * @returns {function(number): number}
     */
    getProduct: (state, getters) => (productID) => {
        const index = getters.getProductIndexByProductID(productID);
        if (index !== -1)
            return state.products[index];
    },
    /**
     * Получает товар для редактирования.
     * @param {object} state
     */
    productToEdit: (state) => {
        return state.products.find(product => product.isEditing);
    },

    /**
     * Возвращает сумму счёта.
     * @param {object} state 
     * @returns {number}
     */
    getInvoiceSum(state) {
        return state.products.reduce((sum, product) => {
            const productSum = parseInt(product.amount) * parseFloat(product.price).toFixed(2);
            if (Number.isNaN(productSum))
                return sum += 0;
            else return sum += productSum;
        }, 0)
    },

    /**
     * Возвращает сумму оприходованного товара.
     * @param {object} state 
     * @returns {number}
     */
    getReceivedProductsSum(state) {
        return state.products.reduce((sum, product) => {
            const productSum = parseInt(product.received) * parseFloat(product.price).toFixed(2);
            if (Number.isNaN(productSum))
                return sum += 0;
            else return sum += productSum;
        }, 0)
    },

    /**
     * Возвращает сумму товара от которого отказались.
     * @param {object} state 
     * @returns {number}
     */
    getRefusedProductsSum(state) {
        return state.products.reduce((sum, product) => {
            const productSum = parseInt(product.refused) * parseFloat(product.price).toFixed(2);
            if (Number.isNaN(productSum))
                return sum += 0;
            else return sum += productSum;
        }, 0)
    }
}
