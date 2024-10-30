<template>
    <div  class="d-flex flex-column filters">
       <div class="d-flex flex-row w-100">
            <TextInput v-model="selection.filter.number" class="me-3" title="Номер" placeholder="счёта или заказа"></TextInput>
            <ContractorSelect v-model="selection.filter.contractor" class="me-3"></ContractorSelect>
            <NumberBetweenInput v-model:start="selection.filter.sum.start" v-model:end="selection.filter.sum.end" class="me-3" title="Сумма"></NumberBetweenInput>
            <SelectInput v-model="selection.filter.status" class="me-3" title="Статус" :options="{ 0: 'Не сделан', 1: 'Сделан' }"></SelectInput>
            <SelectInput v-model="selection.filter.type" title="Тип возврата" :options="{ 0: 'По счёту', 1: 'По заказу' }"></SelectInput>
       </div>
   </div>
</template>
<script>
import ContractorSelect from '../Other/filter_inputs/contractor_select.vue';
import TextInput from '../Other/filter_inputs/text_input.vue';
import NumberBetweenInput from '../Other/filter_inputs/numbers_between_input.vue'
import DateBetweenInput from '../Other/filter_inputs/date_between_input.vue'
import SelectInput from '../Other/filter_inputs/select_input.vue'
import EmptyPlaceholder from '../Other/filter_inputs/empty.vue'
export default {
   components:{ ContractorSelect, TextInput, NumberBetweenInput, DateBetweenInput, SelectInput, EmptyPlaceholder},
   inject:['selection'],
}
</script>