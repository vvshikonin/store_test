<template>
    <div class="m-3 d-flex flex-row justify-content-start align-items-center" style="width: 45%; position: relative">
        <label class="text-muted mb-0 ps-1 d-flex align-items-center" :class="{ required: required }"
            style="font-size: 13px; width: 30%;"> {{ label }} <RetailCrmLogo v-if="crmSync"
                style="height: 15px; margin-left: 5px;" title="Это поле синхронизируется с полем в заказе RetailCRM">
            </RetailCrmLogo>
        </label>
        <div class="d-flex" style="width: 70%; max-width: 413px; position: relative;">
            <vSelect :options="options" label="name" v-model="selected" :reduce="option => option.id" :disabled="disabled"
                style="width: 100%;"></vSelect>
        </div>
    </div>
</template>


<style scoped>
label.required::after {
    content: '*';
    color: red;
    font-weight: 800;
    font-size: 13px;
    vertical-align: middle;
    padding-left: 3px;
}
</style>


<script>
import { defineComponent } from 'vue';
import RetailCrmLogo from '../../components/Other/CRM_logo.vue';
import vSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';

export default {
    data() {
        return {
            selected: null,
        }
    },
    components: {
        RetailCrmLogo,
        vSelect
    },
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
            default: () => [{ id: 'id1', name: 'name1' }]
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
    mounted() {
        this.selected = this.modelValue
    },
    watch: {
        'selected': function (newValue, oldValue) {
            this.$emit('update:modelValue', newValue)
        },
        'options': function (newValue, oldValue) {
            const optionExists = this.options.some(option => option.id == newValue);
            if (!optionExists) {
                this.selected = null;
            }
        }
    }
};

</script>
