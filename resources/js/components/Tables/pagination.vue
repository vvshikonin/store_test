<template >
    <div class="ms-auto d-flex flex-row">
        <template v-if="meta.links && meta.last_page > 1">
            <ul v-for="(page, index) in meta.links" class="pagination pe-1 mb-0 d-flex align-items-center">
                <li @click="onChangeCurrentPage(page)" class="page-item" v-if="index != 0 && index != meta.links.length - 1"
                    :class="{ active: page.active, disabled: page.label == '...' }" aria-current="page">
                    <a class="page-link border-0">{{ page.label }}</a>
                </li>
            </ul>
        </template>

        <template v-else-if="!meta.links">
            <ul class="pagination pe-1 mb-0 d-flex align-items-center placeholder-glow">
                <li class="page-item disabled placeholder " aria-current="page">
                    <a class="page-link border-0"> ... </a>
                </li>
            </ul>
            <ul class="pagination pe-1 mb-0 d-flex align-items-center placeholder-glow">
                <li class="page-item active placeholder " aria-current="page">
                    <a class="page-link border-0"> ... </a>
                </li>
            </ul>
            <ul class="pagination pe-1 mb-0 d-flex align-items-center placeholder-glow">
                <li class="page-item disabled placeholder " aria-current="page">
                    <a class="page-link border-0"> ... </a>
                </li>
            </ul>
        </template>
    </div>
    <template v-if="meta.last_page > 1 || !meta.links">
        <div @click="onPreviousPage()" :class="{ disabled: checkPreviousIsDisabled() }"
            class="d-flex justify-content-center align-items-center border-end border-start text-muted pagination-next-btn"
            style="width:52px; height:52px; cursor: pointer;">
            <font-awesome-icon icon="fa-solid fa-chevron-right" rotation="180" class="opacity-75" />
        </div>
        <div @click="onNextPage()" :class="{ disabled: checkNextIsDisabled() }"
            class="d-flex justify-content-center align-items-center text-muted pagination-next-btn"
            style="width:52px; height:52px; cursor: pointer;">
            <font-awesome-icon icon="fa-solid fa-chevron-right" class="opacity-75" />
        </div>
    </template>
</template>

<style>
.pagination li:not(.active) a {
    cursor: pointer;
}

.pagination li.placeholder {
    background-color: unset;
}

.pagination-next-btn:hover {
    color: #0d6efd !important;
    background: #f9fafb;
}

.pagination-next-btn.disabled {
    pointer-events: none;
    color: rgb(229, 231, 233) !important;
}
</style>

<script>
export default {
    props: {
        meta: Object
    },
    emits: ["current_page"],
    methods: {
        onChangeCurrentPage(page) {
            if (page.url) {
                this.$emit('current_page', page.label);
            }
        },
        onPreviousPage() {
            const urlParams = new URLSearchParams(this.meta.links[0].url.replace(this.meta.path, ''));
            this.$emit('current_page', urlParams.get('page'));
        },
        onNextPage() {
            const urlParams = new URLSearchParams(this.meta.links[this.meta.links.length - 1].url.replace(this.meta.path, ''));
            this.$emit('current_page', urlParams.get('page'));
        },
        checkPreviousIsDisabled() {
            if (this.meta) {
                if (this.meta.links) {
                    if (!this.meta.links[0].url) {
                        return true;
                    }
                    return false;
                }
                return true;
            }
        },
        checkNextIsDisabled() {
            if (this.meta) {
                if (this.meta.links) {
                    if (!this.meta.links[this.meta.links.length - 1].url) {
                        return true;
                    }
                    return false;
                }
                return true;
            }
        }

    }
}
</script>