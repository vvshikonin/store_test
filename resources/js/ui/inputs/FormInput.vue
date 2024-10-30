<template>
    <div class="m-3 d-flex flex-row justify-content-start" style="width: 45%;">
        <label v-if="label" class="text-muted d-flex align-items-center mb-0 ps-1" :class="{ required: required }"
            style="font-size: 13px; width: 30%">{{ label }}</label>
        <input @blur="blur()" @change="$emit('change', $event)" :value="modelValue" v-bind:type="type" :required="required"
            class="form-control" ref="inputRef" @input="$emit('update:modelValue', $event.target.value)" :min="min"
            :max="max" :step="step" style="height: 30px; font-size: 13px; width: 70%; max-width: 413px;"
            :disabled="disabled" :accept="accept" v-bind:placeholder="placeholder" v-bind:autocomplete="autocomplete">
    </div>
</template>
<style>
label.required:after {
    content: '*';
    color: red;
    font-weight: 800;
    font-size: 13px;
    vertical-align: middle;
    padding-left: 3px;
}
</style>
<script>
export default {
    props: {
        autocomplete: String,
        label: String,
        placeholder: String,
        type: String,
        required: Boolean,
        ref: String,
        accept: String,
        step: Number,
        min: Number,
        max: Number,
        modelValue: [String, Number, Array, null],
        disabled: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        blur() {
            this.$emit('blur')
        },
        getRef() {
            return this.$refs.inputRef;
        }
    },
    emits: ['update:modelValue', 'blur', 'change'],
}
</script>
