<template>
    <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon3">Поставщик</span>
        <select  class="form-select" id="inputGroupSelect01" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)">
            <option value="" selected>Выбрать значение</option>
            <option v-if="contractors"  v-for="contractor in contractors" v-bind:value="contractor.id">
                    {{ contractor.name }}
            </option>
        </select>
    </div>
</template>
<script>
import { mapGetters } from 'vuex';
export default{
    props: ['modelValue'],
    emits: ['update:modelValue'],
    computed: {
        ...mapGetters({contractors:'getContractors'}),
    },
    mounted(){
        this.$store.dispatch('loadContractorsData');
    }
}
</script>