<template>
    <page_wrapper>
        <LoadingCover :loadingCover="loadingCover" />
        <form id="entityFormId" @submit.prevent="onSave()" ref="entityform">
            <div v-if="isLoaded">
                <div class="d-flex flex-row align-items-center  pb-3 pt-3 mb-3">
                    <slot name="header"></slot>
                </div> 
                <!-- {{ fullSidebarSize }} -->
                <slot name="content"></slot>
            </div>
            <div v-else style="height: 500px;">
                <div class="d-flex flex-row align-items-center" style="position: relative;">
                    <IsLoading style="position: absolute; margin-top: 80%; margin-left: 50%;"></IsLoading>
                </div>
            </div>
            <div v-if="withSaveButton || withDeleteButton"
                class="save-box bottom-0 position-fixed bg-white border rounded-top d-flex flex-row pt-3 pb-2 shadow-lg"
                style="transition: all .2s;" :style="saveboxMargin">
                <slot name="save-box">
                    <button v-if="withSaveButton" type="submit" ref="entityFormSubmitButton" class="btn btn-primary me-2"
                        style="margin-left:20px" :disabled="SaveDisabled || loadingCover || !isLoaded">Сохранить</button>
                    <button v-if="withSaveButton && withSaveAndExitButton" type="button" class="btn btn-light border me-2"
                        @click.prevent="saveAndExit()" :disabled="SaveDisabled || loadingCover || !isLoaded">Сохранить и
                        выйти</button>
                    <button v-if="withDeleteButton" :disabled="loadingCover || !isLoaded" type="button"
                        class="btn btn-danger ms-auto" style="margin-right:40px" @click="onDeleteClick()">Удалить</button>
                </slot>
            </div>
        </form>
        <DestroyConfirmModal v-if="isShowDeleteConfirmModal" :entityName="entityName"
            @cancel="isShowDeleteConfirmModal = false" @confirm="onDestroy()"></DestroyConfirmModal>
    </page_wrapper>
</template>

<style>
.save-box {
    left: 0;
    right: 0;
    z-index: 510;
}
</style>

<script>
import page_wrapper from './page_wrapper_no_bg.vue';
import IsLoading from '../Tables/is_loading.vue';
import DestroyConfirmModal from '../modals/DestroyConfirmModal.vue';

import { mapGetters } from 'vuex';
import LoadingCover from './LoadingCover.vue'

export default {
    data() {
        return {
            isShowDeleteConfirmModal: false,
            isShowExitConfirmModal: false,
        }
    },
    props: {
        isLoaded: Boolean,
        withSaveButton: Boolean,
        entityName: String,
        SaveDisabled: {
            type: Boolean,
            default: false
        },
        DeleteDisabled: {
            type: Boolean,
            default: false
        },
        withDeleteButton: {
            type: Boolean,
            default: true
        },
        loadingCover: {
            type: Boolean,
            default: false
        },
        withSaveAndExitButton: {
            type: Boolean,
            default: true
        }
    },
    components: { page_wrapper, IsLoading, DestroyConfirmModal, LoadingCover },
    methods: {
        onDeleteClick() {
            this.isShowDeleteConfirmModal = true;
        },
        onDestroy() {
            this.isShowDeleteConfirmModal = false;
            this.$emit('destroy');
        },
        onSave() {
            this.$emit('save');
        },
        saveAndExit() {
            if (this.$refs.entityform.checkValidity()) {
                this.onSave();
                this.exit();
            } else {
                this.$refs.entityform.reportValidity()
            }
        },
        exit() {
            this.$emit('exit');
        },
        onBeforeUnload(event) {
            event.preventDefault()
            event.returnValue = ''
        },
    },
    computed: {
        ...mapGetters({ sidebarMode: 'getSidebarMode' }),
        saveboxMargin() {
            return { 'margin-left': this.sidebarMode ? 'var(--max-sidebar-size)' : 'var(--min-sidebar-size)' };
        }
    }

}
</script>