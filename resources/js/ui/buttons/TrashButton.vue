<template>
    <button :class="{'btn btn-outline-danger border-0': isAlowed}" @click="$emit('on_click')" type="button">
        <font-awesome-icon icon="fa-solid fa-trash" size="lg"
            class="cursor-pointer" :class="!isAlowed ? 'text-muted' : ''"
            :style="{ 'font-size': iconSize, cursor: !isAlowed ? 'not-allowed' : 'pointer' }" />
    </button>
</template>

<script>
export default {
    props: {
        isAlowed: {
            type: Boolean,
            default: true
        },
        iconSize: {
            type: String,
            default: '16px'
        },
    }
}
</script>
