<template>
    <DefaultModal @close_modal="onClose_modal()" :title="title" :width="width">
        <!-- Таблица-листинг с шаблонами -->
        <div v-if="currentState === 'list'" class="p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>Шаблоны XML-каталогов</h3>
                <button class="btn btn-light border border-secondary bg-opacity btn-sm" @click="switchToCreate">
                    Создать новый шаблон
                    <font-awesome-icon icon="fa-plus" class="text-success" />
                </button>
            </div>
            <MainTable :tableSettings="settings" class="border-1">
                <template v-slot:thead>
                    <TR>
                        <TH width="250px">Название шаблона</TH>
                        <TH width="250px">Бренды</TH>
                        <TH>ICML-каталог</TH>
                        <TH width="105px"></TH>
                    </TR>
                </template>
                <template v-slot:tbody>
                    <TR v-for="template in templates" :key="template.id">
                        <TD>{{ template.name }}</TD>
                        <TD>
                            <span v-for="(brand, index) in template.brand_names" :key="brand">
                                {{ brand }}<span v-if="index < template.brand_names.length - 1">,&nbsp;</span>
                            </span>
                        </TD>
                        <TD>
                            <a :href="template.xml_link"> {{ template.xml_link ? 'XML-файл' : '-'}} </a>
                        </TD>
                        <TD>
                            <button @click="editTemplate(template)" class="btn btn-outline-primary border-0 btm-sm">
                                <font-awesome-icon icon="fa-regular fa-pen-to-square" />
                            </button>
                            <TrashButton style="width: 40px; height: 36px;" class="btn btn-sm ms-2" @click="deleteTemplate(template.id)" />
                        </TD>
                    </TR>
                </template>
            </MainTable>
        </div>

        <!-- Интерфейс для создания/редактирования шаблона -->
        <div v-if="currentState === 'create' || currentState === 'edit'" class="d-flex flex-column p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>{{ isEditing ? 'Редактирование шаблона' : 'Создание нового шаблона' }}</h3>
                <button v-if="templates.length > 0" class="btn btn-secondary btn-sm" @click="switchToList">Вернуться</button>
            </div>
            <div class="mb-3">
                <input id="templateName" v-model="form.name" type="text" class="form-control" placeholder="Введите название шаблона" />
                <label for="templateName" class="text-muted" style="font-size: 13px">Старайтесь называть шаблоны иначе, чем названия брендов, для предотвращения перезаписи каталогов.</label>
            </div>
            <div>
                <multiselect-input 
                    v-model="form.brand_ids" 
                    :options="brands" 
                    class="ms-0 mb-0"
                    label="Выберите бренды" 
                    placeholder="Выберите бренды для шаблона"
                    style="width: 80%">
                </multiselect-input>
            </div>
        </div>

        <!-- Кнопка для генерации XML-каталогов, всегда отображается -->
        <div class="d-flex p-3" :class="bottomButtonsPlace">
            <button v-if="currentState === 'create' || currentState === 'edit'"  class="btn btn-success" @click="saveTemplate">
                {{ isEditing ? 'Сохранить изменения' : 'Создать шаблон' }}
            </button>
            <button v-if="currentState === 'list' || !templates.length" class="btn btn-primary" @click="handleXMLGeneration" :disabled="isLoading">
                Генерация XML-каталогов
            </button>
        </div>
    </DefaultModal>
</template>

<script>
import { XMLCatalogAPI } from '../../api/xml_catalog_api';
import DefaultModal from './default_modal.vue';
import MainTable from '../Tables/main_table.vue';
import TR from '../Tables/tr.vue';
import TD from '../Tables/td.vue';
import TrashButton from '../inputs/trash_button.vue';
import MultiselectInput from '../inputs/multiselect_input.vue'; // Подключаем multiselect_input


export default {
    components: { DefaultModal, MainTable, TR, TD, TrashButton, MultiselectInput },
    data() {
        return {
            settings: {
                isLoading: false,
                isCover: false,
            },
            templates: [], // Список шаблонов
            form: {
                id: null,
                name: '',
                brand_ids: []
            },
            isEditing: false, // Флаг для режима редактирования
            currentState: 'list' // Возможные состояния: 'list', 'create', 'edit'
        }
    },
    props: {
        brands: Array,
        title: String,
        width: {
            type: String,
            default: '850px'
        }
    },
    emits: ['close_modal'],
    methods: {
        async handleXMLGeneration() {
            this.showToast('Пожалуйста, подождите', 'Идет генерация XML-каталогов...');
            try {
                const response = await XMLCatalogAPI.manual_generate();
                this.showToast('ОК', response.data.message, 'success');
            } catch (error) {
                console.error(error);
                this.showToast('Ошибка', error.data.message, 'danger');
            }
        },
        async loadXMLTemplates() {
            this.settings.isLoading = true;
            try {
                const response = await XMLCatalogAPI.index();
                this.templates = response.data;

                if (this.templates.length === 0) {
                    this.switchToCreate();
                } else {
                    this.switchToList();
                }

                this.settings.isLoading = false;
            } catch (error) {
                console.error(error);
                this.settings.isLoading = false;
            }
        },
        switchToCreate() {
            this.resetForm();
            this.isEditing = false;
            this.currentState = 'create';
        },
        switchToList() {
            this.currentState = 'list';
        },
        editTemplate(template) {
            this.form.id = template.id;
            this.form.name = template.name;
            this.form.brand_ids = template.brand_ids;
            this.isEditing = true;
            this.currentState = 'edit';
            console.log(this.form);
        },
        async saveTemplate() {
            this.settings.isLoading = true;
            try {
                let response;
                if (this.isEditing) {
                    response = await XMLCatalogAPI.update(this.form);
                    console.log(this.form);
                } else {
                    response = await XMLCatalogAPI.store({
                        name: this.form.name,
                        brand_ids: this.form.brand_ids
                    });
                }
                // this.showToast('ОК', response.data.message, 'success');
                this.resetForm();
                this.loadXMLTemplates();
            } catch (error) {
                console.error(error);
                // this.showToast('Ошибка', error.response.data.message, 'danger');
            }
        },
        async deleteTemplate(id) {
            this.settings.isLoading = true;
            try {
                const response = await XMLCatalogAPI.destroy(id);
                this.showToast('ОК', response.data.message, 'success');
                this.loadXMLTemplates();
            } catch (error) {
                console.error(error);
                this.showToast('Ошибка', error.response.message, 'danger');
            }
        },
        resetForm() {
            this.form.id = null;
            this.form.name = '';
            this.form.brand_ids = [];
            this.isEditing = false;
        },
        onClose_modal() {
            this.$emit('close_modal');
        }
    },
    computed: {
        bottomButtonsPlace()
        {
            let saveButtonCondition = this.currentState === 'create' || this.currentState === 'edit';
            let generateButtonCondition = this.currentState === 'list' || this.templates.length == 0;

            let justifyClass = saveButtonCondition && generateButtonCondition ? 'justify-content-between' : 
                            saveButtonCondition && !generateButtonCondition ? 'justify-content-start' : 'justify-content-end';

            return justifyClass;
        }
    },
    mounted() {
        this.loadXMLTemplates();
    }
}
</script>