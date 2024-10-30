import { invoiceAPI } from "../../../api/invoice_api";
import { productAPI } from "../../../api/products_api";
import { makeFormData } from "../../../utils/objects";
import { contractorRefundAPI } from "../../../api/contractor_refund";
export default {
    /**
    * Загружает счёт по `ID` и записивает в `state`.
    * @param {object}
    * @param {number} id
    */
    async load({ commit }, id) {
        const res = await invoiceAPI.show(id);
        commit('initState', res.data.data);
    },

    /**
     * Отправляет запрос на создание счёта по данным из `state` и перезаписывает `state` данными из ответа.
     * @param {object}
     */
    async create({ commit, state }) {
        const data = {
            number: state.number,
            contractor_id: state.contractor_id,
            date: state.date,
            comment: state.comment,
            legal_entity_id: state.legal_entity_id,
            payment_method_id: state.payment_method_id,
            payment_status: state.payment_status,
            payment_confirm: state.payment_confirm,
            payment_date: state.payment_date,
            payment_order_date: state.payment_order_date,
            delivery_type: state.delivery_type,
            debt_payment: state.debt_payment,
            new_products: state.products,
            is_edo: state.is_edo,
        };

        const requestData = makeFormData(data);

        state.files.new_invoice_files.forEach(function (file, index) {
            requestData.append(`new_invoice_files[${index}]`, file);
        })

        state.files.new_payment_files.forEach(function (file, index) {
            requestData.append(`new_payment_files[${index}]`, file);
        })

        const res = await invoiceAPI.store(requestData);
        commit('initState', res.data.data);
    },

    /**
     * Отправляет запрос в формате `FormData` на изменение данных счёта по данным из `state` и перезаписывает `state` данными из ответа.
     * @param {object}
     */
    async save({ commit, state }) {
        const products = state.products.filter(product => product.id !== 'new');
        const newProducts = state.products.filter(product => product.id === 'new');
        const deletedProducts = state.deleted_products.filter(id => id !== 'new');

        const data = {
            number: state.number,
            contractor_id: state.contractor_id,
            date: state.date,
            comment: state.comment,
            legal_entity_id: state.legal_entity_id,
            payment_method_id: state.payment_method_id,
            payment_status: state.payment_status,
            payment_confirm: state.payment_confirm,
            payment_date: state.payment_date,
            payment_order_date: state.payment_order_date,
            debt_payment: state.debt_payment,
            delivery_type: state.delivery_type,
            products: products,
            is_edo: state.is_edo,
        };

        if (newProducts.length)
            data.new_products = newProducts;

        if (state.deleted_products.length)
            data.deleted_products = deletedProducts;

        if (state.files.receipt !== null)
            data.receiptFile = state.files.receipt;

        const requestData = makeFormData(data, true);

        state.files.new_invoice_files.forEach(function (file, index) {
            requestData.append(`new_invoice_files[${index}]`, file);
        })

        state.files.deleted_invoice_files.forEach(function (file, index) {
            requestData.append(`deleted_invoice_files[${index}]`, file);
        })

        state.files.new_payment_files.forEach(function (file, index) {
            requestData.append(`new_payment_files[${index}]`, file);
        })

        state.files.deleted_payment_files.forEach(function (file, index) {
            requestData.append(`deleted_payment_files[${index}]`, file);
        })

        const res = await invoiceAPI.update(state.id, requestData);
        commit('initState', res.data.data);
    },

    async delete({ state }) {
        await invoiceAPI.destroy(state.id);
    },

    async updateProductBrand({ commit }, { productID, brandID }) {
        const res = await productAPI.update({ product: { id: productID, brand_id: brandID } });
        if (res.status == 200)
            commit('setProductBrandByID', { productID, value: brandID });
    },

    /**
     * Ищет товар по SKU и заменяет данные товара в счете, если он найден.
     * Если товар не найден, создаёт новый товар и заменяет им неоприходованный товар в счёте.
     * @param {object} context
     * @param {object} payload - Данные товара для поиска (productID, sku).
     */
    async replaceProductBySKU({ commit }, { productID, sku }) {
        try {
            // Ищем товар по SKU
            const res = await productAPI.bulkSearch([sku]);
            const productData = res.data[sku];

            if (productData) {
                // Если товар найден, обновляем его данные в счете
                commit('updateProductByID', {
                    product_id: productID,
                    sku: productData.main_sku,
                    name: productData.name,
                    brand_id: productData.brand_id
                });
            } else {
                // Если товар не найден, создаём новый товар
                const newProduct = {
                    sku,
                    name: 'Новое название', // пользователь должен ввести
                    brand_id: null, // пользователь должен выбрать бренд
                };
                const createdProduct = await productAPI.create(newProduct);
                // Обновляем товар в счете новым созданным товаром
                commit('updateProductByID', {
                    product_id: productID,
                    sku: createdProduct.data.main_sku,
                    name: createdProduct.data.name,
                    brand_id: createdProduct.data.brand_id
                });
            }
        } catch (error) {
            console.error('Ошибка замены товара:', error);
        }
    },
    async loadRefundAvailableProducts({ commit, state }) {
        const res = await invoiceAPI.availableForRefund(state.id);
        commit('initRefundAvailableProducts', res.data.data);
    },

    async loadRefunds({ commit, state }) {
        const res = await invoiceAPI.refunds(state.id);
        commit('initRefunds', res.data);
    },

    async creatContractorRefund({ state }) {

        const newRefund = {
            invoice_id: state.id,
            products: [],
            created_by: 1,
            updated_by: 1,
        };

        state.refundAvailableProducts.forEach(product => {
            const refundAmount = product.stocks.reduce((sum, stock) => +sum + +stock.refund, 0);
            if (refundAmount > 0) {
                const newRefundProduct = {
                    invoice_product_id: product.invoice_product_id,
                    amount: refundAmount,
                    stocks: [],
                }

                product.stocks.forEach(stock => {
                    if (stock.refund > 0)
                        newRefundProduct.stocks.push({
                            stock_id: stock.id,
                            amount: stock.refund
                        })
                })

                newRefund.products.push(newRefundProduct);
            }
        });

        contractorRefundAPI.store(newRefund);
    },

    /**
     * Устанавливает всё количество ожидаемого как оприходованное по переданным `product_id`.
     * @param {object}
     * @param {number[]} productsIDs
     */
    receiveExpectedProductsByIDs({ commit }, productsIDs) {
        productsIDs.forEach(productID => commit('receiveExpectedProductByID', productID));
    },

    /**
     * Устанавливает всё количество ожидаемого как отказ по переданным `product_id`.
     * @param {object}
     * @param {number[]} productsIDs
     */
    refuseExpectedProductsByIDs({ commit }, productsIDs) {
        productsIDs.forEach(productID => commit('refuseExpectedProductByID', productID));
    },
    /**
     * Ищет продукт по SKU.
     * @param {object} context
     * @param {object} payload - { productID, newSKU }
     */
    async searchProductBySKU({ commit }, { productID, newSKU }) {
        try {
            const res = await productAPI.bulkSearch([newSKU]);
            const productData = res.data[newSKU];

            if (productData) {
                // Продукт найден
                commit('updateProductAfterSearch', {
                    product_id: productID,
                    sku: productData.sku,
                    product_name: productData.name,
                    brand_id: productData.brand_id,
                    exists: true
                });
            } else {
                // Продукт не найден
                commit('updateProductAfterSearch', {
                    id: 'new',
                    product_id: 'new',
                    sku: newSKU,
                    product_name: '',
                    brand_id: null,
                    exists: false
                });
            }
        } catch (error) {
            console.error('Ошибка при поиске продукта по SKU:', error);
            // Можно добавить уведомление пользователю об ошибке
            throw error;
        }
    },

    /**
     * Подтверждает редактирование продукта.
     * @param {object} context
     * @param {object} payload - { productID }
     */
    async confirmEditProduct({ state, commit }, { productID }) {
        const product = state.products.find(p => p.product_id === productID);
        if (!product) return;

        if (product.isNewProduct) {
            // Если продукт не найден, создаём новый
            try {
                const newProductData = {
                    sku: product.sku,
                    name: product.product_name_input,
                    brand_id: product.brand_id,
                    // Добавьте другие необходимые поля
                };
                const createdProduct = await productAPI.create(newProductData);

                // Создаём InvoiceProduct
                const invoiceProductData = {
                    invoice_id: state.invoice.id,
                    product_id: createdProduct.data.id,
                    amount: 1, // Или другое значение
                    price: 0.00, // Или другое значение
                };
                const createdInvoiceProduct = await invoiceProductAPI.create(invoiceProductData);

                // Обновляем состояние
                commit('addNewProduct', { product: createdProduct.data });
                commit('addInvoiceProduct', { invoiceProduct: createdInvoiceProduct.data });
                commit('finishEditProduct', productID);
            } catch (error) {
                console.error('Ошибка при создании нового продукта или InvoiceProduct:', error);
                // Можно добавить уведомление пользователю об ошибке
                throw error;
            }
        } else {
            // Если продукт найден, создаём InvoiceProduct или обновляем существующий
            try {
                const invoiceProductData = {
                    invoice_id: state.invoice.id,
                    product_id: product.product_id,
                    amount: 1, // Или другое значение
                    price: product.price, // Или другое значение
                };
                const createdInvoiceProduct = await invoiceProductAPI.create(invoiceProductData);

                // Обновляем состояние (при необходимости)
                commit('addInvoiceProduct', { invoiceProduct: createdInvoiceProduct.data });
                commit('finishEditProduct', productID);
            } catch (error) {
                console.error('Ошибка при создании InvoiceProduct:', error);
                // Можно добавить уведомление пользователю об ошибке
                throw error;
            }
        }
    },
}
