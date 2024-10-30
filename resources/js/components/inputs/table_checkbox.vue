<template>
    <div class="form-check d-flex justify-content-center">
        <input @click.stop="none"  :disabled="disabled" class="form-check-input" type="checkbox" v-model="value" :indeterminate="checkIndeterminate()">
    </div>
</template>
<script>
    export default{
        props: {
            modelValue: Number | String,
            disabled:Boolean
        },
        emits: ['update:modelValue'],
        methods:{
            checkIndeterminate: function(){
                if(this.modelValue == 'indeterminate'){
                    return true;
                }
                return false;
            }
        },
        computed: {
            value: {
                get() {
                    if(this.modelValue == '1' || this.modelValue == 'true'){
                        return true;
                    }
                },
                set(value) {
                    this.$emit('update:modelValue', value)
                }
            }       
        }
    }
</script>