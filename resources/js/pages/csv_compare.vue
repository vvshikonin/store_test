<template>
    <div>
        <div class="container-xxl mt-3">
            <div class="card">
                <div class="card-body">

                    <!-- TABDS -->
                    <ul class="nav nav-tabs mb-2 flex-nowrap" style="overflow-x: auto; overflow-y: hidden;">
                        <li class="nav-item">
                            <a @click.prevent href="#" class="nav-link active" aria-current="page">Деньги</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled">Контрагенты (в работе)</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link disabled">Реквизиты (в работе)</a>
                        </li>
                    </ul>

                    <div class="mb-3">
                        <label for="formFile" class="form-label">
                            Загрузить файл
                            <div v-if="isLoading" class="spinner-border text-primary spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </label>

                        <input @change="uploadFile" :disabled="isLoading" class="form-control" type="file"
                            id="formFile">
                    </div>
                    <button @click="removeAll()" :disabled="isLoading" type="button" class="btn btn-sm btn-danger">
                        Очистить всё
                    </button>

                    <!-- FILTERS -->
                    <hr />
                    <h6 class="mt-3">Фильтры</h6>
                    <div class="container-xxl" style="font-size: 12px;">
                        <div class="row row-cols-auto">
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Дата</label>
                                    <input v-model="filters.date" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Контрагент</label>
                                    <input v-model="filters.kontragent" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">р/с 1</label>
                                    <input v-model="filters.rs1" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Наименование банка
                                        1</label>
                                    <input v-model="filters.naimenovanie_banka1" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Тип денег
                                        (Банк/касса/прочее)</label>
                                    <input v-model="filters.tip_deneg" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">р/с 2</label>
                                    <input v-model="filters.rs2" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Наименование банка
                                        2</label>
                                    <input v-model="filters.naimenovanie_banka2" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Тип документа</label>
                                    <input v-model="filters.tip_documenta" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">№ документа</label>
                                    <input v-model="filters.nomer_documenta" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Тип операции</label>
                                    <input v-model="filters.tip_operacii" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Описание</label>
                                    <input v-model="filters.opisanie" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Поступило</label>
                                    <input v-model="filters.postuplenie" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Списано</label>
                                    <input v-model="filters.spisano" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Комиссия</label>
                                    <input v-model="filters.komissiya" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Учитывается в налоге УСН
                                        (доход)</label>
                                    <input v-model="filters.usn_dohod" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Учитывается в налоге УСН
                                        (расход)</label>
                                    <input v-model="filters.usn_rashod" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Учитывается в
                                        патенте</label>
                                    <input v-model="filters.patent" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                            <div class="col">
                                <div class="mb-3">
                                    <label for="exampleFormControlInput1" class="form-label">Комментарий</label>
                                    <input v-model="filters.comment" :disabled="isLoading" type="email"
                                        class="form-control form-control-sm" id="exampleFormControlInput1">
                                </div>
                            </div>
                        </div>
                        <button @click="initFilters()" :disabled="isLoading" type="button"
                            class="btn btn-sm btn-primary">Очистить</button>
                    </div>

                    <!-- ACTUAL TABLE -->
                    <div v-if="actualUpload.length" class="mt-3">
                        <h6 class="card-title text-center">Последняя загрузка</h6>

                        <div v-if="isLoading" style="font-size: 12px;">
                            <div class="container-sm placeholder-glow">
                                <p aria-hidden="true">
                                    <span class="placeholder col-2 me-1"></span>
                                    <span class="placeholder col-2 me-1"></span>
                                </p>
                                <p aria-hidden="true">
                                    <span class="placeholder col-2 me-1"></span>
                                    <span class="placeholder col-1 me-1"></span>
                                    <span class="placeholder col-1"></span>
                                </p>

                            </div>
                        </div>

                        <div v-else class="mt-2 mb-2 " style="font-size: 12px; width: fit-content;">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Учитывается в налоге УСН (расход):</th>
                                        <td>{{ usnRashodSum.priceFormat(true) }}</td>
                                        <th>Списано:</th>
                                        <td> {{ spisanoSum.priceFormat(true) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Учитывается в налоге УСН (доход):</th>
                                        <td>{{ usnDohodSum.priceFormat(true) }}</td>
                                        <th>Комиссия:</th>
                                        <td>{{ komissiyaSum.priceFormat(true) }}</td>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div class="overflow-auto border table-responsive" style="font-size: 12px;">
                            <TablePlaceholder v-if="isLoading" />
                            <table v-else
                                class="table table-striped table-auto sticky-header table-bordered border-secondary">
                                <thead>
                                    <tr>
                                        <th scope="col">Дата</th>
                                        <th scope="col">Контрагент</th>
                                        <th scope="col">р/с 1</th>
                                        <th scope="col">Наименование банка 1</th>
                                        <th scope="col">Тип денег (Банк/касса/прочее)</th>
                                        <th scope="col">р/с 2</th>
                                        <th scope="col">Наименование банка 2</th>
                                        <th scope="col">Тип документа</th>
                                        <th scope="col">№ документа</th>
                                        <th scope="col">Тип операции</th>
                                        <th scope="col">Описание</th>
                                        <th scope="col">Поступило</th>
                                        <th scope="col">Списано</th>
                                        <th scope="col">Комиссия</th>
                                        <th scope="col">Учитывается в налоге УСН (доход)</th>
                                        <th scope="col">Учитывается в налоге УСН (расход)</th>
                                        <th scope="col">Учитывается в патенте</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="actual in filteredActual">
                                        <td> {{ actual.date }} </td>
                                        <td> {{ actual.kontragent }} </td>
                                        <td> {{ actual.rs1 }} </td>
                                        <td> {{ actual.naimenovanie_banka1 }} </td>
                                        <td> {{ actual.tip_deneg }} </td>
                                        <td> {{ actual.rs2 }} </td>
                                        <td> {{ actual.naimenovanie_banka2 }} </td>
                                        <td> {{ actual.tip_documenta }} </td>
                                        <td> {{ actual.nomer_documenta }} </td>
                                        <td> {{ actual.tip_operacii }} </td>
                                        <td> {{ actual.opisanie }} </td>
                                        <td> {{ actual.postuplenie }} </td>
                                        <td> {{ actual.spisano }} </td>
                                        <td> {{ actual.komissiya }} </td>
                                        <td> {{ actual.usn_dohod }} </td>
                                        <td> {{ actual.usn_rashod }} </td>
                                        <td> {{ actual.patent }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div v-if="uploads.length" class="mt-5">
                        <h6 class="card-title text-center">История</h6>
                        <Accordion :uploads="uploads" :filters="filters" />
                    </div>

                </div>
            </div>
        </div>
    </div>

</template>
<style scoped>
.table-auto {
    table-layout: auto;
    width: auto;
}

.table-auto th,
.table-auto td {
    white-space: nowrap;
}

.table-responsive {
    max-height: 80vh;
}

.sticky-header th {
    position: sticky;
    top: 0;
    background-color: #fff;
    z-index: 1020;
}

.table-bordered.border-secondary td {
    border-color: rgba(var(--bs-secondary-rgb), var(--bs-border-opacity)) !important;
}
</style>
<script>
import CsvCompare from '../modules/CsvCompare/CsvCompare.vue';
import { csvCompareAPI } from '../api/csv_compare';
import { makeFormData } from '../utils/objects';
import Accordion from '../modules/CsvCompare/components/Accordion.vue';
import TablePlaceholder from '../modules/CsvCompare/UI/table-placeholder.vue';
export default {
    components: {
        CsvCompare,
        Accordion,
        TablePlaceholder
    },
    data() {
        return {
            isLoading: false,
            filters: {},
            uploads: [],
            actualUpload: [],
        }
    },
    async mounted() {
        this.initFilters();
        await this.getUploads();
        await this.getActualUpload();
    },
    methods: {

        uploadFile: async function (event) {
            this.newAnomalyes = [];
            this.oldAnomalyes = [];
            this.initFilters();
            this.isLoading = true;
            const file = event.target.files[0];
            const data = {
                file
            }
            makeFormData(data);
            await csvCompareAPI.uploadFile(makeFormData(data));
            event.target.value = ''
            await this.getUploads();
            await this.getActualUpload();
            this.isLoading = false;
        },
        initFilters() {
            this.filters = {
                comment: '',
                date: '',
                komissiya: '',
                kontragent: '',
                naimenovanie_banka1: '',
                naimenovanie_banka2: '',
                nomer_documenta: '',
                opisanie: '',
                patent: '',
                postuplenie: '',
                rs1: '',
                rs2: '',
                spisano: '',
                tip_deneg: '',
                tip_documenta: '',
                tip_operacii: '',
                usn_dohod: '',
                usn_rashod: '',
            }
        },
        async getUploads() {
            const data = await csvCompareAPI.getUploads();
            this.uploads = data.data;
        },

        removeAll: async function () {
            this.isLoading = true;
            await csvCompareAPI.removeAll();
            await this.getUploads();
            await this.getActualUpload();
            this.isLoading = false;
        },

        getActualUpload: async function () {
            this.isLoading = true;
            const data = await csvCompareAPI.getActualUpload();
            this.actualUpload = data.data;
            this.isLoading = false;
        },

        filterAnomalies(anomalies) {
            return anomalies.filter(anomaly => {
                return Object.keys(this.filters).every(key => {
                    const filterValue = this.filters[key].toLowerCase();
                    if (!filterValue) return true;

                    const anomalyValue = anomaly[key] && anomaly[key].toLowerCase();
                    return anomalyValue && anomalyValue.includes(filterValue);
                });
            });
        },
    },

    computed: {
        filteredActual() {
            return this.filterAnomalies(this.actualUpload);
        },

        spisanoSum() {
            return this.actualUpload.reduce((acc, item) => acc + (parseFloat(item.spisano?.replace(',', '.')) || 0), 0);
        },

        komissiyaSum() {
            return this.actualUpload.reduce((acc, item) => acc + (parseFloat(item.komissiya?.replace(',', '.')) || 0), 0);
        },

        usnDohodSum() {
            return this.actualUpload.reduce((acc, item) => acc + (parseFloat(item.usn_dohod?.replace(',', '.')) || 0), 0);
        },

        usnRashodSum() {
            return this.actualUpload.reduce((acc, item) => acc + (parseFloat(item.usn_rashod?.replace(',', '.')) || 0), 0);
        },
    }
}

</script>
