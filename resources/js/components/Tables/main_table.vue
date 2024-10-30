<template>
    <div class="border-start border-end border-bottom rounded-1 rounded-bottom">
        <div v-if="tableSettings.withFilters" class="p-3 bg-white" style="position: relative;">
            <div v-show="tableSettings.isCover" class="filter-cover text-center rounded-bottom" >
                <div style="margin-top: 100px;" class="d-flex flex-column">
                    <div>
                        <div class="text-primary pe-2 position-fixed opacity-75" role="status" ></div>
                    </div>
                </div>
            </div>   
            <div class="d-flex flex-row align-items-center justify-content-between">
                <div class="d-flex flex-row align-items-center" style="margin-left: 10px">
                    <h2>{{ tableSettings.tableTitle }}</h2>
                    <a @click.prevent="onToggleFilers()" href="" class="ps-3" style="text-decoration: none;">{{ filterText() }}</a>
                </div>
                <div v-if="withHeadeSection" class="d-flex flex-row align-items-center">
                    <slot name="header">
                        <button @click="onAddingElement()" type="button" class="btn btn-primary border-light fs-5 align-middle d-flex align-items-center p-2 ps-3 pe-3 me-2">
                            <font-awesome-icon icon="fa-solid fa-plus" class="me-2" />
                            <span style="font-weight: 500;">Создать</span>
                        </button>
                        <button v-if="tableSettings.withAdditionalButton" @click="onAdditionalButton()" type="button"
                            class="btn btn-primary border-light fs-5 align-middle d-flex align-items-center p-2 ps-3 pe-3">
                            <font-awesome-icon icon="fa-solid fa-plus" class="me-2" />
                            <span style="font-weight: 500;">{{ tableSettings.additionalButtonTitle }}</span>
                        </button>
                    </slot>

                </div>
            </div>
            <Transition>
                <form v-if="isShowFilters " @submit.prevent="">
                    <div class="d-flex flex-row flex-wrap filter-wrapper bg-white mb-3 mt-3">
                        <slot name="filters"></slot>
                    </div>
                    <button @click="onConfirmFilter()" type="submit" class="btn btn-primary btn-sm  me-1" :disabled="tableSettings.isLoading" style="margin-left: 10px">Применить</button>
                    <button v-if="isShowClearButton" @click="onClearFilter()" type="button" class="btn btn-sm btn-light me-1 border text-danger" :disabled="tableSettings.isLoading">
                        <font-awesome-icon icon="fa-solid fa-xmark" class="me-1" />Очистить
                    </button>
                </form>
            </Transition>

        </div>
        <slot name="info"></slot>
        <div class="w-100 h-100" style="position: relative;">
            <div v-if="tableSettings.isCover && !tableSettings.isLoading" class="table-cover text-center rounded-bottom" style="z-index: 5000;">
                <div style="margin-top:260px;" class="d-flex flex-column">
                    <div>
                        <div class="spinner-border text-primary pe-2 position-fixed opacity-75" role="status" ></div>
                    </div>
                </div>
            </div>
            <table ref="mainTable" class="table m-0 table-hover main-table">
                <thead class="align-middle border-top border-bottom bg-light shadow-sm main-th" style="font-size: 12px; height: 41px; position: sticky; top: 0; z-index: 500;">
                    <td v-if="tableSettings.isLoading || tableSettings.isNoEntries"></td>
                    <slot v-if="!tableSettings.isLoading && !tableSettings.isNoEntries" name="thead"></slot>
                </thead>
                <tbody class="align-middle bg-white" style="font-size: 13px;">
                    <IsLoading :tableHeight="tableSettings.tableHeight" v-if="tableSettings.isLoading"></IsLoading>
                    <NoEntries v-else-if="tableSettings.isNoEntries"></NoEntries>
                    <slot v-else name="tbody"></slot>
                </tbody>
                <tfoot class="border-bottom bg-light border-top" v-if="tableSettings.withFooter" :style="tfootStyle">
                    <tr>
                        <td colspan="100%" class="text-muted text-end" :class="{'border-top': tableSettings.isLoading}" style="height: 39px; font-size: 13px;">
                            <span style="margin: 13px 20px 0 10px;">
                                <slot name="tfoot"></slot>
                            </span>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <div class="d-flex flex-row bg-white rounded-1">
                <PerPage v-if="meta" :meta="meta" :perPageVuluesArray="[25, 50, 100]" @per_page="onChangePerPage($event)"></PerPage>
                <div v-if="tableSettings.withExport && !tableSettings.isNoEntries" class="p-3 border-end" :class="{'placeholder-glow': tableSettings.isLoading}" style="width: 185px; font-size: 14px;">
                    <a href="" style="text-decoration: none;" @click.prevent="onExport()" :class="{'placeholder rounded pe-none user-select-none': tableSettings.isLoading}"><font-awesome-icon icon="fa-solid fa-download"/> Выгрузить таблицу</a>
                </div>
                <Pagination v-if="meta" :meta="meta" @current_page="onChangeCurrentPage($event)"></Pagination>
            </div>
        </div>
    </div>
</template>
<style>
    thead td, tfoot td{
        padding: 0.5rem 0.5rem;
    }
    tbody td{
        height: 63px;
        max-height: 80px;
    }
    tbody tr{
        --bs-border-width: 2px!important;
        border-left: var(--bs-border-width) var(--bs-border-style); 
        border-right: var(--bs-border-width) var(--bs-border-style); 
        border-left-color: #0d6dfd00!important;
        border-right-color: #0d6dfd00!important;
    }
    .main-table{
        position: relative;
    }
    .filter-cover{
        transition: all 0.2s;
        background-color: rgba(230, 230, 230, 0.267);
        position: absolute;
        margin-top: -1rem;
        margin-left: -1rem;
        width: 100%;
        height: 100%;
        z-index: 1000;
    }
    .table-cover{
        transition: all 0.2s;
        background-color: rgba(230, 230, 230, 0.267);
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 1000;
    }
    .main-table{
        border-collapse: separate;
        border-spacing: 0;
    }
    .main-table .main-th td{
        border-top: 1px var(--bs-border-color) solid;
        border-bottom: 1px var(--bs-border-color) solid;
    }
    .filter-wrapper .filter{
        min-width: fit-content;
        padding-left: 10px;
        padding-right: 10px;
    }
    /* .filter-wrapper .filter:nth-child(6n+1){
        padding-left: 0px;
    }
    .filter-wrapper .filter:nth-child(6n){
        padding-right: 0px;
    } */
</style>
<script>
    import IsLoading from './is_loading.vue'
    import NoEntries from './no_entries.vue'
    import PerPage from './per_page.vue'
    import Pagination from './pagination.vue'
    import PageWrapper from '../../components/Layout/page_wrapper.vue';
    import AddButton from '../buttons/add_button.vue';
    export default {
        props: {
            meta: Object,
            tableSettings:{
                type: Object,
                default:{
                    tableTitle: null,
                    tableHeight: null,
                    isLoading: false,
                    isNoEntries: false,
                    isCover: false,
                    withFilters: false,
                    withExport: false,
                    withFooter: true,
                    isStickyFooter: false,
                    withAdditionalButton: false,
                    additionalButtonTitle: 'Вторая кнопка'
                }
            },
            withHeadeSection: false,
        },
        data(){
            return{
                isShowFilters: true,
                isShowClearButton: false,
            }
        },
        components:{IsLoading, NoEntries, PerPage, Pagination, PageWrapper, AddButton},
        methods:{
            filterText(){
                if(this.isShowFilter) return 'Скрыть фильтры';
                return 'Показать фильтры';
            },
            onChangePerPage: function(event){
                this.$emit('per_page', event);
            },
            onChangeCurrentPage(event){
                this.$emit('current_page', event);
            },
            onExport(event){
                this.$emit('export', event);
            },
            onToggleFilers(){
                this.isShowFilters = !this.isShowFilters;
            },
            onConfirmFilter() {
                this.isShowClearButton = true;
                this.$emit('confirm_filter');
            },
            onClearFilter() {
                this.isShowClearButton = false;
                this.$emit('clear_filter');
            },
            onAddingElement() {
                this.$emit('add_element');
            },
            onAdditionalButton() {
                this.$emit('additional_button_trigger');
            }
        },
        computed:{
            tfootStyle(){
                if (this.tableSettings.isStickyFooter) return {position: 'sticky', bottom: '0'}
            }
        }
    }
</script>