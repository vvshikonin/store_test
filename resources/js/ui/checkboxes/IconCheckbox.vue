<template>
    <div @click="$emit('update:modelValue', !modelValue)" class="m-2 d-flex justify-content-center align-items-center"
        style="width:20px; height: 20px" :title=title>
        <font-awesome-icon :icon="icon" size="lg" :class="iconActiveClass" />
    </div>
</template>
<script>
export default {
    props: {
        icon: {
            type: String,
            default: 'fa-solid fa-xmark'
        },
        title: {
            type: String,
            default: 'title prop'
        },
        activeClass: {
            type: String,
            default: 'text-primary'
        },
        modelValue: [Boolean, Number],
    },
    emits: ['update:modelValue'],
    computed: {
        iconActiveClass() {
            return [this.modelValue ? this.activeClass : 'text-muted opacity-75'];
        }
    }
}
</script>
