<template>
    <FormSelect :options="contractors" placeholder="Выбрать поставщика" label="Поставщик" />
</template>

<script>
import { mapGetters } from 'vuex';

import FormSelect from '../../ui/selects/FormSelect.vue';

export default {
    components: { FormSelect },
    props: {
        load: {
            type: Boolean,
            default: true,
        }
    },
    computed:{
        ...mapGetters({ contractors: 'getContractors'}),
    },
    mounted() {
        if(this.load){
            this.$store.dispatch('loadContractorsData');
        }
    }
}
</script>
