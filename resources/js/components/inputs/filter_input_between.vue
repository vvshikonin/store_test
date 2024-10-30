<template>
    <div class="mb-2 filter" style="width: 16.6%;">
        <div class="d-flex align-items-center">
            <label class="form-label text-muted mb-0 ps-1 pe-2" style="font-size: 13px;">{{ label }}</label>
            <div style="cursor:pointer">
                <font-awesome-icon @click="onChangeMode(1)" icon="fa-solid fa-arrows-left-right" size="sm" :class="{'text-primary' : mode === 1, 'text-muted' : mode !== 1,}" class="text-primary pe-2" title="Промежуток"/>
                <font-awesome-icon @click="onChangeMode(2)" icon="fa-solid fa-equals" size="sm" class="pe-2" :class="{'text-primary' : mode === 2, 'text-muted' : mode !== 2,}" title="Равно"/>
                <font-awesome-icon @click="onChangeMode(3)" icon="fa-solid fa-not-equal" size="sm" class="pe-2" :class="{'text-primary' : mode === 3, 'text-muted' : mode !== 3,}" title="Не равно"/>
            </div>
        </div>
        <div v-if="mode === 1" class="d-flex flex-row align-items-center justify-content-between" style="width:230px">
            <input :value="start" v-bind:type="type" v-bind:step="step" class="form-control" @input="$emit('update:start', $event.target.value); $emit('update:equal', null); $emit('update:notEqual', null)" style="height: 30px; font-size: 13px; width:110px" placeholder="от">
            <span class="text-muted">-</span>
            <input :value="end" v-bind:type="type" v-bind:step="step" class="form-control" @input="$emit('update:end', $event.target.value)" style="height: 30px; font-size: 13px; width:110px" placeholder="до">
        </div>
        <div v-else-if="mode === 2" class="d-flex flex-row align-items-center justify-content-between" style="width:230px">
            <input :value="equal" v-bind:type="type" v-bind:step="step" class="form-control" @input="$emit('update:equal', $event.target.value); $emit('update:start', null); $emit('update:end', null)" style="height: 30px; font-size: 13px; width:230px" placeholder="Равно">
        </div>
        <div v-else-if="mode === 3" class="d-flex flex-row align-items-center justify-content-between" style="width:230px">
            <input :value="notEqual" v-bind:type="type" v-bind:step="step" class="form-control" @input="$emit('update:notEqual', $event.target.value); $emit('update:start', null); $emit('update:end', null)" style="height: 30px; font-size: 13px; width:230px" placeholder="Не равно">
        </div>
    </div>
</template>


<script>
export default {
    props: {
        label: String,
        placeholder: String,
        type: String,
        step: String,
        start: String,
        end: String,
        equal: String,
        notEqual: String,
    },
    emits: ['update:start', 'update:end', 'update:equal', 'update:notEqual'],
    data(){
        return{
            mode: 1,
        }
    },
    methods:{
        onChangeMode(newMode){
            this.mode = newMode;
            switch (newMode){
                case 1:
                    this.$emit('update:equal', null); 
                    this.$emit('update:notEqual', null)
                    break;
                case 2:
                    this.$emit('update:start', null); 
                    this.$emit('update:end', null)
                    this.$emit('update:notEqual', null)
                    break;
                case 3:
                    this.$emit('update:start', null); 
                    this.$emit('update:end', null)
                    this.$emit('update:equal', null)
                    break;
            }
        },
    }
}
</script>