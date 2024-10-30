<template>
    <button type="button" class="btn btn-sm me-1">
        <font-awesome-icon :icon="icon" />
        {{ text }}
    </button>
</template>

<script>
    export default{
        props:{
            text: String,
            icon: String
        }
    }
</script>
