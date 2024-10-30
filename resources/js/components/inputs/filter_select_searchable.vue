<template>
    <div class="mb-2 filter" style="width: 16.6%; position: relative;">
        <div class="select-searchable" ref="searchableSelectWrapper">  
            <label v-if="label" for="exampleFormControlInput1" class="form-label text-muted mb-0 ps-1" style="font-size: 13px;">
                {{ label }}
            </label>
            <p @click="onPlaceholderClick()" class="form-control" style="height: 30px; width: 230px;">
                <span v-if="modelValue != null" :value="modelValue" style="cursor: text; position: absolute; left: 13px; z-index: 10;"
                    :style="{'top': label ? '30px' : '6px'}"> 
                    {{ areOptionsOpened ? '' : selected }}
                </span>
                <input ref="searchInput"
                    class="text-muted form-control" v-model="searchText"
                    :placeholder="modelValue == null ? placeholder : ''"
                    style="position: absolute; left: 0; width: 230px; height: 30px; font-size: 13px;"
                    :style="{'top': label ? '24px' : 0}">
                <span v-if="modelValue != null" @click="$emit('update:modelValue', null); selected = null" class="text-danger d-flex justify-content-center align-items-center"
                    style="position: absolute; cursor:pointer; left: 205px; width:20px; height:20px"
                    :style="{'top': label ? '29px' : '5px'}">
                    <font-awesome-icon icon="fa-solid fa-xmark" />
                </span>
                <span v-else @click="onPlaceholderClick()" class="text-secondary d-flex justify-content-center align-items-center" style="position: absolute; cursor:pointer; left: 210px; width:10px; height:10px"
                    :style="{'top': label ? '34px' : '10px'}">
                    <font-awesome-icon style="width: 12px; height: 12px" icon="fa-solid fa-chevron-down" />
                </span>
            </p>
            <div v-if="areOptionsOpened" class="scrollable-list form-control p-0" style="max-height: 350px; overflow-y: auto; z-index: 1000; width: 230px; position:absolute;"
                :style="{'top': label ? '55px' : '31px'}">
                <p v-for="option in filteredOptions" @click="selectOption(option)" :key="option.id"
                    style="cursor: pointer" class="p-1 ps-2 select-option"
                    :class="{'text-secondary': isOptionSelected(option.id) }"
                    :style="{'background-color': isOptionSelected(option.id) ? '#eee' : '' }">
                    {{ option.name }}
                </p>
                <p v-if="!filteredOptions.length" class="p-1 ps-2 text-muted"> Результатов не найдено </p>
            </div>
        </div>
    </div>
</template>

<style>
    .filter .select-searchable {
        position: relative;
    }
    .filter .select-searchable p {
        margin: 0;
        font-size: 13px;
    }
    .filter .select-searchable .select-option:hover {
        background: #e7e7e7;
    }
    .filter div select{
        width: 230px;
    }
    .scrollable-list::-webkit-scrollbar {
        width: 5px;
    }

    .scrollable-list::-webkit-scrollbar-track {
        background-color: #e7e7e7;
        border-radius: 100px;
    }

    .scrollable-list::-webkit-scrollbar-thumb {
        background-color: #a5a5a5;
        border-radius: 100px;
    }
    .scrollable-list::-webkit-scrollbar-thumb:hover {
        background-color: #7a7a7a
    }
</style>

<script>
export default {
    data() {
        return {
            areOptionsOpened: false,
            selected: null,
            searchText: '',
        }
    },
    props: {
        label: String,
        placeholder: {
            type:String,
            default: "Выбрать значение"
        },
        type: String,
        options:{
            type: Array,
            default:[{id: 0, name: 'Значение 1'}, {id: 1, name: 'Значение 2'}]
        },
        modelValue: [String, Number, Array, null],
    },
    emits: ['update:modelValue'],
    computed: {
        filteredOptions() {
            return this.options.filter(option =>
                option.name.toLowerCase().includes(this.searchText.toLowerCase().trim())
            );
        },
        selected: {
            get() {
                const selectedOption = this.options.find(option => option.id === this.modelValue);
                return selectedOption ? selectedOption.name : '';
            },
            set(value) {
                this.$emit('update:modelValue', this.options.find(option => option.name === value).id);
            }
        }
    },
    methods: {
        onPlaceholderClick() {
            this.areOptionsOpened = true;
            if (this.selected !== null) {
                this.$refs.searchInput.focus();
            }
        },
        selectOption(option) {
            this.selected = option.name;
            this.$emit('update:modelValue', option.id);
            this.hideSelect();
        },
        hideSelect() {
            this.areOptionsOpened = false;
            this.searchText = '';
        },
        handleDocumentClick(event) {
            if (!this.$refs.searchableSelectWrapper.contains(event.target)) {
                this.hideSelect();
            }
        },
        isOptionSelected(el) {
            return el == this.modelValue ? true : false;
        }
    },
    mounted() {
        document.addEventListener( 'click', this.handleDocumentClick);
    },
    beforeUnmount() {
        document.removeEventListener( 'click', this.handleDocumentClick);
    }
}
</script>