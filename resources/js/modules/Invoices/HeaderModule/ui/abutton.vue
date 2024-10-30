<template>
    <a class="link-light" style="cursor: pointer; font-size: 14px;">
        <font-awesome-icon class="pe-1" :icon="icon" />{{ text }}
    </a>
</template>

<script>
export default {
    props: {
        text: {
            type: String,
            default: 'textProp',
        },
        icon:{
            type: String,
            default: 'fa-solid fa-image',
        }
    }
}
</script>
