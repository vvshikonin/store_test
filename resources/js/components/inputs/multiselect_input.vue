<template>
    <div class="m-3 me-5 d-flex flex-row justify-content-between" style="width: 45%;">
        <label class="text-muted mb-0 ps-1" :class="{ required: required }" style="font-size: 13px;">{{ label }}</label>
        <div style="position: relative">
            <div class="form-control" style="font-size: 13px; width: 474px; min-height: 30px;">
                <span class="text-muted" v-if="!this.ids_filter.length" style="vertical-align: text-top;">{{ placeholder
                    }}</span>
                <template v-for="id in ids_filter">
                    <span class="me-1 text-center text-nowrap"
                        style="line-height: 24px; position: relative; padding: .15rem; padding-right: 25px; border-radius: 4px; background: #ededed; border: 1px solid #b8b8b8">
                        {{ getById(id).name }} <font-awesome-icon @click="removeById(id)" icon="fa-solid fa-xmark"
                            size="lg" class="ps-1"
                            style="z-index: 10; cursor: pointer; position: absolute; right: 6px; top: .15rem; color: #b8b8b8" />
                    </span>
                </template>
            </div>
            <select id="select-field" @change="addMulti()" v-model="selected" class="form-control"
                style="z-index: 5; height: 100%; font-size: 13px; width: 474px; position: absolute; top: 0; opacity: 0;">
                <option :value="null">{{ placeholder }}</option>
                <option style="text-overflow:ellipsis" v-for="option, index in options" v-bind:value="index"> {{
                    checkId(option) }} </option>
            </select>
        </div>
        <select :value="modelValue" v-model="ids_filter" multiple class="form-control"
            style="font-size: 13px; display: none;">
            <option v-for="option in options" v-bind:value="options.id"> {{ option.name }} </option>
        </select>
    </div>
</template>
<style>
label.required:after {
    content: '*';
    color: red;
    font-weight: 800;
    font-size: 13px;
    vertical-align: middle;
    padding-left: 3px;
}
</style>
<script>
export default {
    props: {
        label: String,
        required: Boolean,
        placeholder: String,
        options: {
            type: Array,
            default: () => []
        },
        modelValue: {
            type: Array,
            default: () => []
        }
    },
    data() {
        return {
            selected: null,
            ids_filter: this.modelValue
        }
    },
    watch: {
        modelValue(newVal) {
            this.ids_filter = newVal;
        }
    },
    emits: ['update:modelValue'],
    methods: {
        addMulti() {
            if (!this.ids_filter.includes(this.options[this.selected].id)) {
                this.ids_filter.push(this.options[this.selected].id);
                this.selected = null;
            } else {
                this.ids_filter = this.arrayRemove(this.ids_filter, this.options[this.selected].id);
                this.selected = null;
            }
            this.$emit('update:modelValue', this.ids_filter)
        },
        getById(id) {
            return this.options.find(x => x.id === id);
        },
        removeById(id) {
            this.ids_filter = this.arrayRemove(this.ids_filter, id);
            this.$emit('update:modelValue', this.ids_filter);
        },
        arrayRemove(arr, value) {
            return arr.filter(function (ele) {
                return ele != value;
            })
        },
        checkId(option) {
            for (let i = 0; i < this.ids_filter.length; i++) {
                if (this.ids_filter[i] == option.id) {
                    return 'ВЫБРАНО ' + option.name
                }
            };
            return option.name;
        },
    }
}
</script>