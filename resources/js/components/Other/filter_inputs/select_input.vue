<template>
    <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon3">{{title}}</span>
        <select  class="form-select" id="inputGroupSelect01" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)">
            <option value="" selected>Выбрать значение</option>
            <option  v-for="(option, index) in options" v-bind:value="index">
                    {{option}}
            </option>
        </select>
    </div>
</template>
<script>
export default{
    props: ['title','modelValue', 'options'],
    emits: ['update:modelValue'],
}
</script>