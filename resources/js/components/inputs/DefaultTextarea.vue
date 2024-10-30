<template>
    <div class="m-3 d-flex flex-column">
        <label v-if="label" class="text-muted d-flex align-items-center mb-2 ps-1" :class="{ required: required }"
            style="font-size: 13px; width: 30%">
            {{ label }}
        </label>
        <textarea @blur="blur()" :value="modelValue" :required="required" class="form-control" ref="inputRef"
            @input="$emit('update:modelValue', $event.target.value)" v-bind:placeholder="placeholder"
            style="height: 120px; font-size: 13px; max-width: 450px;" :disabled="disabled">
        </textarea>
    </div>
</template>
<style scoped>
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
        label: String,
        placeholder: String,
        required: Boolean,
        ref: String,
        modelValue: [String, Number, Array, null],
        disabled: {
            type: Boolean,
            default: false
        }
    },
    methods:{
        blur(){
            this.$emit('blur')
        },
        getRef(){
            return this.$refs.inputRef;
        }
    },
    emits: ['update:modelValue', 'blur'],
}
</script>