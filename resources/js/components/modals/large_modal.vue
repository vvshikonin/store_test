<template>
    <teleport to="body">
        <div @click="onClose()" class="position-fixed top-0 left-0 w-100 h-100 " style="z-index: 9998; background-color: #80808066">
        </div>
        <div class="position-fixed" style="z-index: 9999; top: 50px; left: 50px; right: 50px; bottom: 50px;">
            <div class="d-flex flex-column shadow justify-content-start border rounded bg-white modal-window h-100 w-100" style="min-width: 300px; min-height: 300px;">
                <div class="d-flex flex-row align-items-center" style="height: 57px;">
                    <h5 class="ps-3">{{ title }}</h5>
                    <div class="ms-auto border-start p-3 ps-4 pe-4 close-button" @click="onClose()">
                        <font-awesome-icon icon="fa-solid fa-xmark" size="lg" />
                    </div>
                </div>
                <div class="w-100" style="height: calc(100% - 57px);">
                    <slot>Default slot</slot>
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