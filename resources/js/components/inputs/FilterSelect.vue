<template>
    <div class="mb-2 filter" style="width: 16.6%;">
        <label v-if="label" class="text-muted mb-0 ps-1" style="font-size: 12px;">{{ label }}</label>
        <div class="d-flex" style="width: 230px; position: relative;">
            <select :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" class="form-select" style="height: 30px; font-size: 13px; width: 100%; line-height: 16px;" v-bind:placeholder="placeholder">
                <option v-for="option in options" :value="option.id">{{ option.name }}</option>
            </select>
            <div v-if="modelValue != null" @click="$emit('update:modelValue', null)" class="text-danger d-flex justify-content-center align-items-center" style="position: absolute; cursor:pointer; right: 30px; margin-top: 5px; width:20px; height:20px">
                <font-awesome-icon icon="fa-solid fa-xmark" />
            </div>
            <span v-if="modelValue == null" class="text-muted" style="pointer-events: none; left: 0.75rem; position: absolute; font-size: 13px; margin-top: 5px; z-index: 1" ref="placeholder">{{ placeholder }}</span>
        </div>
    </div>
</template>

<style>
    .filter div select{
        width: 230px;   
    }
</style>

<script>
export default {
    props: {
        label: String,
        placeholder: {
            type:String,
            default: "Выбрать значение"
        },
        type: String,
        options:{
            type: Array,
            default:[{id: 0, name: 'Значение 1'}, {id: 1, name: 'Значение 2'}]
        },
        modelValue: [String, Number, Array, null],
    },
    emits: ['update:modelValue'],
}
</script>