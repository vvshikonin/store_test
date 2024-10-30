<template>
    <EntityLayout :loadingCover="isCover" :isLoaded="isLoaded" :withSaveButton="canUserEdit"
        :withDeleteButton="canUserDelete" @save="onSave()" @exit="onExit()" @destroy="deleteContractor(contractor.id)">
        <template v-slot:header>
            <span v-if="isEditing">
                <h3 class="ms-3">Поставщик {{ contractor.name }}</h3>
            </span>
            <span v-else>
                <h3 class="ms-3">Новый поставщик</h3>
            </span>
        </template>
        <template v-slot:content>
            <div class="d-flex justify-content-between">
                <div class="col-md-6 pe-3">
                    <Card class="contractor-main-info" title="Основные данные">
                        <div>
                            <inputDefault class="w-100" :required="true" v-model.lazy="newContractorName"
                                :disabled="!canUserEdit" label="Имя"></inputDefault>
                            <inputDefault class="w-100" :required="true" v-model.lazy="newContractorMarginality"
                                :disabled="!canUserEdit" label="% наценки" type="number" :step="1" :min="0" :max="100">
                            </inputDefault>
                            <inputDefault class="w-100" v-model="newContractorLegalEntity"
                                :disabled="!canUserEdit" label="Юр.лицо">
                            </inputDefault>
                            <!-- Новые поля -->
                            <inputDefault class="w-100" v-model.lazy="contractor.min_order_amount"
                                :disabled="!canUserEdit" label="Минимальная сумма заказа" type="number" :step="0.01">
                            </inputDefault>
                            <div class="mb-3">
                                <label for="pickup_time" class="form-label ms-3">Время забора</label>
                                <textarea id="pickup_time" class="form-control ms-3" v-model.lazy="contractor.pickup_time"
                                    :disabled="!canUserEdit" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="warehouse" class="form-label ms-3">Склад</label>
                                <textarea id="warehouse" class="form-control ms-3" v-model.lazy="contractor.warehouse"
                                    :disabled="!canUserEdit" rows="3"></textarea>
                            </div>
                            <Checkbox class="w-100" v-model="contractor.payment_delay" :disabled="!canUserEdit"
                                title="Отсрочка платежа"></Checkbox>
                            <div class="mb-3">
                                <label for="delay_info" class="form-label ms-3">Информация об отсрочке</label>
                                <textarea id="delay_info" class="form-control ms-3" v-model.lazy="contractor.payment_delay_info"
                                    :disabled="!canUserEdit" rows="3"></textarea>
                            </div>
                            <Checkbox class="w-100" v-model="contractor.has_delivery_contract" :disabled="!canUserEdit"
                                title="Есть договор доставки"></Checkbox>
                            <!-- Конец новых полей -->
                            <Checkbox class="w-100" v-model="contractor.is_main_contractor" :disabled="!canUserEdit"
                                title="Основной поставщик"></Checkbox>
                            <div class="mb-3">
                                <label for="working_conditions" class="form-label ms-3">Особые моменты</label>
                                <textarea id="working_conditions" class="form-control ms-3" v-model.lazy="contractor.working_conditions"
                                    :disabled="!canUserEdit" rows="3"></textarea>
                            </div>
                        </div>
                        <div>
                        </div>
                    </Card>
                </div>
                <div class="col-md-6">
                    <Card class="contractor-code-table" title="Символьные коды поставщика">
                        <div class="w-100 d-flex justify-content-end p-3">
                            <AddButton v-if="canUserEdit" @on_click="newCodeWindow()" label="Добавить код"></AddButton>
                        </div>
                        <MainTable :tableSettings="tableSettings" class="border-start-0 border-end-0 border-bottom-0"
                            style="min-width: min-content; width: 100%">
                            <template v-slot:thead>
                                <TableHeader>Код</TableHeader>
                                <TableHeader width="50px">
                                    <!-- пустой хэдер для ячейки с кнопкой удаления -->
                                </TableHeader>
                            </template>
                            <template v-slot:tbody>
                                <TableRow v-for="code, index in contractor.symbolic_code_list" :key="code">
                                    <TableCell class="contractor-code-table-cell">
                                        <div>
                                            <EditInput v-if="canUserEdit" @cancel="cancelEditExistingCode(index)"
                                                @update:content="editCode($event, index)"
                                                v-model:content="contractor.symbolic_code_list[index]"></EditInput>
                                            <span v-else>{{ contractor.symbolic_code_list[index] }}</span>
                                            <div class="text-danger" v-if="symbolCodeCheck[index]">Такой код поставщика
                                                уже используется!</div>
                                        </div>
                                    </TableCell>
                                    <TableCell width="50px">
                                        <TrashButton v-if="canUserEdit" @on_click="deleteCode(code)"></TrashButton>
                                    </TableCell>
                                </TableRow>
                            </template>
                        </MainTable>
                    </Card>
                </div>
            </div>
            <ModalWindow v-if="symCodeCreating" @close_modal="closeModal()" width="500px" title="Добавить новый код">
                <template v-slot>
                    <div class="d-flex p-3 h-25 flex-column justify-content-between">
                        <div>Введите новый символьный код поставщика</div>
                        <div><input class="form-control" v-model="newSymCode" /></div>
                        <div class="text-danger" v-if="newSymbolCodeCheck">Такой код поставщика уже используется!</div>
                        <div>
                            <button type="button" class="btn btn-primary mt-2 me-2"
                                @click="createNewCode(newSymCode)">Создать</button>
                            <button type="button" class="btn-light btn border bg-gradient border-1 mt-2"
                                @click="closeModal()">Отмена</button>
                        </div>
                    </div>
                </template>
            </ModalWindow>
        </template>
    </EntityLayout>
</template>
<style>
.contractor-main-info div.m-3.me-5 {
    width: 100% !important;
}

.contractor-code-table-cell>span,
.contractor-code-edit-table-cell>span {
    display: flex;
    align-items: center;
}

.contractor-code-table tfoot span {
    display: flex;
    justify-content: flex-end;
    margin: 10px !important;
}

.contractor-code-table tbody .edit-input-field * {
    font-size: 13px;
}
</style>
<script>
import { contractorAPI } from '../api/contractor_api';

import inputDefault from '../components/inputs/default_input.vue';
import Checkbox from '../ui/checkboxes/DefaultCheckbox.vue'
import MainTable from '../components/Tables/main_table.vue';
import TableHeader from '../components/Tables/th.vue';
import TableRow from '../components/Tables/tr.vue';
import TableCell from '../components/Tables/td.vue';
import EntityLayout from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';
import ModalWindow from '../components/modals/default_modal.vue';
import EditInput from '../components/inputs/table_edit_input.vue';
import AddButton from '../components/inputs/add_button.vue';
import TrashButton from '../components/inputs/trash_button.vue';
import TextAreaPopup from '../components/popups/table_textarea_popup.vue';

export default {
    data() {
        return {
            symCodeCreating: false,
            contractor: {},
            isLoaded: false,
            isCover: false,
            isSaving: false,
            isEditingCodes: false,
            newContractorName: null,
            newSymCode: null,
            newSymbolCodeCheck: null,
            newContractorLegalEntity: null,
            symbolCodeCheck: [],
            initialCodes: [],
            symbolicCodeListOnCreate: [],
            saveDisabled: true,
            tableSettings: {
                withFooter: false,
            }
        }
    },
    components: { inputDefault, MainTable, TableHeader, TableRow, TableCell, EntityLayout, Checkbox, Card, ModalWindow, EditInput, AddButton, TrashButton, TextAreaPopup },

    methods: {
        initNewContractor() {
            this.newContractorName = "Новый поставщик";
            this.contractor.symbolic_code_list = this.symbolicCodeListOnCreate;
        },
        async createContractor() {
            if (!this.newContractorName) {
                this.showToast('Имя поставщика', 'Имя поставщика не может быть пустым!', 'danger');
            } else {
                this.isCover = true;
                this.contractor.name = this.newContractorName;
                this.contractor.marginality = this.newContractorMarginality;
                this.contractor.is_main_contractor = this.contractor.is_main_contractor ? 1 : 0;
                this.contractor.legal_entity = this.newContractorLegalEntity;
                await contractorAPI.store(this.contractor).then((res) => {
                    this.$router.push('/contractors/' + res.data.data.id + '/edit');
                    this.contractor = res.data.data;
                    this.showToast('Создание', 'Поставщик успешно добавлен!', 'success');
                    this.isCover = false;
                })
            }
        },
        async loadContractor() {
            const id = this.$route.params.contractor_id;
            await contractorAPI.show(id).then((res) => {
                this.contractor = res.data.data;
                this.newContractorName = this.contractor.simple_name;
                this.newContractorMarginality = this.contractor.marginality;
                this.newContractorLegalEntity = this.contractor.legal_entity;
                this.contractor.symbolic_code_list.forEach((e) => {
                    this.symbolCodeCheck.push(0);
                    this.initialCodes.push(e);
                });
                this.isLoaded = true;
            });
        },
        onSave() {
            this.isEditing ? this.saveContractor() : this.createContractor();
            this.isSaving = true;
        },
        onExit() {
            if (!this.isSaving) {
                this.showToast('Отмена', 'Отмена изменений. Возвращение к последнему сохраненному состоянию.', 'info');
            }
            this.$router.push('/contractors');
        },
        async saveContractor() {
            this.isCover = true;
            this.contractor.name = this.newContractorName;
            this.contractor.marginality = this.newContractorMarginality;
            this.contractor.legal_entity = this.newContractorLegalEntity;
            this.contractor.is_main_contractor = this.contractor.is_main_contractor ? 1 : 0;
            await contractorAPI.update(this.contractor).then(() => {
                this.showToast('Сохранение', 'Поставщик успешно сохранен!', 'success');
                this.saveDisabled = true;
                this.isSaving = false;
                this.isEditingCodes = false;
                this.isCover = false;
            })
        },
        closeModal() {
            this.symCodeCreating = false;
            this.newSymCode = null;
            this.newSymbolCodeCheck = false;
        },
        async deleteContractor(id) {
            await contractorAPI.destroy(id).then(() => {
                this.$router.push('/contractors/');
                this.showToast('Удаление поставщика', 'Удаление поставщика произведено успешно!', 'success');
            });
        },
        onChangeContractorName() {
            this.saveDisabled = false;
        },
        newCodeWindow() {
            this.symCodeCreating = true;
        },
        async createNewCode(code) {
            let existCodes = this.isEditing ? this.contractor.symbolic_code_list : this.symbolicCodeListOnCreate;
            let canAdd = true;
            await contractorAPI.checkSymbolicCode(code).then((res) => {
                if (res.data) {
                    this.newSymbolCodeCheck = true;
                } else {
                    existCodes.forEach(e => {
                        if (e == code) {
                            this.newSymbolCodeCheck = true;
                            canAdd = false;
                        }
                    });
                    if (canAdd) {
                        this.isEditing ? this.contractor.symbolic_code_list.push(code) : this.symbolicCodeListOnCreate.push(code);
                        this.symbolCodeCheck.push(0);
                        this.symCodeCreating = false;
                        this.newSymCode = null;
                        this.showToast('Добавление символьного кода', 'Символьный код успешно добавлен!', 'success');
                        this.saveDisabled = false;
                        this.isEditingCodes = true;
                    }
                }
            });
        },
        deleteCode(code) {
            let codes = this.contractor.symbolic_code_list;
            codes.splice(codes.indexOf(code), 1);
            this.symbolCodeCheck.splice(codes.indexOf(code), 1, 0);
            this.contractor.symbolic_code_list = codes;
            this.showToast('Удаление кода', 'Удаление символьного кода произведено успешно!', 'success');
        },
        checkSavePossibility() {
            for (let i = 0; i < this.symbolCodeCheck.length; i++) {
                if (this.symbolCodeCheck[i]) {
                    this.saveDisabled = true;
                    this.showToast('Неверный код', 'Невозможно добавить символьный код!', 'danger');
                    break;
                } else {
                    this.saveDisabled = false;
                }
            }
        },
        cancelEditExistingCode(index) {
            this.contractor.symbolic_code_list[index] = this.initialCodes[index];
            this.symbolCodeCheck.splice(index, 1, 0);
            this.checkSavePossibility();
        },
        async checkSymCode(code, index) {
            await contractorAPI.checkSymbolicCode(code).then((res) => {
                if (code == this.initialCodes[index]) {
                    this.symbolCodeCheck.splice(index, 1, 0);
                } else {
                    if (res.data) {
                        this.symbolCodeCheck.splice(index, 1, 1);
                    } else {
                        this.symbolCodeCheck.splice(index, 1, 0);
                    }
                }
                this.checkSavePossibility();
            });
        },
        editCode(code, index) {
            this.checkSymCode(code, index);
            this.isEditingCodes = true;
        },
    },
    mounted() {
        if (this.isEditing) {
            this.loadContractor();
        } else {
            this.isLoaded = true;
            this.initNewContractor();
        }
    },
    computed: {
        canUserEdit() {
            return this.checkPermission('contractor_update') || (!this.isEditing && this.checkPermission('contractor_create'))
        },
        canUserDelete() {
            return this.checkPermission('contractor_delete') && this.isEditing
        },
        contractorId() {
            return this.$route.params.contractor_id;
        },
        isEditing() {
            return !!this.contractorId;
        }
    },
    watch: {
        newContractorName() {
            if (this.contractor.name !== this.newContractorName) {
                this.checkSavePossibility();
            } else {
                if (!this.isEditingCodes) {
                    this.saveDisabled = true;
                }
            }
        },
    },
}
</script>
