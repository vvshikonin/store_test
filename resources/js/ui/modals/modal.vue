<template>
    <teleport to="body">
        <div class="d-flex position-fixed top-0 left-0 h-100 w-100" style="z-index: 9999;">
            <div @click="onClose()" class="w-100 h-100" style="position:fixed; top:0; left:0; background-color: #80808066">
            </div>
            <div class="position-fixed d-flex justify-content-center w-100" style="top: 7%">
                <div class="d-flex flex-column justify-content-center border rounded bg-white modal-window"
                    style="max-height: 85vh;" :style="{ width: width }">
                    <div class="d-flex flex-row align-items-center border-bottom">
                        <h5 class="ps-3">{{ title }}</h5>
                        <div class="ms-auto border-start p-3 ps-4 pe-4 close-button" @click="onClose()">
                            <font-awesome-icon icon="fa-solid fa-xmark" size="lg" />
                        </div>
                    </div>
                    <div style="min-height: 70px;">
                        <slot>Default slot</slot>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: 'title prop'
        },
        width: {
            type: String,
            default: '80%'
        },
        top:{
            type: String,
            default: '5%'
        }
    },
    methods: {
        onClose() {
            this.$emit('close');
        }
    }
}
</script>
