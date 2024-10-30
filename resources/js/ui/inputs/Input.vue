<template>
    <input @input="$emit('update:modelValue', $event.target.value)" :value="modelValue" :type="type" :placeholder="placeholder"
        :class="isInputCheckbox()">
</template>
<script>
export default {
    emits: ['update:modelValue'],
    props: ['modelValue'],
    props: {
        type: {
            type: String,
            default: 'text'
        },
        placeholder: {
            type: String,
            default: 'Введите значение'
        }
    },
    methods: {
        isInputCheckbox() {
            if (this.type == 'checkbox')
                return '';
            return 'form-control';
        }
    }
}
</script>
