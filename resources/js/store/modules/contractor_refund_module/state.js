export default () => ({
    id: null,
    is_complete: null,
    comment: null,
    delivery_date: null,
    delivery_address: null,
    delivery_status: null,
    invoice: {},
    refund_documents: null,
    contractor_refund_products: [],
    deleted_contractor_refund_products_ids: [],
    files: {
        refund_documents: null,
    },

    isNew: true,
    original: {},
})