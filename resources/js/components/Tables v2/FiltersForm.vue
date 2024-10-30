<template>
    <form @submit.prevent="submit()">
        <div class="d-flex flex-column">
            <div class="d-flex flex-row flex-wrap filter-wrapper bg-white mb-3 mt-3">
                <slot></slot>
            </div>
            <div class="d-flex flex-row justify-content-between">
                <div class="d-flex flex-row">
                    <button type="submit" class="btn btn-primary btn-sm me-1" :disabled="disabled"
                        style="margin-left: 10px">Применить</button>
                    <button v-if="showClearButton" @click="clear()" type="button"
                        class="btn btn-sm btn-light me-1 border text-danger align-item-center" :disabled="disabled">
                        <font-awesome-icon icon="fa-solid fa-xmark" class="me-1" />Очистить
                    </button>
                </div>
                <div v-if="showTemplates">
                    <button @click="switchTemplateCreateBlock" type="button" :disabled="disabled"
                        class="btn btn-primary me-1" v-if="isFiltersApplied" style="font-size: 14px;">
                        <font-awesome-icon class="me-1" icon="fa-solid fa-floppy-disk" size="xl" />
                        Сохранить фильтры
                    </button>
                    <button @click="switchTemplatesListBlock" v-if="filterTemplates.data.length" :disabled="disabled"
                        type="button" class="btn btn-success" style="font-size: 14px;">
                        <font-awesome-icon class="me-1" icon="fa-solid fa-file" size="xl" />
                        Шаблоны фильтров
                    </button>
                </div>
            </div>
            <div class="w-100 d-flex justify-content-end">
                <div v-if="createTemplateInputOpen" class="mt-3 d-flex">
                    <input style="font-size: 12px; width: 260px" placeholder="Введите название нового шаблона" v-model="newTemplateName" class="form-control">
                    <button @click="createTemplate(newTemplateName)" type="button" class="btn btn-success ms-2" style="font-size: 12px">
                        Сохранить
                    </button>
                </div>
                <div v-if="templatesListBlockOpen && filterTemplates.data.length" class="border border-secondary rounded-2 p-2 pe-0 pb-0 d-flex flex-wrap mt-3 justify-content-start align-items-start " style="max-width: 50%">
                    <span v-if="filterTemplates.isLoaded" v-for="template in filterTemplates.data" class="rounded-1 p-1 me-2 mb-2 template-element">
                        <span class="ms-1" @click="selectTemplate(template)">
                            {{ template.name }}
                        </span>
                        <EditInputPopup @input-changed="editTemplate(template.id, $event)">
                            <template #button-content>
                                <font-awesome-icon style="cursor:pointer"
                                class="ms-2 text-primary template-icon-hover" icon="fa-solid fa-pen" />
                            </template>
                        </EditInputPopup>
                        <font-awesome-icon style="cursor:pointer" @click="deleteTemplate(template.id)"
                            class="ms-2 me-1 text-danger template-icon-hover" icon="fa-solid fa-xmark" size="lg" />
                    </span>
                    <span v-else class="p-1 me-2 mb-2 text-secondary"> Идет загрузка... </span>
                </div>
            </div>
        </div>
    </form>
</template>

<style scoped>
.filter-wrapper .filter {
    min-width: fit-content;
    padding-left: 10px;
    padding-right: 10px;
}

.template-element {
    cursor: pointer;
    line-height: 24px;
    position: relative;
    padding: 0.15rem 25px 0.15rem 0.15rem;
    border-radius: 4px;
    background: rgb(236, 236, 236);
    border: 1px solid rgb(170, 170, 170);
    transition: .2s ease;
}
.template-element:hover {
    background: rgb(212, 212, 212);
}
.template-icon-hover {
    transition: filter .2s;
}

.template-icon-hover:hover {
    filter: brightness(75%);
}

</style>

<script>
import EditInputPopup from '../../ui/inputs/EditInputPopup.vue';

export default {
    data() {
        return {
            templatesListBlockOpen: false,
            createTemplateInputOpen: false,
            newTemplateName: null,
        }
    },
    props: {
        disabled: {
            default: false,
            type: Boolean,
        },
        showClearButton: {
            default: false,
            type: Boolean,
        },
        showTemplates: {
            default: false,
            type: Boolean
        },
        isFiltersApplied: {
            default: false,
            type: Boolean,
        }
    },
    inject: ['filterTemplates'],
    components: { EditInputPopup },
    methods: {
        switchTemplateCreateBlock() {
            this.createTemplateInputOpen = !this.createTemplateInputOpen;
            this.templatesListBlockOpen = false;
        },
        switchTemplatesListBlock() {
            this.templatesListBlockOpen = !this.templatesListBlockOpen;
            this.createTemplateInputOpen = false;
        },
        submit() {
            this.$emit('sumbit');
        },
        clear() {
            this.$emit('clear');
        },
        selectTemplate(template) {
            this.$emit('selectTemplate', template);
        },
        createTemplate(newTemplateName) {
            this.$emit('createTemplate', newTemplateName);
            this.newTemplateName = null;
            this.createTemplateInputOpen = false;
        },
        editTemplate(id, newName) {
            this.$emit('editTemplate', [id, newName]);
            this.editedName = null;
        },
        deleteTemplate(id) {
            this.$emit('deleteTemplate', id);
        }
    }
}
</script>