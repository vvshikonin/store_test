<template>
    <form @submit.prevent="$emit('search')" class="w-100">
        <div class="input-group p-2">
            <Input @input="$emit('update:modelValue', $event.target.value)" @blur="$emit('blur')" :value="modelValue"
                :placeholder="placeholder" class="rounded-0" :disabled="disabled"></Input>
            <div class="input-group-append">
                <SearchButton type="submit" :disabled="searchDisabled" class="rounded-0"/>
                <ClearButton @click="$emit('update:modelValue', ''); $emit('clear')" class="rounded-0" />
            </div>
        </div>
    </form>
</template>

<script>
import moduleConfig from '../config';
import UIKit from '../../../../ui/UIKit'

export default {
    mixins: [UIKit],
    props: ["modelValue", "isLoading", "placeholder", "disabled"],
    emits: ["update:modelValue", "search"],
    computed: {
        searchDisabled() {
            return (this.modelValue.trim().length < moduleConfig.minSearchLength) || this.isLoading;
        }
    },
}
</script>
