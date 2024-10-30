<template>
    <div class="d-flex flex-row">
        <DateBetweenInput v-model:start="selection.filter.date.start" v-model:end="selection.filter.date.end" title="Создан" class="me-3"></DateBetweenInput>
        <TextInput v-model="selection.filter.order_number" title="Заказ" placeholder="Номер заказа" class="me-3"></TextInput>
        <TextInput v-model="selection.filter.product"  title="Товар" placeholder="Артикул или название" class="me-3"></TextInput>
        <ContractorSelect v-model="selection.filter.contractor_id" class="me-3"></ContractorSelect>
        <NumberBetweenInput v-model:start="selection.filter.price.start" v-model:end="selection.filter.price.end" title="Цена" ></NumberBetweenInput>
    </div>
    <div class="d-flex flex-row">
        <NumberBetweenInput v-model:start="selection.filter.amount.start" v-model:end="selection.filter.amount.end" title="Количество" class="me-3"></NumberBetweenInput>
        <NumberBetweenInput v-model:start="selection.filter.summ.start" v-model:end="selection.filter.summ.end" title="Сумма" class="me-3"></NumberBetweenInput>
        <SelectInput v-model="selection.filter.sheduled_status" title="Запланирован" :options="{ 0: 'Нет', 1: 'Да' }" class="me-3"></SelectInput>
        <TextInput v-model="selection.filter.comment" title="Комментарий" class="me-3"></TextInput>
        <SelectInput v-model="selection.filter.refund_status" title="Возврат ДС" :options="{ 0: 'Нет', 1: 'Да' }"></SelectInput>
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
    inject:{
        selection:{
            from: 'selection'
        },
    },
}
</script>