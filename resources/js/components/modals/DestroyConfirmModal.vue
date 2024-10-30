<template>
    <DefaultModal :width="'500px'" @close_modal="cancel()" :title="'Подтвердите действие'">
        <div class="d-flex p-3">
            <span>Вы действительно хотите удалить {{ entityName }}?</span>
        </div>
        <div class="d-flex justify-content-end p-2 mb-0 bg-light">
            <button @click="cancel()" class="btn bg-gradient btn-light border m-1">Отмена</button>
            <button @click="confirm()" class="btn btn-danger m-1" >Удалить</button>
        </div>
    </DefaultModal>
</template>

<script>
import DefaultModal from './default_modal.vue';
export default {
    components: { DefaultModal },
    props:{
        entityName:{
            default: null,
            type: String,
        }
    },
    methods:{
        cancel(){
            this.$emit('cancel');
        },
        confirm(){
            this.$emit('confirm');
        }
    }
}
</script>