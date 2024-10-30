<template>
    <div @click="updateValue" class="m-2 d-flex justify-content-center align-items-center"
        style="width:20px; height: 20px" :title="title">
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
        modelValue: {
            type: Number,
            default: 0, // 0 - без фильтра/неактивно, 1 - активно, 2 - третье состояние (для has_maintained_balance)
        },
        isTriState: {
            type: Boolean,
            default: false // По умолчанию компонент работает в двухстадийном режиме
        },
    },
    emits: ['update:modelValue'],
    methods: {
        updateValue() {
            let newValue = this.modelValue + 1;
            if (this.isTriState) {
                if (newValue > 2) newValue = 0;
            } else {
                newValue = newValue % 2;
            }
            this.$emit('update:modelValue', newValue);
        },
    },

    computed: {
        iconActiveClass() {
            switch (this.modelValue) {
                case 1:
                    return this.activeClass; // Первое состояние (Есть поддерживаемый остаток)
                case 2:
                    return 'text-danger'; // Третье состояние (Нет поддерживаемого остатка)
                default:
                    return 'text-muted opacity-75'; // По умолчанию (Без фильтра)
            }
        },
    },

}
</script>
