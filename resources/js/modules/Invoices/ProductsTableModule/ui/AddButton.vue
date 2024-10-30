<template>
    <button type="button" class="btn btn-sm btn-light border border-secondary">
        {{ text }}
        <font-awesome-icon class="text-success ms-1" icon="fa-solid fa-plus" />
    </button>
</template>

<script>
export default {
    props:{
        text: String,
    }
}
</script>
