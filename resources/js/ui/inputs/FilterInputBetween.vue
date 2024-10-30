<template>
    <div class="mb-2 filter" style="width: 16.6%">
        <div class="d-flex align-items-center">
            <label class="form-label text-muted mb-0 ps-1 pe-2" style="font-size: 13px;">{{ label }}</label>
            <div style="cursor:pointer">
                <font-awesome-icon @click="onChangeMode(1)" :icon="icons.between" size="sm" :class="iconClasses(1)"
                    class="text-primary pe-2" title="Промежуток" />
                <font-awesome-icon @click="onChangeMode(2)" :icon="icons.equal" size="sm" class="pe-2"
                    :class="iconClasses(2)" title="Равно" />
                <font-awesome-icon @click="onChangeMode(3)" :icon="icons.notEqual" size="sm" class="pe-2"
                    :class="iconClasses(3)" title="Не равно" />
            </div>
        </div>
        <div v-if="mode == 1" class="d-flex flex-row align-items-center justify-content-between" style="width:230px;">
            <Input @input="setStart($event.target.value)" :value="values.start" :type="type" :step="step" :placeholder="null"
                style="font-size: 13px; height:30px; width: 110px;" />
            <span class="text-muted">-</span>
            <Input @input="setEnd($event.target.value)" :value="values.end" :type="type" :step="step" :placeholder="null"
                style="font-size: 13px; height:30px; width: 110px" />
        </div>
        <div v-else-if="mode == 2" class="d-flex flex-row align-items-center justify-content-between" style="width:230px;">
            <Input @input="setEqual($event.target.value)" :value="values.equal" :type="type" :step="step" style="font-size: 13px; height:30px" />
        </div>
        <div v-else class="d-flex flex-row align-items-center justify-content-between" style="width:230px;">
            <Input @input="setNotEqual($event.target.value)" :value="values.notEqual" :step="step" :type="type"
                style="font-size: 13px; height:30px" />
        </div>
    </div>
</template>
<script>

import Input from './Input'
import IconsMixin from '../../mixins/fa-icons'

export default {
    components: { Input },
    mixins: [IconsMixin],
    props: {
        label: String,
        placeholder: String,
        type: String,
        step: String,
        modelValue: Object,
    },
    emits: ['update:modelValue'],
    data() {
        return {
            mode: 1,
        }
    },
    methods: {
        iconClasses(iconMode) {
            return { 'text-primary': this.mode === iconMode, 'text-muted': this.mode !== iconMode, }
        },
        onChangeMode(newMode) {
            this.mode = newMode;
            switch (newMode) {
                case 1:
                    this.$emit('update:modelValue', { start: null, end: null });
                    break;
                case 2:
                    this.$emit('update:modelValue', { equal: null });
                    break;
                case 3:
                    this.$emit('update:modelValue', { notEqual: null });
                    break;
            }
        },
        setStart(value) {
            this.values.start = value;
            this.$emit('update:modelValue', this.values);
        },
        setEnd(value) {
            this.values.end = value;
            this.$emit('update:modelValue', this.values);
        },
        setEqual(value) {
            this.values.equal = value;
            this.$emit('update:modelValue', this.values);
        },
        setNotEqual(value) {
            this.values.notEqual = value;
            this.$emit('update:modelValue', this.values);
        }
    },
    computed: {
        values() {
            if (this.modelValue)
                return this.modelValue;
            return {};
        }
    }
}
</script>
