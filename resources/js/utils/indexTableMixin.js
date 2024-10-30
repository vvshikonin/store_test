import { computed } from 'vue';
import { userFilterTemplateAPI } from '../api/user_filter_template_api';

import Table from '../components/Tables v2/Table.vue'
import TH from '../components/Tables v2/TH.vue'
import TR from '../components/Tables v2/TR.vue'
import TD from '../components/Tables v2/TD.vue'

import FilterInput from '../components/inputs/filter_input.vue'
import FilterSelect from '../components/inputs/FilterSelect.vue'
import FilterInputBetween from '../components/inputs/filter_input_between.vue'
import FilterMultipleSelect from '../components/inputs/filter_select_multiple.vue';
import FilterSelectSearch from '../components/inputs/filter_select_searchable.vue';

export default {
    components: { Table, TH, TR, TD, FilterInput, FilterSelect, FilterInputBetween, FilterMultipleSelect, FilterSelectSearch },
    data() {
        return {
            settings: {
                indexAPI: null,

                tableTitle: 'Заголовок таблицы',
                createButtonText: 'Создать',
                additionalHeaderButtonText: 'Дополнительная кнопка',
                localStorageKey: 'request_params',

                isLoading: false,
                isCover: false,
                isNoEntries: false,
                isStickyFooter: false,
                isFilterClearable: false,
                isFiltersApplied: false,

                saveParams: false,

                withTitle: true,
                withHeader: true,
                withCreateButton: true,
                withAdditionalHeaderButton: false,
                withFilters: true,
                withInfo: true,
                withThead: true,
                withFooter: true,
                withPagination: true,
                withExport: true,
                withFilterTemplates: true
            },
            filterTemplates: {
                data: [],
                isLoaded: false,
            },
            params: {},
            data: [],
            meta: {},
        }
    },
    provide() {
        return {
            settings_inject: this.settings,
            params: computed(() => this.params),
            meta: computed(() => this.meta),
            filterTemplates: computed(() => this.filterTemplates),
            clickCreateButton: () => this.onClickCreateButton(),
            clickAdditionalHeaderButton: () => this.onClickAdditionalHeaderButton(),
            confirmFilter: () => this.confirmFilter(),
            clearFilter: () => this.clearFilter(),
            sort: (field) => this.sort(field),
            changePerPage: (perPage) => this.changePerPage(perPage),
            changeCurrentPage: (page) => this.changeCurrentPage(page),
            exportFile: () => this.onExport(),
            createTemplate: (name) => this.createTemplate(name),
            selectTemplate: (template) => this.selectTemplate(template),
            editTemplate: (name) => this.editTemplate(name),
            deleteTemplate: (id) => this.deleteTemplate(id),
        }
    },
    methods: {
        initSettings: () => null,
        onInitData: res => null,
        onInitParamsDefault: defaultParams => null,
        onClickCreateButton: () => null,
        onClickAdditionalHeaderButton: () => null,
        onConfirmFilter: () => null,
        onClearFilter: () => null,
        onSort: field => null,
        onChangePerPage: perPage => null,
        onChangeCurrentPage: page => null,
        onExport: () => null,
        onCreateTemplate: () => null,
        onSelectTemplate: () => null,
        onEditTemplate: () => null,
        onDeleteTemplate: () => null,

        async index() {
            if (this.settings.indexAPI) {
                this.settings.isCover = true;
                const res = await this.settings.indexAPI(this.params);
                this.initData(res);
                this.saveParams();
                if (this.settings.withFilterTemplates)
                {
                    this.initFilterTemplates();
                }

                this.settings.isLoading = false;
                this.settings.isCover = false;
            }
        },

        confirmFilter: function () {
            this.params.page = 1;
            this.settings.isFilterClearable = this.checkIsFiltersApplied()
            this.settings.isFiltersApplied = this.checkIsFiltersApplied();
            this.onConfirmFilter();

            if (this.settings.isFilterClearable) {
                this.index();
            }
        },
        clearFilter: function () {
            this.params.page = 1;
            this.params = this.initParamsDefault();
            this.settings.isFilterClearable = false;
            this.settings.isFiltersApplied = false;
            this.onClearFilter()
            this.index();
        },
        checkIsFiltersApplied() {
            const currentParams = JSON.stringify(this.params);
            const defaultParams = JSON.stringify(this.initParamsDefault());
            return currentParams !== defaultParams;
        },

        sort: function (field) {
            let current_sort_field = this.params.sort_field;

            if (current_sort_field == field) this.changeSortType();
            else this.changeSortField(field);

            this.onSort(field);
            this.index();
        },
        changeSortType() {
            let current_sort_type = this.params.sort_type
            this.params.sort_type = current_sort_type == 'asc' ? 'desc' : 'asc';
        },
        changeSortField(field) {
            this.params.sort_type = 'desc';
            this.params.sort_field = field;
        },

        changePerPage: function (event) {
            this.params.page = 1;
            this.params.per_page = event;
            this.onChangePerPage(event);
            this.index();
        },
        changeCurrentPage: function (event) {
            this.params.page = event;
            this.onChangeCurrentPage(event);
            this.index();
        },

        initData(res) {
            this.data = res.data.data;
            this.meta = res.data.meta;
            this.settings.isNoEntries = !this.meta.total;
            this.onInitData(res);
        },

        initParams() {
            if (this.settings.saveParams) {
                const storageParams = JSON.parse(localStorage.getItem(this.settings.localStorageKey));
                this.params = storageParams ? storageParams : this.initParamsDefault();
                this.settings.isFilterClearable = this.checkIsFiltersApplied();
                this.settings.isFiltersApplied = this.checkIsFiltersApplied();
            } else {
                this.params = this.initParamsDefault();
            }
        },
        initParamsDefault() {
            const defaultParams = {
                per_page: this.params.per_page || 25,
                page: this.params.page || 1,
                sort_field: this.params.sort_field || 'id',
                sort_type: this.params.sort_type || 'asc',
            }
            this.onInitParamsDefault(defaultParams);
            return defaultParams;
        },
        saveParams() {
            if (this.settings.saveParams) {
                localStorage.setItem(this.settings.localStorageKey, JSON.stringify(this.params));
            }
        },
        async initFilterTemplates() {
            const templateData = {
                table_enum: this.formTableEnum(),
            }
            await userFilterTemplateAPI.index(templateData).then(res => {
                this.filterTemplates.data = res.data.data;
                this.filterTemplates.isLoaded = true;
            })
        },
        async createTemplate(name) {
            this.settings.isLoading = true;
            this.settings.isCover = true;
            this.filterTemplates.isLoaded = false;

            const newTemplateData = {
                name: name,
                table_enum: this.formTableEnum(),
                template_data: JSON.stringify(this.params)
            };
            
            await userFilterTemplateAPI.store(newTemplateData).then(res => {
                this.showToast('OK', 'Шаблон фильтров успешно сохранен.', 'success');
                this.initFilterTemplates();
            })
            this.onCreateTemplate();

            this.settings.isLoading = false;
            this.settings.isCover = false;
        },
    
        selectTemplate(template) {
            this.params = JSON.parse(template.template_data);
            this.onSelectTemplate();
            this.confirmFilter();
            this.showToast('OK', 'Шаблон фильтров успешно применен.', 'success');
        },

        async editTemplate(data) {
            this.filterTemplates.isLoaded = false;
            const id = data[0];
            const name = data[1];
            await userFilterTemplateAPI.update(id, name).then(res => {
                this.showToast('OK', 'Шаблон фильтров успешно переименован.', 'success');
                this.initFilterTemplates();
            })
            this.onEditTemplate();
        },

        async deleteTemplate(id) {
            this.filterTemplates.isLoaded = false;
            this.settings.isLoading = true;
            this.settings.isCover = true;
            
            await userFilterTemplateAPI.delete(id).then(res => {
                this.showToast('OK', 'Шаблон фильтров успешно удален.', 'success');
                this.initFilterTemplates();
            })
            this.onDeleteTemplate();

            this.settings.isLoading = false;
            this.settings.isCover = false;
        },

        formTableEnum() {
            const currentHash = window.location.hash;
            let tableAddress = currentHash.split('/')[1];
            const tableEnum = tableAddress.replace(/-/g, '_');

            return tableEnum;
        },
        mounted() {
            this.initSettings();
            this.initParams();
            this.index();
        },
    },
    mounted() {
        this.mounted()
    },
}