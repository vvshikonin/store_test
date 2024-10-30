<template>
    <div class="m-3 d-flex flex-row justify-content-start" style="width: 45%;">
        <label v-if="label" class="text-muted d-flex align-items-center mb-0 ps-1" :class="{ required: required }"
            style="font-size: 13px; width: 30%">{{ label }}</label>
        <div style="width: 70%; max-width: 413px;">
            <div class="input-group">
                <input @change="$emit('change', $event)" type="file" :required="required" class="form-control" :multiple="multiple"
                    ref="inputRef" style="height: 30px; font-size: 13px; " :disabled="disabled" :accept="accept">
                <a @click="loadFile()" :href="fileURL" :class="linkClasses" class="input-group-text" :title="title"
                    style="height: 30px; font-size: 13px;" download>
                    <font-awesome-icon class="pe-1" :icon="linkIcon" />
                </a>
            </div>
        </div>
    </div>
</template>
<style>
label.required:after {
    content: '*';
    color: red;
    font-weight: 800;
    font-size: 13px;
    vertical-align: middle;
    padding-left: 3px;
}
</style>
<script>
export default {
    props: {
        label: String,
        required: Boolean,
        ref: String,
        accept: String,
        fileURL: String,
        multiple: Boolean,
        disabled: {
            type: Boolean,
            default: false
        }
    },
    methods: {
        blur() {
            this.$emit('blur')
        },
        getRef() {
            return this.$refs.inputRef;
        },
        loadFile() {
            if (!this.fileURL)
                this.$refs.inputRef.click();
        }
    },
    emits: ['update:modelValue', 'blur', 'change'],
    computed: {
        linkIcon() {
            if (this.fileURL)
                return 'fa-solid fa-download'
            return 'fa-solid fa-file';
        },
        title() {
            if (this.fileURL)
                return 'Скачать файл';
            return 'Нет файла';
        },
        linkClasses() {
            if (this.fileURL)
                return 'link-primary';
            return 'link-secondary';
        }
    }
}
</script>
