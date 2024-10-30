<template>
    <div class="p-3 border-end" :class="conteinerClasses" style="font-size: 14px;">
        <a @click.prevent="click()" href="#" class="text-decoration-none" :class="linkClassess">
            <font-awesome-icon icon="fa-solid fa-download" /> Выгрузить таблицу</a>
    </div>
</template>
<script>
export default {
    props: {
        isLoading: {
            default: false,
            type: Boolean,
        }
    },
    computed: {
        conteinerClasses() {
            return { 'placeholder-glow': this.isLoading }
        },
        linkClassess() {
            return { 'placeholder rounded pe-none user-select-none': this.isLoading }
        }
    },
    methods: {
        click() {
            this.$emit('export');
        }
    }
}
</script>
