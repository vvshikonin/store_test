export default {
    initState(state, refund) {
        Object.assign(state, refund);
        Object.assign(state.original, refund);

        if (refund.refund_documents)
            state.refund_documents = 'storage/' + refund.refund_documents;

        state.isNew = false;
        state.files = {
            refund_documents: null,
        };
    },

    setIsComplete(state, value) {
        state.is_complete = parseInt(value);
    },

    setIsComment(state, value) {
        state.comment = value;
    },

    setIsDeliveryDate(state, value) {
        state.delivery_date = value;
    },

    setIsDeliveryAddress(state, value) {
        state.delivery_address = value;
    },

    setIsDeliveryStatus(state, value) {
        state.delivery_status = value;
    },

    setRefundDocuments(state, file) {
        state.files.refund_documents = file;
    },
}