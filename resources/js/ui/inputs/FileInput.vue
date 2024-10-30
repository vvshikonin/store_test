<template>

    <div class="d-flex flex-row">
        <div v-if="modelValue !== null" class="bg-primary me-2 ps-3 border-secondary rounded">
            <span class="text-white me-2"> {{ fileLabel }} </span>
            <a @click="$emit('download')" :href="'storage/' + modelValue" 
                target="_blank" class="btn download-file-button bg-white btn-outline-primary">
                <font-awesome-icon icon="fa fa-download"></font-awesome-icon>
            </a>
        </div>
        <button @click="openFileInput()" type="button" class="btn btn-outline-success"> {{ btnTitle }} </button>
        <input ref="fileInput" type="file" class="d-none" accept=".xlsx,.xls,.csv,.pdf,.jpeg,.jpg,.png,.doc,.docx"
            @change="uploadFile($event)">
    </div>

</template>

<style scoped>
.download-file-button:hover,
.download-file-button:focus,
.download-file-button:active  {
    background-color: #0d6efd!important;
}
</style>

<script>

export default {
    props: {
        fileLabel: String,
        btnTitle: String,
        ref: String,
        modelValue: [String, File, null],
    },
    emits: ['upload', 'download', 'update:modelValue'],
    methods: {
        openFileInput() {
            this.$refs.fileInput.click();
        },
        uploadFile(event) {
            this.$emit('update:modelValue', event.target.files[0]);
            this.$emit('upload');
        },
        getRef() {
            return this.$refs.fileInput;
        }
    }
}

</script>