<template>
    <DefaultModal title="Файлы чека/накладной" width="800px" modalStyle="align-self:self-start!important; margin-top:50px;">
        <div class="w-100 p-3">
            <p class="p-3 pb-0 pt-0 text-info">
                <i>
                    <font-awesome-icon class="me-2" :icon="['far', 'circle-question']" />
                    <small>Для подтверждения сохранения или удаления файлов требуется сохранить счёт.</small>
                </i>
            </p>
            <div @dragover.prevent @dragenter.prevent="dragEnter" @dragleave.prevent="dragLeave"
                @drop.prevent="handleDrop" @click="openFileInput" class="drag-field m-3 pt-5 pb-5"
                :class="{ 'dragging': isDragging }">
                <input type="file" multiple ref="fileInput" class="d-none" @change="handleFiles" />
                <font-awesome-icon class="me-2" :icon="['fas', 'file']" />
                Перетащите файлы сюда или нажмите для выбора
            </div>
            <div v-if="invoice.files.new_payment_files.length">
                <small class="ps-3 text-muted">Добавляемые файлы:</small>
                <ul class="text-primary p-3 pt-1 pb-1" style="max-height: 20dvh; overflow-y: auto;">
                    <li v-for="newFile, index in invoice.files.new_payment_files" class="d-flex align-items-center">
                        <small class="text-truncate">{{ newFile.name }}</small>
                        <button @click="removeFile(index)" class="ms-auto btn btn-outline-danger border-0">
                            <font-awesome-icon :icon="['fas', 'xmark']" />
                        </button>
                    </li>
                </ul>
            </div>

            <div v-if="invoice.payment_files.length">
                <small class="ps-3 text-muted">Сохранённые файлы:</small>
                <ul class="text-primary p-3 pt-1 pb-1" style="max-height: 20dvh; overflow-y: auto;">
                    <li v-for="file, index in invoice.payment_files" class="d-flex align-items-center">
                        <a :href="`/storage/${file}`" target="_blank" download>
                            <small class="text-truncate">
                                {{ makeFileName(file) }}
                            </small>
                        </a>
                        <button @click="removeSavedFile(index)" class="ms-auto btn btn-outline-danger border-0">
                            <font-awesome-icon :icon="['fas', 'xmark']" />
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </DefaultModal>
</template>

<style scoped>
.drag-field {
    border: 2px dashed #6c757d;
    color: #6c757d;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    border-radius: 10px;
}


.drag-field.dragging,
.drag-field:hover {
    border: 2px dashed #0d6efd;
    color: #0d6efd;
}

.middle-truncate {
    position: relative;
    max-width: 93%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<script>
import InvoiceMixin from '../../../mixins/InvoiceMixin';
import DefaultModal from '../../../components/modals/default_modal.vue';

export default {
    components: { DefaultModal },
    mixins: [InvoiceMixin],
    data() {
        return {
            isDragging: false,
        }
    },
    methods: {
        openFileInput() {
            this.$refs.fileInput.click();
        },
        handleFiles(event) {
            const files = event.target.files;
            this.addFilesToPreview(files);
            this.$refs.fileInput.value = null;
        },
        addFilesToPreview(files) {
            for (let i = 0; i < files.length; i++) {
                if (!this.fileExists(files[i])) {
                    this.$store.commit('invoiceModule/addNewPaymentFile', files[i])
                }
            }
        },
        fileExists(file) {
            return this.invoice.files.new_payment_files.some(f => f.name === file.name);
        },
        removeFile(index) {
            this.invoice.files.new_payment_files.splice(index, 1);
        },
        dragEnter() {
            this.isDragging = true;
        },
        dragLeave() {
            this.isDragging = false;
        },
        handleDrop(event) {
            this.isDragging = false;
            const files = event.dataTransfer.files;
            this.addFilesToPreview(files);
        },
        makeFileName(file) {
            return file.replace('invoices/', '');
        },
        removeSavedFile(index) {
            const file = this.invoice.payment_files[index];
            this.$store.commit('invoiceModule/addDeletedPaymentFile', file);
            this.invoice.payment_files.splice(index, 1);
        }
    }
}
</script>