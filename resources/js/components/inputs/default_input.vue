<template>
    <div class="m-3 d-flex flex-row justify-content-start" style="width: 45%;">
        <label v-if="label" class="text-muted d-flex align-items-center mb-0 ps-1" :class="{ required: required }" style="font-size: 13px; width: 30%">
            {{ label }}
            <RetailCrmLogo v-if="crmSync" style="height: 15px; margin-left: 5px;" title="Это поле синхронизируется с полем в заказе RetailCRM"></RetailCrmLogo>
        </label>
        <input @blur="blur()" @change="$emit('change', $event)" :value="modelValue" v-bind:type="type" :required="required" :class="isInputCheckbox()" ref="inputRef"
            @input="$emit('update:modelValue', $event.target.value)" :min="min" :max="max" :step="step" style="height: 30px; font-size: 13px; width: 70%; max-width: 413px;" :disabled="disabled"
            :accept="accept" v-bind:placeholder="placeholder" v-bind:autocomplete="autocomplete">
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
import RetailCrmLogo from '../../components/Other/CRM_logo.vue';

export default {
    components: { RetailCrmLogo },
    props: {
        autocomplete: String,
        label: String,
        placeholder: String,
        type: String,
        required: Boolean,
        ref: String,
        accept: String,
        step: [String, Number, null],
        min: [String, Number, null],
        max: [String, Number, null],
        modelValue: [String, Number, Array, null],
        disabled: {
            type: Boolean,
            default: false
        },
        crmSync: {
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
        },
        isInputCheckbox() {
            if (this.type == 'checkbox')
                return '';
            return 'form-control';
        }
    },
    emits: ['update:modelValue', 'blur', 'change'],
}
</script>