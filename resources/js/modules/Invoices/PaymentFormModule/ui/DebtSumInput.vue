<template>
    <div class="mt-3 ms-3 d-flex flex-row justify-content-start" style="width: 45%">
        <label class="text-muted d-flex align-items-center mb-0 ps-1 required" for="debt-sum"
            style="font-size: 13px; width: 30%">
            Сумма оплаты долгом
        </label>
        <div class="input-group" style="height: 30px; font-size: 13px; width: 70%; max-width: 413px">
            <span class="input-group-text" style="height: 30px; font-size: 13px">
                {{ sum.priceFormat(true) }}
            </span>
            <span class="input-group-text" style="height: 30px; font-size: 13px">
                /
            </span>
            <input @input="$emit('update:modelValue', $event.target.value)" :value="modelValue" :disabled="disabled"
                :step="0.01" :max="sum" min="0" type="number" id="debt-sum" class="form-control"
                placeholder="Введите сумму оплаты долгом" style="height: 30px; font-size: 13px" required />
        </div>
    </div>
</template>

<script>
export default {
    props: {
        sum: String,
        disabled: Boolean,
        modelValue: [Number, String, null],
    },
    emits: ['update:modelValue'],
};
</script>
