<template>
    <div class="d-flex flex-row">
        <TextInput v-model="selection.filter.product"  title="Товар" placeholder="Артикул или название" class="me-3"></TextInput>
        <NumberBetweenInput v-model:start="selection.filter.amount.start" v-model:end="selection.filter.amount.end" title="Количество" class="me-3"></NumberBetweenInput>
        <NumberBetweenInput v-model:start="selection.filter.price.start" v-model:end="selection.filter.price.end" title="Цена" class="me-3"></NumberBetweenInput>
        <ContractorSelect v-model="selection.filter.contractor_id" class="me-3"></ContractorSelect>
        <SelectInput v-model="selection.filter.refund_type" title="Тип возврата" :options="{ 0: 'На склад', 1: 'Поставщику' }"></SelectInput>
    </div>
    <div class="d-flex flex-row">
        <TextInput v-model="selection.filter.order_number" title="Заказ" placeholder="Номер заказа" class="me-3"></TextInput>
        <TextInput v-model="selection.filter.comment" title="Комментарий" class="me-3"></TextInput>
        <DateBetweenInput v-model:start="selection.filter.created_at.start" v-model:end="selection.filter.created_at.end" title="Создан" class="me-3"></DateBetweenInput>
        <DateBetweenInput v-model:start="selection.filter.completed_at.start" v-model:end="selection.filter.completed_at.end" title="Завершен" class="me-3"></DateBetweenInput>
        <EmptyPlaceholder></EmptyPlaceholder>
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