<template>

    <EntityEditPage :isLoaded="layoutSettings.isLoaded" :withDeleteButton="layoutSettings.withDeleteButton">
        <template v-slot:header><h1>Юридические лица и способы оплаты</h1></template>
        <template v-slot:content>
            <div class="d-flex flex-row w-100">
                <Card :titleBorder="false" class="w-50 me-2 d-flex flex-column" title="Юридические лица">
                    <Table class="w-100" style="border: 0!important">
                        <template v-slot:thead>
                            <TH> # </TH>
                            <TH> Юр. лицо </TH>
                        </template>
                        <template v-slot:tbody>
                            <TR v-if="legalEntities.length" @click_row="selectLegalEntity(legalEntity)"
                                v-for="legalEntity, index in legalEntities" :key="legalEntity">
                                <TD :class="{
                                    'bg-primary bg-gradient text-white': selectedLegalEntity ? selectedLegalEntity.id == legalEntity.id : false
                                    }"> {{ index + 1 }} </TD>
                                <TD :class="{
                                    'bg-primary bg-gradient text-white': selectedLegalEntity ? selectedLegalEntity.id == legalEntity.id : false
                                    }"> {{ legalEntity.name }} </TD>
                            </TR>
                            <NoEntries v-else></NoEntries>
                        </template>
                    </Table>
                </Card>
                <Card :titleBorder="canUserCreate" class="w-50 ms-2 d-flex flex-column" title="Способы оплаты">
                    <Table v-if="selectedLegalEntity" class="w-100" style="border: 0!important">
                        <template v-slot:info v-if="canUserCreate">
                            <div class="w-100 d-flex justify-content-end">
                                <AddButton @click="openAddPaymentMethodInput()" class="m-1" :buttonTitle="'Добавить способ оплаты'"></AddButton>
                            </div>
                        </template>
                        <template v-slot:thead>
                            <TH width="350"> Способ оплаты </TH>
                            <TH width="350"> Тип оплаты </TH>
                            <TH>  </TH>
                        </template>
                        <template v-slot:tbody>
                            <TR v-if="addingPaymentMethodInput">
                                <TD>
                                    <Input style="margin: 0!important;" placeholder="Введите способ оплаты" v-model="newPaymentMethod.name"></Input>
                                </TD>
                                <TD>
                                    <Select placeholder="Выберите тип оплаты" style="margin: 0!important; width:100%!important" v-model="newPaymentMethod.type" :options="paymentMethodsType"></Select>
                                </TD>
                                <TD>
                                    <button @click="addPaymentMethod(newPaymentMethod)" class="btn btn-outline-success" type="button" :disabled="newPaymentMethod.name === '' || newPaymentMethod.type === null">
                                        <font-awesome-icon icon="fa-check"></font-awesome-icon>
                                    </button>
                                </TD>
                            </TR>
                            <TR v-for="paymentMethod in paymentMethods" :key="paymentMethod">
                                <template v-if="selectedLegalEntity.id == paymentMethod.legal_entity_id">
                                    <TD> {{ paymentMethod.name }} </TD>
                                    <TD> {{ paymentMethod.type ? 'Безналичными' : 'Наличными' }} </TD>
                                    <TD>
                                        <button v-if="canUserEdit" class="btn btn-outline-primary border-0" @click="openEditPaymentMethodModal(paymentMethod)">
                                            <font-awesome-icon icon="fa-regular fa-pen-to-square"></font-awesome-icon>
                                        </button>
                                    </TD>
                                </template>
                            </TR>
                        </template>
                    </Table>
                    <div v-else class="pt-5 d-flex justify-content-center w-100">Выберите юр. лицо, чтобы посмотреть способы оплаты</div>
                </Card>
                <DefaultModal @close_modal="closeEditPaymentMethodModal()" title="Редактирование способа оплаты" width="600px" v-if="editPaymentMethodModal">
                    <template v-slot:default>
                        <div class="m-3 d-flex">
                            <Input style="width: 48%!important;" label="Название" v-model="editingPaymentMethod.name"></Input>
                            <Select style="width: 48%!important;" label="Тип оплаты" v-model="editingPaymentMethod.type" :options="paymentMethodsType"></Select>
                        </div>
                        <div class="d-flex justify-content-between m-3 mt-0">
                            <div>
                                <button :disabled="savePosibillity" type="button" @click="updatePaymentMethod(editingPaymentMethod)" class="btn btn-primary">Сохранить</button>
                                <button type="button" @click="closeEditPaymentMethodModal()" class="btn btn-light border ms-2">Отмена</button>
                            </div>
                            <!-- <button v-if="canUserDestroy" type="button" @click="deletePaymentMethod(editingPaymentMethod)" class="btn btn-danger">Удалить</button> -->
                        </div>
                    </template>
                </DefaultModal>
            </div>
        </template>
    </EntityEditPage>

</template>

<script>

import { legalEntityAPI } from '../api/legal_entity_api'
import { paymentMethodAPI } from '../api/payment_method_api'

import Table from '../components/Tables/main_table.vue';
import TH from '../components/Tables/th.vue';
import TD from '../components/Tables/td.vue';
import TR from '../components/Tables/tr.vue';
import NoEntries from '../components/Tables/no_entries.vue';
import AddButton from '../components/buttons/add_button.vue';
import TrashButton from '../components/inputs/trash_button.vue';

import Input from '../components/inputs/filter_input.vue';
import Select from '../components/inputs/FilterSelect.vue';

import EntityEditPage from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';
import DefaultModal from '../components/modals/default_modal.vue';
import DestroyConfirmModal from '../components/modals/DestroyConfirmModal.vue';

export default {
    components: { EntityEditPage, Card, Table, TH, TD, TR, NoEntries, AddButton, TrashButton, DestroyConfirmModal, Input, Select, DefaultModal },
    data() {
        return {
            layoutSettings: {
                isLoaded: false,
                withDeleteButton: false
            },
            legalEntities: [],
            paymentMethods: [],
            paymentMethodsType: [
                {id: 0, name: 'Наличными'},
                {id: 1, name: 'Безналичными'}
            ],
            selectedLegalEntity: null,
            addingPaymentMethodInput: false,
            newPaymentMethod: {
                name: '',
                type: null
            },
            editPaymentMethodModal: false,
            editingPaymentMethod: {
                id: null,
                name: '',
                type: null
            },
        }
    },
    methods: {
        selectLegalEntity(legalEntity) {
            this.selectedLegalEntity = this.selectedLegalEntity == null ? legalEntity : (this.selectedLegalEntity.id == legalEntity.id ? null : legalEntity);
        },
        async indexLegalEntities() {
            const res = await legalEntityAPI.index();
            return res.data.data;
        },
        async indexPaymentMethods() {
            const res = await paymentMethodAPI.index();
            return res.data.data;
        },
        async indexData() {
            this.selectedLegalEntity = null;
            this.newPaymentMethod.name = '';
            this.newPaymentMethod.type = null;
            this.editingPaymentMethod.name = null;
            this.editingPaymentMethod.type = null;
            const resLegalEntities = await this.indexLegalEntities();
            const resPaymentMethods = await this.indexPaymentMethods();
            this.legalEntities = resLegalEntities;
            this.paymentMethods = resPaymentMethods;
            this.layoutSettings.isLoaded = true;
        },
        openAddPaymentMethodInput() {
            this.addingPaymentMethodInput = true;
        },
        async addPaymentMethod(method) {
            this.layoutSettings.isLoaded = false;
            this.addingPaymentMethodInput = false;
            const data = {
                legal_entity_id: this.selectedLegalEntity.id,
                name: method.name,
                type: method.type,
            };
            await paymentMethodAPI.store(data).then((res) => {
                this.indexData();
            })
        },
        async updatePaymentMethod(method) {
            this.layoutSettings.isLoaded = false;
            await paymentMethodAPI.update(method.id, method).then((res) => {
                this.closeEditPaymentMethodModal();
                this.indexData();
            })
        },
        async deletePaymentMethod(method) {
            this.layoutSettings.isLoaded = false;
            await paymentMethodAPI.destroy(method.id).then((res) => {
                this.closeEditPaymentMethodModal();
                this.indexData();
            })
        },
        openEditPaymentMethodModal(method) {
            this.editingPaymentMethod.id = method.id;
            this.editingPaymentMethod.name = method.name;
            this.editingPaymentMethod.type = method.type;
            this.editPaymentMethodModal = true;
        },
        closeEditPaymentMethodModal() {
            this.editingPaymentMethod.name = null;
            this.editingPaymentMethod.type = null;
            this.editPaymentMethodModal = false;
        }
    },
    mounted() {
        this.indexData();
    },
    computed: {
        canUserCreate() {
            return this.checkPermission('legal_entity_create')
        },
        canUserEdit() {
            return this.checkPermission('legal_entity_update')
        },
        canUserDestroy() {
            return this.checkPermission('legal_entity_delete')
        },
        savePosibillity() {
            return this.editingPaymentMethod.name === '' || this.editingPaymentMethod.type === null
        }
    }
}

</script>
