<template>
    <div class="p-2 border rounded text-white text-start " :class="statusBgClass()"
        :style="{ width: fullsize ? '230px' : '48px' }" :title="fullsize ? '' : statusName()">
        <font-awesome-icon :icon="statusIcon()" size="lg" class="pe-2 ps-1" style="width:20px;" />
        <span v-if="fullsize">{{ statusName() }}</span>
    </div>
</template>

<style>
    .invoice-status-expect-bg{
        background-color: rgb(254, 165, 48);
        border-color:  rgb(254, 165, 48)!important;
    }
    .invoice-status-part-bg{
        background-color: rgb(0 94 235);
        border-color:  rgb(0 94 235)!important;
    }
    .invoice-status-complete-bg{
        background-color:#157e5c;
        border-color:  #157e5c!important;
    }
    .invoice-status-cancel-bg{
        background-color:#b73232;
        border-color:  #b73232!important;
    }
</style>

<script>

export default {
    props: {
        statusCode: Number,
        fullsize: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        statusName() {
            switch (this.statusCode) {
                case 'new':
                    return 'Новый счет';
                case 0:
                    return 'Ожидается';
                case 1:
                    return 'Частично оприходован';
                case 2:
                    return 'Оприходован';
                case 3:
                    return 'Отменён';
            }
        },
        statusBgClass() {
            switch (this.statusCode) {
                case 'new':
                    return { 'bg-light text-muted': true, 'border': true };
                case 0:
                    return { 'invoice-status-expect-bg': true};
                case 1:
                    return { 'invoice-status-part-bg': true };
                case 2:
                    return { 'invoice-status-complete-bg': true };
                case 3:
                    return { 'invoice-status-cancel-bg': true };
            }
        },
        statusIcon() {
            switch (this.statusCode) {
                case 'new':
                    return "fa-solid fa-lightbulb";
                case 0:
                    return "fa-solid fa-clock";
                case 1:
                    return "fa-solid fa-dolly";
                case 2:
                    return "fa-solid fa-check";
                case 3:
                    return "fa-solid fa-xmark";
            }
        },
    }
}
</script>
