<template>
    <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon3">{{title}}</span>
        <input type="text" class="form-control" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"  :placeholder="placeholder" >
    </div>
</template>
<script>
export default{
    props: ['modelValue', 'title', 'placeholder'],
    emits: ['update:modelValue'],
}
</script>