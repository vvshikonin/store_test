<template>
    <div class="textarea-table-popup">
        <textarea class="form-control" style="font-size: 12px;" :style="{'width': width}"
            v-model="value" :placeholder="placeholder" :disabled="disabled"
            @blur="$emit('blur')" maxlength="255"></textarea>
    </div>

</template>
<style>
    .textarea-table-popup{
        position:absolute;
        margin-top: -25px;
        border-color: var(--bs-border-color);
        transition: all 0.2s;
        z-index: 10;
    }
    .textarea-table-popup textarea{
        resize: none;
        width: 200px;
        max-height: 50px;
        transition: all 0.2s;
    }
    .textarea-table-popup:has(textarea:focus){
   
        background-color: white;
        border: 1px var(--bs-border-style) var(--bs-border-color);
        border-radius: 0.375rem;
        padding: 1rem;
        z-index: 100;
        box-shadow: 0 0.125rem 0.25rem rgb(0 0 0 / 15%);

    }
    .textarea-table-popup:has(textarea:focus) textarea{
        resize: vertical; 
        max-height: unset;
        width: 350px!important;
        min-height: 100px;
        resize: none; 
    }
</style>
<script>
    export default{
        props: {
            placeholder: String,
            modelValue: String, 
            disabled: Boolean,
            width: String,
        },
        emits: ['update:modelValue', 'blur'],
        computed: {
            value: {
                get() {
                    return this.modelValue
                },
                set(value) {
                    this.$emit('update:modelValue', value)
                }
            }       
        }
    }
</script>