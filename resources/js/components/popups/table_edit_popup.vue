<template>
    <div @click="onOpenPopup()" class="table-edit-popup">
        <span class="popup-text ps-2" :class="{'currency': withCurrency}">
            {{modelValue}} 
        </span>
    </div>
    <div v-show="isShowPopup" >
        <div @click="onClosePopup()" style="position: fixed; cursor: pointer; width: 100vw; height: 100vh; z-index: 1000; top: 0; left: 0;"></div>
        <div class="bg-white border rounded shadow-lg position-absolute p-3 border-1" style="z-index: 1010;">
            <div class="d-flex flex-row">
                <input type="text" class="form-control me-2" v-model="inputValue" style="font-size: 13px;">
                <button type="button" @click="onSave()" class="btn btn-success bg-gradient me-2">
                    <font-awesome-icon icon="fa-solid fa-check" size="sm"/>
                </button>
                <button type="button" @click="onClosePopup()" class="btn btn-light text-danger bg-gradient border border-1">
                    <font-awesome-icon icon="fa-solid fa-xmark"/>
                </button>
            </div>
        </div>
    </div>

</template>
<style>
    .table-edit-popup{
        cursor: pointer;
    }
    .table-edit-popup .popup-text{
        text-decoration: underline dotted;
        color: #6c757d;
    }

    .table-edit-popup .popup-text.currency::after{
        content: '₽';
        padding-left: 3px;
    }
    .table-edit-popup:hover .popup-text{
        color: #0d6efd;
    }
</style>
<script>
export default{
    props:{
        modelValue: String,
        withCurrency: Boolean,
    },
    emits:['update:modelValue'],
    data(){
        return {
            isShowPopup: false,
            inputValue: this.modelValue,
        }
    },
    methods:{
        onOpenPopup(){
            this.isShowPopup = true;
        },
        onClosePopup(){
            this.isShowPopup = false;
        },
        onSave(){
            this.$emit('update:modelValue', this.inputValue);
            this.isShowPopup = false;
        }
    }
}
</script>