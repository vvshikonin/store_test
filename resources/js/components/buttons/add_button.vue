<template>

    <button @click="onClick()" class="btn btn-light border border-secondary bg-opacity d-flex align-items-center" style="font-size: 14px;" type="button">
        {{ buttonTitle }} <font-awesome-icon class="ms-1 text-success" icon="fa-solid fa-plus"/>
    </button>

</template>

<script>

export default {
    props: {
        buttonTitle: String,
    },
    methods: {
        onClick() {
            this.$emit('add_element');
        }
    }
}

</script>