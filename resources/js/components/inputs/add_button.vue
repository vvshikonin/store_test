<template>
    <button style="font-size: 14px;" type="button" class="ms-3 btn btn-light border border-secondary bg-opacity" @click="$emit('on_click')">{{
        label }}<font-awesome-icon class="ms-1 text-success" icon="fa-solid fa-plus" /></button>
</template>
<script>
export default {
    props: {
        label: String
    }
}
</script>