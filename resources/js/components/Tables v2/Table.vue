<template>
    <div class="position-relative border-start border-end border-bottom rounded-1">
        <TableCover v-if="settings.isCover && !settings.isLoading"></TableCover>

        <div v-if="isShowTopBlock" class="p-3 bg-white">
            <div class="d-flex flex-row align-items-center justify-content-between">
                <div class="d-flex flex-row align-items-center" style="margin-left: 10px">
                    <h2 v-if="settings.withTitle">{{ settings.tableTitle }}</h2>
                    <a v-if="settings.withFilters" @click.prevent="onToggleFilers()" href="#" class="ps-3"
                        style="text-decoration: none;">{{ filterTogglerText }}</a>
                </div>

                <div class="d-flex flex-row align-items-center">
                    <slot v-if="settings.withHeader" name="header">
                        <span class="pe-3"> Header Slot (withHeader: true) </span>
                    </slot>

                    <button v-if="settings.withCreateButton" @click="handleClickCreateButton()" type="button"
                        class="d-flex align-items-center btn btn-primary fs-5 p-2 ps-3 pe-3">
                        <font-awesome-icon icon="fa-solid fa-plus" class="me-2" />
                        <span class="fw-normal">{{ settings.createButtonText }}</span>
                    </button>

                    <button v-if="settings.withAdditionalHeaderButton" @click="handleClickAdditionalHeaderButton()" type="button"
                        class="d-flex align-items-center btn btn-primary fs-5 p-2 ps-3 pe-3 ms-3">
                        <font-awesome-icon icon="fa-solid fa-plus" class="me-2" />
                        <span class="fw-normal">{{ settings.additionalHeaderButtonText }}</span>
                    </button>
                </div>
            </div>

            <Transition>
                <FiltersForm v-if="settings.withFilters && isShowFilters" :disabled="settings.isLoading"
                    :showClearButton="settings.isFilterClearable" :showTemplates="settings.withFilterTemplates" 
                    :isFiltersApplied="settings.isFiltersApplied" :filterTemplates="filterTemplates"
                    @sumbit="handleConfirmFilter()" @clear="handleClearFilter()"
                    @createTemplate="handleCreateTemplate($event)" @selectTemplate="handleSelectTemplate($event)"
                    @editTemplate="handleEditTemplate($event)" @deleteTemplate="handleDeleteTemplate($event)">
                    <slot name="filters">
                        <span class="ps-3">Filters Slot (withFilters: true)</span>
                    </slot>
                </FiltersForm>
            </Transition>
        </div>

        <Transition>
            <div v-if="settings.withBottomBox" class="d-flex flex-row position-fixed end-0 bottom-0 start-0 bg-white border pt-3 pb-2 shadow-lg"
                style="transition: all .2s; z-index: 510;" :style="bottomboxMargin">
                <slot name="bottom-box"></slot>
            </div>
        </Transition>
        <slot v-if="settings.withInfo" name="info"></slot>

        <div class="w-100 h-100">
            <table ref="mainTable" class="table m-0 table-hover main-table">

                <thead v-if="settings.withThead"
                    class="position-sticky top-0 align-middle border-top border-bottom bg-light shadow-sm main-th"
                    style="font-size: 12px; height: 41px; z-index: 500;">
                    <td v-if="settings.isLoading || settings.isNoEntries"></td>
                    <slot v-if="!settings.isLoading && !settings.isNoEntries" name="thead">
                        <th class="border-top border-bottom">Thead Slot (withThead: true)</th>
                    </slot>

                </thead>
                <div v-else class="border-top"></div>

                <tbody class="align-middle bg-white" style="font-size: 13px;">
                    <TableSpinner v-if="settings.isLoading" :tableHeight="settings.tableHeight"></TableSpinner>
                    <NoEntries v-else-if="settings.isNoEntries"></NoEntries>
                    <slot v-else name="tbody"></slot>
                </tbody>

                <TFoot v-if="settings.withFooter" :isLoading="settings.isLoading" :isStickyFooter="isStickyFooter">
                    <slot name="tfoot" v-if="!settings.isLoading && !settings.isNoEntries">
                        Всего в таблице: {{ meta.total }}
                    </slot>
                </TFoot>

            </table>

            <div class="d-flex flex-row bg-white rounded-1">
                <PerPage v-if="meta && settings.withPagination" :meta="meta" :perPageVuluesArray="[25, 50, 100]"
                    @per_page="handleChangePerPage($event)">
                </PerPage>
                <ExportButton v-if="isShowExportButton" :isLoading="settings.isLoading" @export="handleExportFile()">
                </ExportButton>
                <Pagination v-if="meta && settings.withPagination" :meta="meta"
                    @current_page="handleChangeCurrentPage($event)"></Pagination>
            </div>

        </div>
    </div>
</template>
<style scoped>
thead td,
tfoot td {
    padding: 0.5rem 0.5rem;
}

tbody td {
    height: 63px;
    max-height: 80px;
}

tbody tr {
    --bs-border-width: 2px !important;
    border-left: var(--bs-border-width) var(--bs-border-style);
    border-right: var(--bs-border-width) var(--bs-border-style);
    border-left-color: #0d6dfd00 !important;
    border-right-color: #0d6dfd00 !important;
}

.main-table {
    position: relative;
}

.main-table {
    border-collapse: separate;
    border-spacing: 0;
}

.main-table .main-th td {
    border-top: 1px var(--bs-border-color) solid;
    border-bottom: 1px var(--bs-border-color) solid;
}
</style>
<script>
import { mapGetters } from 'vuex';

import TableCover from './TableCover.vue'
import FiltersForm from './FiltersForm.vue'

import TableSpinner from './TableSpinner.vue'
import NoEntries from './NoEntries.vue'
import TFoot from './TFoot.vue'

import PerPage from './PerPage.vue'
import ExportButton from './ExportButton.vue'
import Pagination from './Pagination.vue'

export default {
    inject: {
        settings_inject: {
            default: () => ({
                tableTitle: 'tableTitle',
                createButtonText: 'createButtonText',
                additionalButtonText: 'additionalButtonText',

                isLoading: false,
                isCover: false,
                isNoEntries: false,
                isStickyFooter: false,
                isFilterClearable: false,
                isFiltersApplied: false,

                saveParams: false,

                withTitle: false,
                withHeader: false,
                withCreateButton: false,
                withAdditionalHeaderButton: false,
                withFilters: false,
                withInfo: true,
                withThead: true,
                withFooter: true,
                withPagination: false,
                withExport: false,
                withBottomBox: false,
                withFilterTemplates: false,
            })
        },
        filterTemplates: {
            default: () => [],
        },
        meta: {
            default: () => ({})
        },
        clickCreateButton: {
            default: () => null,
        },
        clickAdditionalHeaderButton: {
            default: () => null,
        },
        confirmFilter: {
            default: () => null
        },
        clearFilter: {
            default: () => null,
        },
        createTemplate: {
            default: () => null,
        },
        selectTemplate: {
            default: () => null,
        },
        editTemplate: {
            default: () => null,
        },
        deleteTemplate: {
            default: () => null,
        },
        changePerPage: {
            default: () => null
        },
        changeCurrentPage: {
            default: () => null,
        },
        exportFile: {
            default: () => null,
        }
    },
    props: ['settings_prop'],
    data() {
        return {
            isShowFilters: true,
        }
    },
    components: { TableCover, FiltersForm, TableSpinner, NoEntries, TFoot, PerPage, ExportButton, Pagination },
    methods: {
        onToggleFilers() {
            this.isShowFilters = !this.isShowFilters;
        },
        handleCreateTemplate(name) {
            if (!this.settings_prop) {
                this.createTemplate(name)
            } else {
                this.$emit('createTemplate', name)
            }
        },
        handleSelectTemplate(template) {
            if (!this.settings_prop) {
                this.selectTemplate(template)
            } else {
                this.$emit('selectTemplate', template)
            }
        },
        handleEditTemplate(data) {
            if (!this.settings_prop) {
                this.editTemplate(data)
            } else {
                this.$emit('editTemplate', data)
            }
        },
        handleDeleteTemplate(id) {
            if (!this.settings_prop) {
                this.deleteTemplate(id)
            } else {
                this.$emit('deleteTemplate', id)
            }
        },
        handleClickCreateButton() {
            if (!this.settings_prop) {
                this.clickCreateButton()
            } else {
                this.$emit('clickCreateButton')
            }
        },
        handleClickAdditionalHeaderButton() {
            if (!this.settings_prop) {
                this.clickAdditionalHeaderButton()
            } else {
                this.$emit('clickAdditionalHeaderButton')
            }
        },
        handleConfirmFilter() {
            if (!this.settings_prop) {
                this.confirmFilter();
            } else {
                this.$emit('confirmFilter')
            }
        },
        handleClearFilter() {
            if (!this.settings_prop) {
                this.clearFilter();
            } else {
                this.$emit('clearFilter')
            }
        },
        handleChangePerPage(perPage) {
            if (!this.settings_prop) {
                this.changePerPage(perPage);
            } else {
                this.$emit('changePerPage', perPage)
            }
        },
        handleChangeCurrentPage(Page) {
            if (!this.settings_prop) {
                this.changeCurrentPage(Page);
            } else {
                this.$emit('changeCurrentPage', Page)
            }
        },
        handleExportFile() {
            if (!this.settings_prop) {
                this.exportFile();
            } else {
                this.$emit('exportFile')
            }
        }
    },
    computed: {
        ...mapGetters({ sidebarMode: 'getSidebarMode' }),
        settings() {
            return !this.settings_prop ? this.settings_inject : this.settings_prop
        },
        isShowTopBlock() {
            return this.settings.withTitle || this.settings.withFilters || this.settings.withCreateButton || this.settings.withHeader
        },
        isShowExportButton() {
            return this.settings.withExport && !this.settings.isNoEntries
        },
        filterTogglerText() {
            return this.isShowFilters ? 'Скрыть фильтры' : 'Показать фильтры'
        },
        bottomboxMargin() {
            return { 'margin-left': this.sidebarMode ? 'var(--max-sidebar-size)' : 'var(--min-sidebar-size)' };
        }
    }
}
</script>