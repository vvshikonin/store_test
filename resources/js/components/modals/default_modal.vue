<template>
    <teleport to="body">
        <div class="d-flex position-fixed top-0 left-0 h-100 w-100" style="z-index: 9999;">
            <div @click="onClose()" class="w-100 h-100" style="position:fixed; top:0; left:0; background-color: #80808066">
            </div>
            <div :style="modalStyle" class="position-fixed d-flex justify-content-center w-100 align-self-center">
                <div class="d-flex flex-column justify-content-center border rounded bg-white modal-window" style="max-height: 85vh;" :style="{ width: width }">
                    <div class="d-flex flex-row align-items-center border-bottom">
                        <h5 class="ps-3">{{ title }}</h5>
                        <div class="ms-auto border-start p-3 ps-4 pe-4 close-button" @click="onClose()">
                            <font-awesome-icon icon="fa-solid fa-xmark" size="lg" />
                        </div>
                    </div>
                    <div style="min-height: 70px; overflow-y: auto;">
                        <slot>Default slot</slot>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>
<style>
.modal-window .close-button {
    color: gray;
    cursor: pointer;
}

.modal-window .close-button:hover {
    color: red;
}
</style>
<script>
export default {
    props: {
        title: {
            type: String,
            default: 'title prop'
        },
        width: {
            type: String,
            default: '1500px'
        },
        modalStyle:{
            type: String,
            default: 'margin-top: -100px;'
        }
    },
    emits: ['close_modal'],
    methods: {
        onClose() {
            this.$emit('close_modal');
        }
    },
    mounted() {
        document.body.style.overflowY = 'hidden';
    },
    unmounted() {
        document.body.style.overflowY = 'auto';
    }
}
</script>
