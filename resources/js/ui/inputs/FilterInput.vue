<template>
    <div class="mb-2 filter" style="width: 16.6%;">
        <label v-if="label" for="exampleFormControlInput1" class="form-label text-muted mb-0 ps-1"
            style="font-size: 13px;">{{ label }}</label>
        <input :value="modelValue" v-bind:type="type" class="form-control"
            @input="$emit('update:modelValue', $event.target.value)" style="height: 30px; font-size: 13px; width:230px"
            v-bind:placeholder="placeholder">
    </div>
</template>

<script>
export default {
    props: {
        label: String,
        placeholder: String,
        type: String,

        modelValue: String,
    },
    emits: ['update:modelValue'],
}
</script>
