<template>
    <div class="mb-2 filter" style="width: 16.6%;">
        <label for="select-field" class="form-label text-muted mb-0 ps-1" style="font-size: 13px;">{{ label }}</label>

        <div style="position: relative;">
            <div class="form-control d-flex flex-row flex-wrap align-items-center" style="font-size: 13px; min-height: 30px; padding: 0.1rem 0.75rem; width: 230px">
                <span class="text-muted" v-if="!this.modelValue.length || !options.length " style="vertical-align: text-top;">{{ options.length
                    ? placeholder : "Загрузка..." }}</span>
                <template v-for="id in modelValue" :key="id">
                    <span v-if="options.length" class="me-1 text-center text-nowrap"
                        style="line-height: 24px; position: relative; padding: .15rem; padding-right: 25px; border-radius: 4px; background: #ededed; border: 1px solid #b8b8b8">
                        {{ getNameById(id) }}
                        <font-awesome-icon @click="removeById(id)" icon="fa-solid fa-xmark" size="lg" class="ps-1"
                            style="z-index: 10; cursor: pointer; position: absolute; right: 6px; top: .15rem; color: #b8b8b8" />
                    </span>
                </template>
            </div>
            <select id="select-field" @change="addMulti()" v-model="selected" :class="{ 'placeholder': !options.length }"
                :disabled="!options.length" class="form-control"
                style="z-index: 5; height: 100%; font-size: 13px; width: 230px; position: absolute; top: 0; opacity: 0;">
                <option :value="null">{{ options.length ? placeholder : "Загрузка..." }}</option>
                <option style="text-overflow:ellipsis;" :class="{ 'bg-primary text-white': checkId(option) }"
                    v-for="option, index in options" :key="option" v-bind:value="index">
                    {{ option.name }}
                </option>
            </select>
        </div>
        <select :value="modelValue" v-model="ids_filter" multiple class="form-control"
            style="font-size: 13px; display: none;">
            <option v-for="option in options" v-bind:value="options.id" :key="option">
                {{ option.name }}
            </option>
        </select>
    </div>
</template>

<script>


export default {
    data() {
        return {
            ids_filter: [],
            selected: null,
        }
    },
    props: {
        label: String,
        placeholder: String,
        options: {
            type: Array,
            default: [{ id: 0, name: 'Значение 1' }, { id: 1, name: 'Значение 2' }]
        },
        modelValue: {
            default: [],
            type: Array
        },
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
            this.$emit('update:modelValue', this.ids_filter);
        },
        getNameById(id) {
            return this.options.find(x => x.id === id) ? this.options.find(x => x.id === id).name : null
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
            for (let i = 0; i < this.modelValue.length; i++) {
                if (this.modelValue[i] == option.id) {
                    return true;
                }
            };
            return false;
        },
    }
}
</script>
