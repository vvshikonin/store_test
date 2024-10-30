<template>

<div class="m-3 d-flex flex-row justify-content-start" style="width: 45%">
    <label class="text-muted d-flex align-items-center mb-0 ps-1" style="font-size: 13px; width: 30%;"> {{ title }} </label>
    <div class="custom-checkbox" :class="{ 'active' : modelValue, 'disabled-custom-checkbox' : disabled }" >
        <Checkbox @update:modelValue="$emit('update:modelValue', !modelValue)" style="margin: 0!important; cursor: pointer" class="p-3" :title="title" v-model:modelValue="modelValue" :activeClass="'text-white'" :icon="'fa-check'"></Checkbox>
    </div>
</div>

</template>

<style scoped>

.custom-checkbox {
    border: 1px solid var(--bs-border-color);
    border-radius: 4px;
    background-color: white;
    transition: .2s ease;
}
.disabled-custom-checkbox {
    pointer-events: none;
    cursor: not-allowed;
    background: #e9ecef;
}
.disabled-custom-checkbox.active {
    border-color:#1c4077!important;
    background-color: #1c4077!important;
}
.custom-checkbox.active {
    border-color: #0d6efd;
    background-color: #0d6efd;
    transition: .2s ease;
}

</style>

<script>

import Checkbox from '../checkboxes/IconCheckbox.vue';

export default {
    components: { Checkbox },
    props: {
        disabled: Boolean,
        title: {
            type: String,
            default: 'title prop'
        },
        modelValue: [Boolean, Number],
    },
    emits: ['update:modelValue'],
}

</script>