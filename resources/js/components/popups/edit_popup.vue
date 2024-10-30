<template>

    <div class="table-edit-popup">
        <input v-model="value" class="form-control" :placeholder="placeholder" />
        <button></button>
        <button></button>
    </div>

</template>

<style>
    .table-edit-popup {
        position: absolute;
        
    }
</style>

<script>

export default {
    props: {
        placeholder: String,
        modelValue: String,
    }
}

</script>