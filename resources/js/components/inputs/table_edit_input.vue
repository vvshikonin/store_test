<template>
    <div class="d-flex edit-input-component">
        <span v-if="!isEdit" @click="openInput()"> {{ content }} </span>
        <div v-else class="edit-input-field d-flex">
            <input :type="type" :min="min" :max="max" :step="step" @blur="tempEdit()" v-model="inputContent" class="form-control" />
            <button type="button" class="btn-primary btn ms-2" @click="saveEdit()"> Сохранить </button>
            <button type="button" class="btn-light btn border bg-gradient border-1 ms-2" @click="cancelEdit()"> Отменить </button>
        </div>
    </div>
</template>

<style>
    .edit-input-component span {
        text-decoration: none;
        outline: 0;
        color: #0D6EFD;
        opacity: 1;
        border-bottom: dotted 1px #222;
        -webkit-transition: .2s all;
        -moz-transition: .2s all;
        -ms-transition: .2s all;
        -o-transition: .2s all;
        transition: .2s all;
    }

    .edit-input-component span:hover {
        color: #0D6EFD;
        opacity: .7;
        border-bottom: solid 1px #777;
        cursor: pointer;
        -webkit-transition: .2s all;
        -moz-transition: .2s all;
        -ms-transition: .2s all;
        -o-transition: .2s all;
        transition: .2s all;
    }
</style>

<script>
export default {
    data() {
        return {
            isEdit: false,
            inputContent: this.content,
            tempContent: null,
        }
    },
    props: {
        content: String,
        type: {
            default: 'text',
            type: String
        },
        min: {
            default: null,
            type: [ Number, null ]
        },
        max: {
            default: null,
            type: [ Number, null ]
        },
        step: {
            default: null,
            type: [ Number, null ]
        }
    },
    emits: ['update:content', 'update:tempContent', 'cancel'],
    methods: {
        tempEdit() {
            this.$emit('update:tempContent', this.inputContent);
        },
        openInput() {
            this.inputContent = this.content;
            this.isEdit = true;
        },
        saveEdit() {
            if (this.inputContent != '')
            {
                this.$emit('update:content', this.inputContent);
                this.isEdit = false;
            } else {
                this.showToast('Ошибка', 'Поле не может быть пустым!', 'danger');
            }
        },
        cancelEdit() {
            this.$emit('cancel');
            this.isEdit = false;
            this.inputContent = this.content;
        },
    },
}
</script>
