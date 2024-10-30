import moment from "moment";

export default {
    /**
    * Инициализирует 'state'.
    * @param {object} state
    * @param {object} invoice
    */
    initState(state, invoice) {
        Object.assign(state, invoice);
        Object.assign(state.original, invoice);
        
        if (invoice.invoice_files === null)
            state.invoice_files = [];
            // state.invoice_files = invoice.invoice_files;

        if (invoice.payment_files  === null)
            state.payment_files = [];
            // state.receipt_file = invoice.payment_files;

        state.isNew = false;
        state.new_products = [];
        state.deleted_products = [];
        state.files = {
            new_invoice_files:[],
            deleted_invoice_files: [],
            new_payment_files:[],
            deleted_payment_files: [],
        };
    },

    initRefunds(state, refunds) {
        state.refunds = refunds;
    },

    setAsNew(state) {
        state.id = null;
        state.status = null;
        state.status_set_at = null;
        state.number = null;
        state.contractor_id = null;
        state.date = moment().format('YYYY-MM-DD');
        state.comment = null;
        state.legal_entity_id = null;
        state.invoice_files = [];
        state.payment_method_id = null;
        state.payment_status = 0;
        state.payment_confirm = 0;
        state.payment_date = null;
        state.payment_order_date = null;
        state.debt_payment = 0;
        state.payment_files = [];
        state.delivery_type = null;
        state.products = [];
        state.is_edo = false;
        state.new_products = [];
        state.deleted_products = [];
        state.transactions = [];
        state.created_at = null;
        state.updated_at = null;
        state.creator = {};
        state.updater = {};
        state.files = {
            new_invoice_files:[],
            deleted_invoice_files: [],
            new_payment_files:[],
            deleted_payment_files: [],
        }

        state.isNew = true;
        state.original = {};

    },

    /**
     * Преобразует 'state' для копирования.
     * @param {object} state
     */
    setAsCopy(state) {
        state.isNew = true;
        // state.id = null;
        state.number = state.number + " копия";
        state.status = null;
        state.status_set_at = null;
        state.date = moment().format('YYYY-MM-DD');
        state.invoice_files = [];
        state.payment_status = 0;
        state.payment_date = null;
        state.payment_order_date = null;
        state.payment_confirm = 0;
        state.payment_files = [];
        state.debt_payment = 0;
        state.transactions = [];
        state.created_at = null;
        state.updated_at = null;
        state.creator = {};
        state.updater = {};
        state.original = {};
        state.files = {
            new_invoice_files:[],
            deleted_invoice_files: [],
            new_payment_files:[],
            deleted_payment_files: [],
        };
    },

    initRefundAvailableProducts(state, availableProducts) {
        availableProducts.forEach(product =>
            product.stocks.forEach(stock =>
                stock.refund = 0
            )
        );
        state.refundAvailableProducts = availableProducts;
    },

    setNumber(state, value) {
        state.number = value;
    },
    setContractorID(state, value) {
        state.contractor_id = parseInt(value) || null;
    },
    setDate(state, value) {
        state.date = value;
    },
    setComment(state, value) {
        state.comment = value;
    },
    setLegalEntityID(state, value) {
        state.legal_entity_id = parseInt(value) || null;
    },
    setInvoiceFile(state, file) {
        state.files.invoice = file;
    },
    addNewInvoiceFile(state, file) {
        state.files.new_invoice_files.push(file);
    },
    addDeletedInvoiceFile(state, file) {
        state.files.deleted_invoice_files.push(file);
    },
    addNewPaymentFile(state, file) {
        state.files.new_payment_files.push(file);
    },
    addDeletedPaymentFile(state, file) {
        state.files.deleted_payment_files.push(file);
    },
    setPaymentMethodID(state, value) {
        state.payment_method_id = parseInt(value) || null;
    },
    setPaymentStatus(state, value) {
        state.payment_status = parseInt(value);
        state.payment_date = state.payment_status == 1 ? moment().format('YYYY-MM-DD') : null
    },
    setPaymentConfirm(state, value) {
        state.payment_confirm = parseInt(value) || null;
    },
    setPaymentDate(state, value) {
        state.payment_date = value;
    },
    setPaymentOrderDate(state, value) {
        state.payment_order_date = value;
    },
    setDebtPayment(state, value) {
        state.debt_payment = parseFloat(value) || 0;
    },
    setReceiptFile(state, file) {
        state.files.receipt = file;
    },
    setProductsDeliveryDateFrom(state, value) {
        state.products.forEach(product => product.planned_delivery_date_from = value)
    },
    setProductsDeliveryDateTo(state, value) {
        state.products.forEach(product => product.planned_delivery_date_to = value)
    },
    setDeliveryType(state, value) {
        state.delivery_type = parseInt(value);
    },

    /**
     * Устанавливает всё количество ожидаемого, как оприходованное.
     * @param {object} state
     * @param {number} productID
     */
    receiveExpectedProductByID(state, productID) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1) {
            const amount = state.products[index].amount;
            const refused = state.products[index].refused;
            state.products[index].received = amount - refused;
        }
    },

    /**
     * Устанавливает всё количество ожидаемого, как отказ.
     * @param {object} state
     * @param {number} productID
     */
    refuseExpectedProductByID(state, productID) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1) {
            const amount = state.products[index].amount;
            const received = state.products[index].received;
            state.products[index].refused = amount - received;
        }
    },

    setProductBrandByID(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1)
            state.products[index].brand_id = parseInt(value);
    },

    setProductAmountByID(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1)
            state.products[index].amount = parseInt(value);
    },

    setProductPriceByID(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1)
            state.products[index].price = parseFloat(value).toFixed(2);
    },

    setProductReceivedByID(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1) {
            state.products[index].received = parseInt(value);
        }
    },

    setProductRefusedByID(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1)
            state.products[index].refused = parseInt(value);
    },

    setProductDeliveryDateFromByID(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1)
            state.products[index].planned_delivery_date_from = value
    },

    setProductDeliveryTypeByID(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1)
            state.products[index].delivery_type = value
    },

    setProductDeliveryDateToByID(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1)
            state.products[index].planned_delivery_date_to = value
    },

    addProduct(state, product) {
        const newProduct = {
            id: 'new',
            product_id: product.product_id,
            sku: product.sku,
            product_name: product.name,
            brand_id: product.brand_id,
            amount: product.amount,
            price: product.price,
            received: 0,
            refused: 0,
        }
        state.products.push(newProduct);
    },

    /**
     * Обновляет данные о товаре по ID продукта в счёте.
     * @param {object} state
     * @param {object} updatedProduct - Обновленные данные товара (ID, SKU, название, бренд и т.д.).
     */
    updateProductByID(state, updatedProduct) {
        const index = getProductIndexByProductID(state, updatedProduct.product_id);
        if (index !== -1) {
            // Обновляем только SKU, название и бренд, не изменяя количество и цену
            state.products[index].sku = updatedProduct.sku;
            state.products[index].product_name = updatedProduct.product_name;
            state.products[index].brand_id = updatedProduct.brand_id;

            // Разблокируем BrandSelect, если необходимо
            state.products[index].isEditing = false;
            delete state.products[index].editSKU;
            delete state.products[index].product_name_input; // Поле для ввода названия
        }
    },

    deleteProduct(state, productID) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1) {
            state.deleted_products.push(state.products[index].id);
            state.products.splice(index, 1);
        }

    },
    /**
     * Устанавливает товар для редактирования.
     * @param {object} state
     * @param {object} payload - { productID, newSKU }
     */
    setProductToEdit(state, { productID, newSKU }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1) {
            state.products[index].isEditing = true;
            state.products[index].editSKU = newSKU;
            state.products[index].isNewProduct = false; // Скрыть дополнительные поля по умолчанию
            state.products[index].product_name_input = ''; // Очистить поле ввода названия
        }
    },
    /**
     * Обновляет данные товара после поиска по SKU.
     * @param {object} state
     * @param {object} payload - { product_id, sku, product_name, brand_id, exists }
     */
    updateProductAfterSearch(state, { product_id, sku, product_name, brand_id, exists }) {
        const index = getProductIndexByProductID(state, product_id);
        if (index !== -1) {
            state.products[index].sku = sku;
            console.log(state.products[index]);
            if (exists) {
                state.products[index].product_name = product_name;
                state.products[index].brand_id = brand_id;
                state.products[index].isNewProduct = false;
            } else {
                state.products[index].isNewProduct = true;
                state.products[index].product_name_input = ''; // Инициализируем поле ввода
                state.products[index].brand_id = null; // Сбросить бренд
            }
        }
    },

    /**
     * Завершает редактирование товара после сохранения или отмены.
     * @param {object} state
     * @param {number} productID
     */
    finishProductEdit(state, productID) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1) {
            state.products[index].isEditing = false;
            delete state.products[index].editSKU;
            delete state.products[index].product_name_input;
        }
    },

    /**
     * Обновляет поле для ввода названия товара.
     * @param {object} state
     * @param {object} payload - { productID, value }
     */
    setProductNameInput(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1) {
            state.products[index].product_name_input = value;
        }
    },

    /**
     * Обновляет бренд товара.
     * @param {object} state
     * @param {object} payload - { productID, value }
     */
    setProductBrand(state, { productID, value }) {
        const index = getProductIndexByProductID(state, productID);
        if (index !== -1) {
            state.products[index].brand_id = parseInt(value) || null;
        }
    },
    /**
     * Добавляет созданный новый продукт в список.
     * @param {object} state
     * @param {object} payload - { product }
     */
    addNewProduct(state, { product }) {
        state.products.push({
            product_id: product.product_id,
            sku: product.sku,
            product_name: product.name,
            brand_id: product.brand_id,
            isEditing: false,
            editSKU: null,
            isNewProduct: false,
            product_name_input: null,
        });
    },
    /**
     * Добавляет новый InvoiceProduct.
     * @param {object} state
     * @param {object} payload - { invoiceProduct }
     */
    addInvoiceProduct(state, { invoiceProduct }) {
        // Логика добавления InvoiceProduct в список
        // Например, можно обновить список продуктов или другие данные
    },
};

function getProductIndexByProductID(state, productID) {
    return state.products.findIndex(product => product.product_id == productID);
}
