<template>
    <FormSelect :options="contractors" placeholder="Выбрать юр.лицо" label="Юр.лицо" />
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
        ...mapGetters({ contractors: 'getLegalEntities'}),
    },
    mounted() {
        if(this.load){
            this.$store.dispatch('loadLegalEntities');
        }
    }
}
</script>
