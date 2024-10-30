<template>
    <EntityLayout :loadingCover="isCover" :isLoaded="isLoaded" :entityName="'хозяйственный расход ' + expense.id"
        :withSaveButton="true" :withSaveAndExitButton="false" :withDeleteButton="isEditing" @save="onSave()"
        @exit="$router.push('/expenses')" @destroy="onDelete">
        <template v-slot:header>
            <div class="d-flex flex-row border-1 border overflow-hidden bg-gradient shadow-sm text-white rounded w-100"
                :class="expense.is_need_to_complete ? 'bg-warning' : 'bg-primary'">
                <div class="d-flex flex-column w-100">
                    <div class="d-flex flex-row justify-content-between">
                        <div class="d-flex flex-column p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 v-if="isEditing">Хозяйственный расход №<strong>{{ expense.id }}</strong></h3>
                                    <h3 v-else class="ms-3">Новый хозяйственный расход</h3>
                                    <p v-if="isDirty" style="color:red; font-size:16px">*</p>
                                </div>
                            </div>
                            <div>
                                <div v-if="expense.creator?.name" class="d-inline me-1">
                                    <small>Создал:</small>
                                    <strong class="ps-1">{{ expense.creator.name }}</strong>
                                </div>
                                <div v-if="expense.created_at" class="d-inline me-1">
                                    <small>Создан:</small>
                                    <strong class="ps-1">{{ formatDate(expense?.created_at, 'DD.MM.YYYY HH:mm:ss') }}</strong>
                                </div>
                                <div v-if="expense.updater?.name" class="d-inline me-1">
                                    <small>Изменил:</small>
                                    <strong class="ps-1">{{ expense.updater.name }}</strong>
                                </div>
                                <div v-if="expense?.updated_at" class="d-inline me-1">
                                    <small>Изменён:</small>
                                    <strong class="ps-1">{{ formatDate(expense?.updated_at, 'DD.MM.YYYY HH:mm:ss') }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-row align-items-center me-2" v-if="expense.converted_to_money_refunds_id">
                            <div class="me-2 text-white p-1 rounded">
                                Расход был преобразован в Возврат ДС.
                            </div>
                            <button type='button' @click="goToMoneyRefund()" class="btn btn-success">
                                Перейти к Возврату ДС
                            </button>
                        </div>
                    </div>

                    <div class="bg-gradient p-3 pt-2 pb-2 border-top border-primary" style="font-size: 14px;">
                        <span class="pe-2" v-if="moneyRefundConvertAvailability">
                            <font-awesome-icon icon="fa-solid fa-arrow-rotate-right" />
                            <a v-if="isEditing" @click="handleExpenseConvertToRefund()"
                                class="text-white text-decoration-underline" style="cursor: pointer;">
                                Перевести в Возврат ДС
                            </a>
                        </span>
                        <div class="border-start border-2 d-inline p-1" v-if="moneyRefundConvertAvailability"></div>
                        <span class="pe-2">
                            <font-awesome-icon icon="fa-regular fa-copy" />
                            <a v-if="isEditing" @click="handleExpenseCopy()"
                                class="text-white text-decoration-underline" style="cursor: pointer;">
                                Копировать Хоз.расход
                            </a>
                        </span>
                    </div>
                </div>
            </div>

        </template>
        <template v-slot:content>
            <div class="d-flex flex-row justify-content-between">
                <Card title="Основные сведения" class="w-75 me-3">
                    <div class="d-flex align-items-center w-50 contragents-wrapper">
                        <button type="button" @click="isEditingContragents = true"
                            class="btn btn-sm btn-light border border-secondary bg-opacity ms-3">
                            <font-awesome-icon icon="fa-solid fa-gear" class="text-success" />
                        </button>
                        <SearchSelector label="Контрагент" required :options="expenseContragents"
                            v-model="expense.contragent_id" :placeholder="'Укажите контрагента'" />
                    </div>
                    <inputDefault label="Комментарий" placeholder="Введите комментарий" v-model="expense.comment"
                        :required="false" />
                    <div class="d-flex align-items-center w-50 expense-types-wrapper">
                        <button type="button" @click="isEditingExpenseTypes = true"
                            class="btn btn-sm btn-light border border-secondary bg-opacity ms-3">
                            <font-awesome-icon icon="fa-solid fa-gear" class="text-success" />
                        </button>
                        <Selector label="Тип расхода" required :disabled="isExpenseTypeDisabled"
                            :options="filteredExpenseTypes" v-model="commonExpenseTypeId"
                            :placeholder="'Выберите тип расхода'" />
                    </div>
                    <Checkbox v-model="expense.is_edo" title="Чек в ЭДО" />
                    <!-- Только для отладки -->
                    <!-- <pre>{{ debugInfo }}</pre> -->
                </Card>
                <div class="w-25 d-flex flex-column" style="gap: 0;">
                    <Card title="Файл счёта" class="mb-2" style="height: 33%;">
                        <!-- <pre>isFileMandatory: {{ isFileMandatory }}</pre> -->
                        <div class="p-1" v-if="commonExpenseTypeId || expense.is_need_to_complete">
                            <div v-if="attachedFileName" class="d-flex align-items-center justify-content-between">
                                <!-- Иконка и название файла -->
                                <a :href="fileUrl(expense.files.path)" :download="expense.files.name"
                                    class="btn btn-light text-start"
                                    style="text-decoration: none; flex-grow: 1; font-size: 12px;"> {{ expense.files.name
                                    }} </a>
                                <!-- Кнопка удаления файла -->
                                <TrashButton :disabled="false" @click="deleteFile()" class="ms-2 me-3"
                                    style="position: absolute; right: 10px;" />
                            </div>
                            <div v-else class="input-group">
                                <input type="file" class="form-control" @change="onFileSelected"
                                    :class="{ 'is-invalid': fileInvalid }" id="inputFile" style="font-size: 12px;">
                            </div>
                        </div>
                        <div class="p-1" v-else>
                            <p class="text-muted" style="font-size: 12px;">Чтобы прикрепить файл счёта, необходимо
                                выбрать контрагента.</p>
                        </div>
                    </Card>
                    <Card title="Файл Чека/ТТН" style="height: 33%;">
                        <div class="p-1" v-if="commonExpenseTypeId && !expense.is_edo">
                            <div v-if="attachedInvoiceFileName"
                                class="d-flex align-items-center justify-content-between">
                                <!-- Иконка и название файла -->
                                <a :href="fileUrl(expense.invoice_file.path)" :download="expense.invoice_file.name"
                                    class="btn btn-light text-start"
                                    style="text-decoration: none; flex-grow: 1; font-size: 12px;"> {{
                                        expense.invoice_file.name }} </a>
                                <!-- Кнопка удаления файла -->
                                <TrashButton :disabled="false" @click="deleteInvoiceFile()" class="ms-2 me-3"
                                    style="position: absolute; right: 10px;" />
                            </div>
                            <div v-else class="input-group">
                                <input type="file" class="form-control" @change="onInvoiceFileSelected"
                                    :class="{ 'is-invalid': fileInvalid }" id="inputInvoiceFile"
                                    style="font-size: 12px;">
                            </div>
                        </div>
                        <div class="p-1" v-else>
                            <p class="text-muted" style="font-size: 12px;">Чтобы прикрепить файл чека/ТТН, необходимо
                                выбрать контрагента или снять галочку "Чек в ЭДО".</p>
                        </div>
                    </Card>
                </div>
            </div>
            <Card title="Сведения об оплате" class="mt-0">
                <Selector label="Юридическое лицо" required :options="legalEntities" v-model="expense.legal_entity_id"
                    :placeholder="'Юридическое лицо не выбрано'" />
                <Selector label="Способ оплаты" required :disabled="!expense.legal_entity_id"
                    :options="payment_types_options" v-model="expense.payment_method_id"
                    :placeholder="'Способ оплаты не выбран'" />
                <Selector label="Статус оплаты" required :disabled="!expense.payment_method_id"
                    :options="paymentStatusOptions" v-model="expense.is_paid" :placeholder="'Укажите статус оплаты'" />
                <inputDefault label="Дата оплаты" type="date" v-model="expense.payment_date" />
                <Selector label="Месяц периода оплаты" required :options="monthsList" v-model="expense.accounting_month"
                    :placeholder="'Укажите, за какой месяц производится оплата'" />
                <Selector label="Год периода оплаты" required :options="yearsList" v-model="expense.accounting_year"
                    :placeholder="'Укажите, за какой год производится оплата'" />
            </Card>
            <Card title="Список расходов">
                <div class="p-3 d-flex w-100">
                    <div class="ms-auto">
                        <AddButton @click="addExpenseItem()" text="Добавить расход" />
                    </div>
                </div>
                <Table class="w-100">
                    <template v-slot:thead>
                        <TH>Наименование позиции</TH>
                        <TH>Кол-во</TH>
                        <TH>Цена</TH>
                        <TH></TH>
                    </template>
                    <template v-slot:tbody>
                        <TR v-for="(item, index) in expense.items" :key="item">
                            <TD>
                                <input v-model="item.name" type="text" class="form-control table-input w-100">
                            </TD>
                            <TD>
                                <input v-model.number="item.amount" min="0" type="number"
                                    class="form-control table-input short" step="1" required>
                            </TD>
                            <TD>
                                <input v-model.number="item.price" type="number" class="form-control table-input"
                                    step="0.01" min="0" required>
                            </TD>
                            <TD>
                                <TrashButton :disabled="isTrashButtonDisabled" @click="removeExpenseItem(index)" />
                            </TD>
                        </TR>
                    </template>
                    <template v-slot:tfoot>
                        <div class="d-inline">
                            <span class="pe-3"> Сумма по позициям: {{ itemsPriceSum.priceFormat(true) }} </span>
                        </div>
                    </template>
                </Table>
            </Card>
            <ExpenseContragentsModal v-model="isEditingContragents" @on_update="loadExpenseContragents()"></ExpenseContragentsModal>
            <ExpenseTypesModal v-model="isEditingExpenseTypes" @on_update="loadExpenseTypes()"></ExpenseTypesModal>
        </template>
    </EntityLayout>
</template>
<style scoped>
.contragents-wrapper > div,
.expense-types-wrapper > div
{
    width: 100%!important;
}

.table-input {
    font-size: 13px;
}

.table-input.short {
    width: 70px;
}

.table-input:not(.short):not(.form-check-input) {
    width: 135px;
}
</style>
<script>
import { expenseAPI } from '../api/expense_api.js';
import { expenseTypeAPI } from '../api/expense_type_api.js';
import { expenseContragentAPI } from '../api/expense_contragent_api.js';
import { mapGetters } from 'vuex';
import IndexTableMixin from '../utils/indexTableMixin.js';
import EntityLayout from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';
import inputDefault from '../components/inputs/default_input.vue';
import Selector from '../components/inputs/select_input.vue';
import SearchSelector from '../components/inputs/select_input_search.vue';
import DefaultModal from '../components/modals/default_modal.vue';
import DefectMultipleFilesModal from '../components/modals/DefectMultipleFilesModal.vue';
import ExpenseContragentsModal from '../components/modals/ExpenseContragentsModal.vue';
import ExpenseTypesModal from '../components/modals/ExpenseTypesModal.vue';
import AddButton from '../modules/Invoices/ProductsTableModule/ui/AddButton.vue';
import TrashButton from '../ui/buttons/TrashButton.vue';
import Checkbox from '../ui/checkboxes/DefaultCheckbox.vue'

export default {
    components: {
        EntityLayout, Card, Selector, SearchSelector,
        IndexTableMixin, DefaultModal, DefectMultipleFilesModal,
        inputDefault, AddButton, TrashButton,
        ExpenseContragentsModal, ExpenseTypesModal, Checkbox
    },
    mixins: [IndexTableMixin],

    data() {
        return {
            expense: {
                items: [],
                payment_date: null,
                // accounting_month: null,
                // accounting_year: null,
            },
            isEditingExpenseTypes: false,
            isEditingContragents: false,
            expenseTypes: [],
            expenseContragents: [],
            isLoaded: false,
            isCover: false,
            isModalCover: false,
            paymentStatusOptions: [
                { id: 0, name: 'Не платить (оплата не требуется)' },
                { id: 1, name: 'Оплачен' },
                { id: 2, name: 'Требует оплаты' },
            ],
            showAddExpanseModal: false,
            commonExpenseTypeId: null,
            commonExpenseContragentId: null,
            monthsList: [
                { id: 1, name: 'Январь' },
                { id: 2, name: 'Февраль' },
                { id: 3, name: 'Март' },
                { id: 4, name: 'Апрель' },
                { id: 5, name: 'Май' },
                { id: 6, name: 'Июнь' },
                { id: 7, name: 'Июль' },
                { id: 8, name: 'Август' },
                { id: 9, name: 'Сентябрь' },
                { id: 10, name: 'Октябрь' },
                { id: 11, name: 'Ноябрь' },
                { id: 12, name: 'Декабрь' }
            ],
            yearsList: this.generateYearsList(),

            // Для хранения информации о файле счёта
            attachedFile: null,
            attachedFileData: null,
            attachedFileName: '', // Имя файла для отображения в интерфейсе

            // Для хранения информации о файле чека/ТТН
            attachedInvoiceFile: null,
            attachedInvoiceFileData: null,
            attachedInvoiceFileName: '', // Имя файла для отображения в интерфейсе
        }
    },
    methods: {
        async show() {
            const id = this.$route.params.expense_id;
            const res = await expenseAPI.show(id);
            if (res) {
                this.expense = res.data;
                this.expense.legal_entity_id = this.expense.is_need_to_complete ? null : this.expense.legal_entity_id;
                this.expense.payment_method_id = this.expense.is_need_to_complete ? null : this.expense.payment_method_id;
                this.expense.is_paid = this.expense.is_need_to_complete ? null : this.expense.is_paid;
                // Проверяем, есть ли файл и преобразуем его из JSON, если нужно
                if (this.expense.files) {
                    try {
                        this.expense.files = JSON.parse(this.expense.files);
                        // Если структура файла, как вы описали ранее
                        this.attachedFileName = Object.values(this.expense.files)[0];
                    } catch (e) {
                        console.error("Ошибка при разборе файла", e);
                    }
                }
                // Проверяем, есть ли файл чека/ТТН и преобразуем его из JSON, если нужно
                if (this.expense.invoice_file) {
                    try {
                        this.expense.invoice_file = JSON.parse(this.expense.invoice_file);
                        // Если структура файла, как вы описали ранее
                        this.attachedInvoiceFileName = Object.values(this.expense.invoice_file)[0];
                    } catch (e) {
                        console.error("Ошибка при разборе файла чека/ТТН", e);
                    }
                }
            }
            this.isLoaded = true;
        },
        initSettings() {
            this.settings.tableTitle = 'Позиции хозяйственного расхода';
            this.settings.localStorageKey = 'expense_items_params';

            this.settings.withCreateButton = false;
            this.settings.withHeader = false;
            this.settings.withExport = false;
            this.settings.isLoading = false;
            this.settings.saveParams = false;
            this.settings.withBottomBox = false;
            this.settings.withFooter = true;
            this.settings.withPagination = false;
            this.settings.withFilters = false;
            this.settings.withTitle = false;

            this.onInitData = res => {
                this.expense.items = res.data.data;
            }
        },
        onFileSelected(event) {
            console.log("onFileSelected вызван");
            console.log("event.target.id:", event.target.id);
            console.log("event.target.files:", event.target.files);

            this.attachedFile = event.target.files[0];
            this.attachedFileName = this.attachedFile.name;
            // Обновление лейбла инпута
            const inputFileLabel = document.querySelector('.custom-file-label');
            if (inputFileLabel) inputFileLabel.textContent = this.attachedFileName;
        },
        onInvoiceFileSelected(event) {
            console.log("onInvoiceFileSelected вызван");
            console.log("event.target.id:", event.target.id);
            console.log("event.target.files:", event.target.files);

            // Проверяем, что событие произошло на нужном инпуте
            if (event.target.id === 'inputInvoiceFile') {
                // Получаем выбранный файл из input
                this.attachedInvoiceFile = event.target.files[0];
                console.log("attachedInvoiceFile:", this.attachedInvoiceFile);

                // Сохраняем имя файла для отображения в интерфейсе
                this.attachedInvoiceFileName = this.attachedInvoiceFile.name;
                console.log("attachedInvoiceFileName:", this.attachedInvoiceFileName);

                // Обновление лейбла инпута
                const inputFileLabel = document.querySelector('.custom-file-label');
                if (inputFileLabel) inputFileLabel.textContent = this.attachedInvoiceFileName;
            }
        },
        async uploadFile() {
            if (this.expense && this.expense.id) {
                // alert('uploadFile');
                this.isCover = true;
                const formData = new FormData();
                formData.append('file', this.attachedFile);

                try {
                    const res = await expenseAPI.uploadFile(formData, this.expense.id);
                    this.showToast("OK", "Файл загружен", "success");

                    // Обновите данные расхода после загрузки файла
                    this.expense.files = res.data.data.files;
                    this.attachedFile = null;
                    this.attachedFileName = '';

                    // Обновляем данные расхода на фронтенде
                    if (!this.attachedInvoiceFile) {
                        await this.show();
                    }
                } catch (error) {
                    console.error("Ошибка загрузки файла счёта", error);
                    this.showToast("Ошибка загрузки файла счёта", error, "danger");
                } finally {
                    this.isCover = false;
                }
            }
        },
        async uploadInvoiceFile() {
            if (this.expense && this.expense.id) {
                // alert('uploadInvoiceFile');
                this.isCover = true;
                const formData = new FormData();
                formData.append('invoice_file', this.attachedInvoiceFile);
                try {
                    const res = await expenseAPI.uploadInvoiceFile(formData, this.expense.id);
                    this.showToast("OK", "Файл загружен", "success");

                    // Обновите данные расхода после загрузки файла
                    this.expense.invoice_file = res.data.data.invoice_file;
                    this.attachedInvoiceFile = null;
                    this.attachedInvoiceFileName = '';

                    // Обновляем данные расхода на фронтенде
                    if (!this.attachedFile) {
                        await this.show();
                    }
                } catch (error) {
                    console.error("Ошибка загрузки файла чека/ТТН", error);
                    this.showToast("Ошибка загрузки файла чека/ТТН", error, "danger");
                } finally {
                    this.isCover = false;
                }
            }
        },
        async deleteFile() {
            console.log("Попытка удаления файла");
            if (!this.expense.files || !this.expense.files.path) {
                console.error("Файл для удаления не найден");
                return;
            }

            console.log("Информация о файле до запроса на удаление:", JSON.stringify(this.expense.files));
            this.isModalCover = true;
            await expenseAPI.deleteFile(this.expense.id).then(() => {
                console.log("Файл успешно удален с сервера");

                // Логируем данные до изменения, чтобы увидеть текущее состояние
                console.log("Текущее состояние expense.files перед обнулением:", JSON.stringify(this.expense.files));

                this.expense.files = null;
                this.attachedFileData = null;
                this.attachedFileName = '';

                console.log("Состояние expense.files после обнуления:", JSON.stringify(this.expense.files));
                this.showToast("ОК", "Файл удален", "success");
            }).catch(error => {
                console.error("Ошибка удаления файла", error);
            }).finally(() => {
                this.isModalCover = false;
            });

            console.log("Состояние expense.files после завершения удаления:", JSON.stringify(this.expense.files));
        },
        async deleteInvoiceFile() {
            console.log("Попытка удаления файла");
            if (!this.expense.invoice_file || !this.expense.invoice_file.path) {
                console.error("Файл для удаления не найден");
                return;
            }

            console.log("Информация о файле до запроса на удаление:", JSON.stringify(this.expense.invoice_file));
            this.isModalCover = true;
            await expenseAPI.deleteInvoiceFile(this.expense.id).then(() => {
                console.log("Файл успешно удален с сервера");

                // Логируем данные до изменения, чтобы увидеть текущее состояние
                console.log("Текущее состояние expense.invoice_file перед обнулением:", JSON.stringify(this.expense.invoice_file));

                this.expense.invoice_file = null;
                this.attachedInvoiceFileData = null;
                this.attachedInvoiceFileName = '';

                console.log("Состояние expense.invoice_file после обнуления:", JSON.stringify(this.expense.invoice_file));
                this.showToast("ОК", "Файл удален", "success");
            }).catch(error => {
                console.error("Ошибка удаления файла", error);
            }).finally(() => {
                this.isModalCover = false;
            });

            console.log("Состояние expense.invoice_file после завершения удаления:", JSON.stringify(this.expense.invoice_file));
        },
        async onSave() {
            console.log("Попытка сохранения expense");
            if (this.isFileMandatory && (!this.attachedFileName)) {
                this.showToast("Ошибка", "Прикрепление файла обязательно для выбранного контрагента и типа расхода.", "warning");
                return;
            }
            this.isCover = true; // Показываем индикатор загрузки
            try {
                let response;
                if (this.isEditing) {
                    // Обновляем существующий расход
                    this.expense.is_need_to_complete = false;
                    response = await expenseAPI.update(this.expense);
                    this.showToast("ОК", "Хозяйственный расход обновлён", "success");
                    // Загружаем файл, если он прикреплен
                    if (this.attachedFile) {
                        await this.uploadFile();
                    }
                    // alert('attachedInvoiceFile: ' + this.attachedInvoiceFile);
                    if (this.attachedInvoiceFile) {
                        await this.uploadInvoiceFile();
                    }
                } else {
                    this.expense.is_need_to_complete = false;
                    // Создаем новый расход
                    response = await expenseAPI.store(this.expense);
                    // Обновляем ID для нового расхода
                    this.expense.id = response.data.id;
                    this.showToast("ОК", "Хозяйственный расход создан", "success");
                    if (this.attachedFile) {
                        await this.uploadFile();
                    }
                    // alert('attachedInvoiceFile: ' + this.attachedInvoiceFile);
                    if (this.attachedInvoiceFile) {
                        await this.uploadInvoiceFile();
                    }
                    window.location.href = '#/expenses/' + response.data.id + '/edit';
                    window.location.reload();
                }
            } catch (error) {
                console.error("Хоз. расход не сохранён", error);
                // this.showToast("Хоз. расход не сохранён", error.response.data.message, "danger");
            } finally {
                this.isCover = false;
            }
        },
        async onDelete() {
            this.isCover = true;
            if (this.isEditing) {
                try {
                    const response = await expenseAPI.destroy(this.expense);
                    if (response.data) {
                        this.showToast('Расход удалён', 'Расход ' + this.expense.id + ' успешно удалён', 'success');
                        this.$router.push('/expenses');
                    } else {
                        this.showToast('Не удалось удалить расход', 'Расход ' + this.expense.id + ' не был удалён. ', 'warning');
                    }
                } catch (error) {
                    this.showToast('Ошибка при удалении расхода', 'Произошла ошибка при удалении расхода', 'warning');
                }
            }
        },
        async loadExpenseContragents() {
            try {
                const res = await expenseContragentAPI.index({ only_active: 'true' });
                this.expenseContragents = res.data;
            } catch (error) {
                console.error("Ошибка загрузки контрагентов:", error);
            }
        },
        updatePaymentPeriod() {
            const selectedContragent = this.expenseContragents.find(c => c.id === Number(this.expense.contragent_id));
            if (selectedContragent && selectedContragent.is_period_coincides && this.expense.payment_date) {
                const paymentDate = new Date(this.expense.payment_date);
                const month = paymentDate.getMonth() + 1; // +1 потому что getMonth() возвращает месяц от 0 до 11
                const year = paymentDate.getFullYear();

                // Устанавливаем числовые значения для месяца и года напрямую
                this.expense.accounting_month = month; // ожидаемый tinyint
                this.expense.accounting_year = year; // ожидаемый year
            }
        },
        addExpenseItem() {
            // Создаем новый объект item с начальными значениями
            let newItem = {
                expense_type_id: '',
                custom_name: '',
                amount: 1,
                price: 0.00,
            };

            // Проверяем, существует ли уже массив items в объекте expense, если нет - создаем его
            if (!this.expense.items) {
                this.expense.items = [];
            }

            // Добавляем новый item в массив items
            this.expense.items.push(newItem);
        },
        removeExpenseItem(indexToRemove) {
            this.expense.items.splice(indexToRemove, 1);
        },
        async loadExpenseTypes() {
            const res = await expenseTypeAPI.index();
            this.expenseTypes = res.data.data;
        },
        setCommonExpenseType(value) {
            if (value !== 'mixed') {
                this.expense.items.forEach(item => item.expense_type_id = value);
            }
        },
        generateYearsList() {
            const currentYear = new Date().getFullYear();
            return [
                { id: currentYear, name: currentYear.toString() },
                { id: currentYear - 1, name: (currentYear - 1).toString() }
            ];
        },
        fileUrl(filePath) {
            // Убедитесь, что вы используете правильный URL для доступа к файлам
            return `/storage/${filePath}`;
        },
        async handleExpenseCopy() {
            const copiedExpense = this.expense;
            copiedExpense.is_paid = 0;
            copiedExpense.payment_date = null;
            copiedExpense.accounting_month = null;
            copiedExpense.accounting_year = null;
            copiedExpense.files = null;
            copiedExpense.invoice_file = null;
            try {
                const response = await expenseAPI.store(copiedExpense);
                this.showToast('OK', 'Хоз.расход успешно скопирован', 'success');
                window.location.href = '#/expenses/' + response.data.id + '/edit';
                window.location.reload();
            } catch(error) {
                console.error(error);
            }
        },
        async handleExpenseConvertToRefund() {
            let convertableExpense = this.expense;
            // convertableExpense.contragent_id = this.expense.contragent_id;

            console.log(convertableExpense);
            try {
                const response = await expenseAPI.convertToMoneyRefund(convertableExpense);
                this.showToast('OK', 'Хоз.расход успешно преобразован в возврат ДС', 'success');
                // window.location.href = '#/money_refundables/' + response.data.id + '/edit';
            } catch(error) {
                console.error(error);
            }
        },
        goToMoneyRefund() {
            window.location.href = '#/money-refunds/' + this.expense.converted_to_money_refunds_id + '/edit';
        }
    },
    computed: {
        ...mapGetters({ legalEntities: 'getLegalEntities', paymentMethods: 'getPaymentMethods' }),

        expenseId() {
            return this.$route.params.expense_id;
        },
        isEditing() {
            return !!this.expenseId;
        },
        payment_types_options() {
            if (this.expense.legal_entity_id !== null) {
                return this.paymentMethods.filter((element) => {
                    return element.legal_entity_id == this.expense.legal_entity_id;
                });
            } else {
                return [];
            }
        },
        expenseTypeOptionsWithMixed() {
            // Список типов расходов с добавлением опции "Смешанные"
            const mixedOption = { id: 'mixed', name: 'Смешанные' };
            // Проверяем, что expenseTypes является массивом перед распространением
            if (Array.isArray(this.expenseTypes)) {
                return [mixedOption, ...this.expenseTypes];
            }
            return [mixedOption]; // Возвращаем только опцию "Смешанные", если expenseTypes ещё не загружены
        },
        commonExpenseTypeId: {
            get() {
                // Используем Set для определения уникальных типов расходов в items
                const types = new Set(this.expense.items.map(item => item.expense_type_id));
                // Если все items имеют одинаковый тип расхода, возвращаем этот тип
                if (types.size === 1) {
                    return types.values().next().value;
                }
                // Если типы различаются или нет items, возвращаем 'mixed' или null
                return types.size > 1 ? 'mixed' : null;
            },
            set(value) {
                // Установка типа расхода для всех items, если выбранное значение не 'mixed'
                if (value !== 'mixed') {
                    this.expense.items.forEach(item => item.expense_type_id = value);
                }
            }
        },
        isTrashButtonDisabled() {
            return this.expense.items.length < 2;
        },
        itemsPriceSum() {
            // Проверяем, есть ли элементы в массиве
            if (!this.expense.items || this.expense.items.length === 0) {
                return 0;
            }

            // Вычисляем общую сумму
            return this.expense.items.reduce((total, item) => {
                return total + (item.amount * item.price);
            }, 0);
        },
        isExpenseTypeDisabled() {
            // Поле неактивно, если контрагент не выбран или нет подходящих типов расходов
            return !this.expense.contragent_id || this.filteredExpenseTypes.length === 0;
        },
        filteredExpenseTypes() {
            // Проверяем, что контрагент был выбран и список всех типов расходов загружен
            if (!this.expense.contragent_id || !this.expenseTypes.length) {
                this.setCommonExpenseType(null);
                return [];
            }

            // Находим выбранного контрагента в списке всех контрагентов по ID
            const selectedContragent = this.expenseContragents.find(c => c.id === Number(this.expense.contragent_id));

            // Если выбранный контрагент не найден или у него нет связанных типов расходов
            if (!selectedContragent || !selectedContragent.related_expense_types) {
                // this.showToast("Внимание", "К данному контрагенту не привязаны типы расходов. Передайте информацию уполномоченным лицам", "warning");
                this.setCommonExpenseType(null);
                return [];
            }

            // Фильтруем список всех типов расходов
            const filteredTypes = this.expenseTypes.filter(type =>
                selectedContragent.related_expense_types.includes(String(type.id))
            );

            // Автоматический выбор типа расхода, если он единственный и нет установленных типов расходов в элементах
            if (filteredTypes.length === 1 && this.expense.items.every(item => !item.expense_type_id)) {
                this.$nextTick(() => {
                    this.setCommonExpenseType(filteredTypes[0].id);
                });
            }

            return filteredTypes;
        },
        isFileMandatory() {
            const selectedContragent = this.expenseContragents.find(c => c.id === Number(this.expense.contragent_id));

            // Проверяем, выбран ли контрагент, и в зависимости от этого устанавливаем isMandatory
            const isMandatory = selectedContragent ? !selectedContragent.is_receipt_optional : false;

            return isMandatory;
        },
        debugInfo() {
            return JSON.stringify({
                contragent_id: this.expense.contragent_id,
                payment_date: this.expense.payment_date,
                accounting_month: this.expense.accounting_month,
                accounting_year: this.expense.accounting_year,
                is_period_coincides: this.isPeriodCoincides,
                selectedContragent: this.selectedContragent
            }, null, 2);
        },
        isPeriodCoincides() {
            const selectedContragent = this.expenseContragents.find(c => c.id === Number(this.expense.contragent_id));
            return selectedContragent ? selectedContragent.is_period_coincides : false;
        },
        // Добавим это свойство для удобства отслеживания выбранного контрагента в debugInfo
        selectedContragent() {
            return this.expenseContragents.find(c => c.id === Number(this.expense.contragent_id)) || {};
        },
        moneyRefundConvertAvailability() {
            return !this.expense.converted_to_money_refunds_id 
                && (this.expense.payment_method_id !== 0 && this.expense.payment_method_id !== 1)
                && !this.expense.is_converted;
        },
    },
    watch: {
        'expense.payment_date': function (newDate, oldDate) {
            // Проверяем, что дата изменилась и это не первоначальная установка значения
            if (newDate && newDate !== oldDate) {
                this.updatePaymentPeriod();
            }
        },
        'expense.contragent_id': function (newVal, oldVal) {
            // При изменении контрагента обновляем период, если это необходимо
            if (newVal !== oldVal) {
                this.updatePaymentPeriod();
            }
        },
        commonExpenseTypeId(newValue, oldValue) {
            console.log("New Expense Type ID:", newValue, "Old Expense Type ID:", oldValue);
        },
    },
    async mounted() {
        await this.loadExpenseTypes();
        await this.loadExpenseContragents();
        await this.$store.dispatch('loadLegalEntities');
        await this.$store.dispatch('loadPaymentMethods');

        if (this.isEditing) {
            this.show();
        } else {
            this.addExpenseItem();
            this.isLoaded = true;
        }
    },
}
</script>
