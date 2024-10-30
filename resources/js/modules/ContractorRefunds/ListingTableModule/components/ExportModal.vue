<template>
    <modal>
        <div class="p-3 ">
            <span>Что вы хотите выгрузить?</span>
        </div>
        <div class="d-flex  rounded border-top bg-light p-3">
            <button @click="$emit('refundsExport')" class="btn btn-sm btn-primary me-2">
                <font-awesome-icon icon="fa-solid fa-download" />
                Возвраты
            </button>
            <button @click="$emit('refundProductsExport')" class="btn btn-sm btn-primary">
                <font-awesome-icon icon="fa-solid fa-download" />
                Товары возвратов
            </button>
            <button @click="$emit('close')" class="btn btn-sm  btn-light border ms-auto">Отмена</button>
        </div>
    </modal>
</template>
<script>
import modal from '../../../../ui/modals/modal.vue';

export default {
    components: { modal }
}
</script>
