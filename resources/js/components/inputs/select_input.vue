<template>
    <div class="m-3  d-flex flex-row justify-content-start align-items-center" style="width: 45%; position: relative">
        <label v-if="label" class="text-muted mb-0 ps-1 d-flex align-items-center" :class="{ required: required }" style="font-size: 13px; width: 30%;">
            {{ label }}
            <RetailCrmLogo v-if="crmSync" style="height: 15px; margin-left: 5px;" title="Это поле синхронизируется с полем в заказе RetailCRM"></RetailCrmLogo>
        </label>
        <div class="d-flex" style="width: 70%; max-width: 413px; position: relative;">
            <select @change="$emit('change')" :value="modelValue" :required="required" @input="$emit('update:modelValue', $event.target.value)"
                class="form-select" style="height: 30px; font-size: 13px; width: 100%;  line-height: 16px;"
                v-bind:placeholder="placeholder" :disabled="disabled">
                <option v-for="option in options" :value="option.id">{{ option.name }}</option>
            </select>
            <div v-if="modelValue != null && !disabled" @click="$emit('update:modelValue', null); $emit('change')" class="text-danger d-flex justify-content-center align-items-center" style="position: absolute; cursor:pointer; right: 30px; margin-top: 5px; width:20px; height:20px">
                <font-awesome-icon icon="fa-solid fa-xmark" />
            </div>
            <span v-if="modelValue == null" class="text-muted" style="pointer-events: none; left: 0.75rem; position: absolute; font-size: 13px; margin-top: 5px; z-index: 1" ref="placeholder">{{ placeholder }}</span>
        </div>
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
        label: String,
        required: Boolean,
        disabled: Boolean,
        placeholder: {
            type: String,
            default: "placeholder prop"
        },
        options: {
            type: Array,
            default: [{id: 'id1', name: 'name1'}]
        },
        modelValue: [String, Number, Array, null],
        crmSync: {
            type: Boolean,
            default: false
        }
    },
    methods: {
    },
    emits: ['update:modelValue', 'change'],
}
</script>
