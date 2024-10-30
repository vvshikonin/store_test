<template>
    <template v-if="content">
        <strong class="fw-bolder">{{ content }}</strong>
    </template>

</template>
<script>
export default{
    props: {
        text: String,
        content: String,
    },
}

</script>
