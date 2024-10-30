<template>

    <DefaultModal title="Загрузка файлов" width="800px" @close_modal="closeUploadModal">

        <template v-slot:default>
            <div @dragover.prevent @dragenter.prevent="dragEnter" @dragleave.prevent="dragLeave" @drop.prevent="handleDrop"
                @click="openFileInput" class="drag-field m-3 mb-0" :class="{ 'dragging': isDragging }">
                <input type="file" multiple ref="fileInput" class="d-none" @change="handleFiles" />
                Перетащите файлы сюда или кликните для выбора.
            </div>

            <div class="p-3">
                <span style="font-size: 18px">Выбранные файлы:</span>
                <div class="border rounded-2 p-2 w-50" style="background-color: #e7e7e7;">
                    <div v-if="previewFiles.length" class="d-flex flex-row" v-for="file, index in previewFiles" :key="file.name">
                        <span class="text-danger d-flex justify-content-center align-items-center" style="cursor: pointer; width: 24px" @click="removeFile(index)">
                            <font-awesome-icon icon="fa-solid fa-xmark" />
                        </span>
                        <span class="middle-truncate">{{ file.name }}</span>
                    </div>
                    <div v-else>
                        Файлов не добавлено.
                    </div>
                </div>
                <div>
                    <button type="button" :disabled="!previewFiles.length" class="btn btn-success mt-3 me-2" @click="confirmUpload">Загрузить файлы</button>
                    <button type="button" class="btn btn-light border mt-3" @click="closeUploadModal">Отмена</button>
                </div>
            </div>

        </template>

    </DefaultModal>

</template>


<style scoped>

.drag-field {
    border: 2px dashed #aaa;
    padding: 20px;
    text-align: center;
    cursor: pointer;
}
.dragging {
    background-color: #e9e9e9;
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

import DefaultModal from './default_modal.vue';

export default {
    data() {
        return {
            isDragging: false,
            previewFiles: []
        };
    },
    emits: ['upload_files', 'cancel_upload'],
    components: { DefaultModal },
    methods: {
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
        handleFiles(event) {
            const files = event.target.files;
            this.addFilesToPreview(files);
            this.$refs.fileInput.value = null;
        },
        openFileInput() {
            this.$refs.fileInput.click();
        },
        addFilesToPreview(files) {
            for (let i = 0; i < files.length; i++) {
                if (!this.fileExists(files[i])) {
                    this.previewFiles.push(files[i]);
                } else {
                    alert(`Файл ${files[i].name} уже добавлен.`);
                }
            }
        },
        fileExists(file) {
            return this.previewFiles.some(f => f.name === file.name);
        },
        removeFile(index) {
            this.previewFiles.splice(index, 1);
        },
        confirmUpload() {
            this.$emit('upload_files', this.previewFiles);
            this.$emit('cancel_upload');
            this.previewFiles = [];
        },
        closeUploadModal() {
            this.$emit('cancel_upload');
            this.previewFiles = [];
        }
    },

}

</script>
