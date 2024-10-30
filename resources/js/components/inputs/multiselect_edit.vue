<template>
    <div class="mb-3">
        <label for="multiselect" class="form-label">{{ label }}</label>
        <select id="multiselect" class="form-select" :value="value" @change="onChange" multiple>
            <option v-for="option in options" :key="option.value" :value="option.value">
                {{ option.text }}
            </option>
        </select>
    </div>
</template>

<script>
export default {
    props: {
        label: String,
        options: Array,
        value: Array
    },
    methods: {
        onChange(event) {
            const selectedOptions = Array.from(event.target.options)
                .filter(o => o.selected)
                .map(o => o.value);
            console.log("Выбранные опции:", selectedOptions); // Временный вывод для отладки
            this.$emit('update:value', selectedOptions);
        }
    }
};

</script>
