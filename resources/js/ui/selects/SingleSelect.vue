<template>
    <select @change="$emit('update:modelValue', $event.target.value); $emit('change', $event)" :value="modelValue" :required="required" :style="style"
     class="form-select" :disabled="disabled">
        <option v-for="option in options" :value="option.id">{{ option.name }}</option>
    </select>
</template>


<script>
export default {
    props: {
        fontSize: String,
        required: Boolean,
        disabled: Boolean,
        options: {
            type: Array,
            default: [{ id: 'id1', name: 'name1' }]
        },
        modelValue: [String, Number, Array, null],
    },
    computed: {
        style() {
            return {
                'font-size': this.fontSize,
            }
        }
    },
    emits: ['update:modelValue', 'change'],
}
</script>
