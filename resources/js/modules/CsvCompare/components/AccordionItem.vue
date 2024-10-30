<template>
    <div class="accordion-item">
        <h2 @click="toggleItem()" class="accordion-header" id="headingOne">
            <button :class="{ 'collapsed': !isOpenned, 'bg-light': upload.chacked }" class="accordion-button" type="button"
                data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true"
                aria-controls="collapseOne">
                <div class="form-check">
                    <input :checked="upload.chacked" @click.stop="checkUpload()" class="form-check-input" type="checkbox"  id="flexCheckChecked" :disabled="isLoading">
                </div>
                {{ upload.upload_index }}: {{ formatDate(upload.created_at) }}
            </button>
        </h2>
        <Transition>
            <div id="collapseOne" :class="{ 'show': isOpenned }" class="accordion-collapse collapse"
                aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                <div class="accordion-body ps-0 pe-0">
                    <h6 class="card-title text-center">Новое</h6>
                    <div class="overflow-auto border border-start-0 border-end-0 mb-3 table-responsive"
                        style="font-size: 12px;">
                        <TablePlaceholder v-if="isLoading" />
                        <div v-else-if="!isLoading && !filteredNewAnomalies.length">
                            <h3 class="p-5 m-5 text-secondary text-center">
                                Нет расхождений
                            </h3>
                        </div>
                        <table v-else
                            class="table table-striped table-auto sticky-header table-bordered border-secondary ">
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
                                    <th scope="col">Комментарий</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="newAnomany in filteredNewAnomalies">
                                    <td> {{ newAnomany.date }} </td>
                                    <td> {{ newAnomany.kontragent }} </td>
                                    <td> {{ newAnomany.rs1 }} </td>
                                    <td> {{ newAnomany.naimenovanie_banka1 }} </td>
                                    <td> {{ newAnomany.tip_deneg }} </td>
                                    <td> {{ newAnomany.rs2 }} </td>
                                    <td> {{ newAnomany.naimenovanie_banka2 }} </td>
                                    <td> {{ newAnomany.tip_documenta }} </td>
                                    <td> {{ newAnomany.nomer_documenta }} </td>
                                    <td> {{ newAnomany.tip_operacii }} </td>
                                    <td> {{ newAnomany.opisanie }} </td>
                                    <td> {{ newAnomany.postuplenie }} </td>
                                    <td> {{ newAnomany.spisano }} </td>
                                    <td> {{ newAnomany.komissiya }} </td>
                                    <td> {{ newAnomany.usn_dohod }} </td>
                                    <td> {{ newAnomany.usn_rashod }} </td>
                                    <td> {{ newAnomany.patent }}</td>
                                    <td>
                                        <textarea v-model="newAnomany.comment"
                                            @change="updateComment(newAnomany.id, $event)" cols="30"></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- OLD -->
                    <h6 class="text-center card-title">Старое</h6>
                    <div class="overflow-auto border table-responsive border-start-0 border-end-0"
                        style="font-size: 12px;">
                        <TablePlaceholder v-if="isLoading" />
                        <div v-else-if="!isLoading && !filteredOldAnomalies.length">
                            <h3 class="p-5 m-5 text-secondary text-center">
                                Нет расхождений
                            </h3>
                        </div>
                        <table v-else
                            class="table table-striped  table-auto sticky-header table-bordered border-secondary">
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
                                    <th scope="col">Комментарий</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="oldAnomany in filteredOldAnomalies">
                                    <td> {{ oldAnomany.date }} </td>
                                    <td> {{ oldAnomany.kontragent }} </td>
                                    <td> {{ oldAnomany.rs1 }} </td>
                                    <td> {{ oldAnomany.naimenovanie_banka1 }} </td>
                                    <td> {{ oldAnomany.tip_deneg }} </td>
                                    <td> {{ oldAnomany.rs2 }} </td>
                                    <td> {{ oldAnomany.naimenovanie_banka2 }} </td>
                                    <td> {{ oldAnomany.tip_documenta }} </td>
                                    <td> {{ oldAnomany.nomer_documenta }} </td>
                                    <td> {{ oldAnomany.tip_operacii }} </td>
                                    <td> {{ oldAnomany.opisanie }} </td>
                                    <td> {{ oldAnomany.postuplenie }} </td>
                                    <td> {{ oldAnomany.spisano }} </td>
                                    <td> {{ oldAnomany.komissiya }} </td>
                                    <td> {{ oldAnomany.usn_dohod }} </td>
                                    <td> {{ oldAnomany.usn_rashod }} </td>
                                    <td> {{ oldAnomany.patent }}</td>
                                    <td>
                                        <textarea v-model="oldAnomany.comment"
                                            @change="updateComment(oldAnomany.id, $event)" cols="30"></textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Transition>
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
import { csvCompareAPI } from '../../../api/csv_compare';
import TablePlaceholder from '../UI/table-placeholder.vue';
export default {
    components: {
        TablePlaceholder
    },
    props: {
        filters: Object,
        upload: Object
    },
    data() {
        return {
            isOpenned: false,
            newAnomalyes: [],
            oldAnomalyes: [],
            isLoading: false,
        }
    },
    methods: {
        loadData: async function () {
            this.isLoading = true;
            const data = await csvCompareAPI.getData(this.upload.upload_index);
            this.newAnomalyes = await data.data.new_data_anomalies;
            this.oldAnomalyes = await data.data.old_data_anomalies;
            this.isLoading = false;
        },

        async updateComment(id, event) {
            await csvCompareAPI.updateComment(id, event.target.value);
            this.showToast("ОК", "Комментарий обновлён", "success");
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

        async toggleItem() {
            this.isOpenned = !this.isOpenned
            if (this.isOpenned) {
                await this.loadData();
            }
        },

        async checkUpload(){
            this.isLoading = true;
            await csvCompareAPI.checkUpload(this.upload.upload_index);
            this.upload.chacked = !this.upload.chacked
            this.isLoading = false;
        }
    },

    computed: {
        filteredNewAnomalies() {
            return this.filterAnomalies(this.newAnomalyes);
        },
        filteredOldAnomalies() {
            return this.filterAnomalies(this.oldAnomalyes);
        }
    }
}
</script>