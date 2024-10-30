import { contractorRefundAPI } from "../../../api/contractor_refund";
import { makeFormData } from "../../../utils/objects";

export default {
    async load({ commit }, id) {
        const res = await contractorRefundAPI.show(id);
        commit('initState', res.data.data);
    },

    async save({ commit, state }) {
        const data = {
            id: state.id,
            is_complete: state.is_complete,
            comment: state.comment,
            delivery_date: state.delivery_date,
            delivery_address: state.delivery_address,
            delivery_status: state.delivery_status,

        }

        const newProducts = [];
        state.contractor_refund_products.forEach(product => {
            if(product.id === 'new'){
                const newProduct = {
                    id: 'new',
                    amount: product.amount,
                    invoice_product_id: product.invoice_product_id,
                    stocks: product.contractor_refund_stocks
                };

                newProducts.push(newProduct);
            }
        });

        if (state.contractor_refund_products.length)
            data.contractor_refund_products = newProducts;

        if (state.deleted_contractor_refund_products_ids.length)
            data.deleted_contractor_refund_products_ids = state.deleted_contractor_refund_products_ids;

        if (state.files.refund_documents !== null)
            data.refund_documents = state.files.refund_documents;

        const res = await contractorRefundAPI.update(makeFormData(data, true), state.id);
        commit('initState', res.data.data);
    },
    async delete({ state }) {
        await contractorRefundAPI.destroy(state.id);
    }
}