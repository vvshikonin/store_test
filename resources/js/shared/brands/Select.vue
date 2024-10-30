<template>
    <SingleSelect v-model="modelValue" @change="$emit('update:modelValue', modelValue);" :options="brands"
        v-bind="style">
    </SingleSelect>
</template>

<script>
import SingleSelect from '../../ui/selects/SingleSelect.vue';
import { mapGetters } from 'vuex';

export default {
    components: { SingleSelect },
    props: {
        modelValue: Number,
        fontSize: String,
        load: {
            type: Boolean,
            default: true,
        },
    },
    emits: ['update:modelValue'],
    mounted() {
        if (this.load)
            this.$store.dispatch('loadBrands')
    },
    computed: {
        ...mapGetters({ brands: 'getBrands' }),
        style() {
            return {
                fontSize: this.fontSize
            }
        }
    }
}
</script>
