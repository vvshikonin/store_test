<template>
    <tfoot class="border-bottom bg-light border-top" :class="tFootClasses">
        <tr>
            <td colspan="100%" class="text-muted text-end" :class="tdClasses" style="height: 39px; font-size: 13px;">
                <span style="margin: 13px 20px 0 10px;">
                    <slot></slot>
                </span>
            </td>
        </tr>
    </tfoot>
</template>
<script>
export default {
    props: {
        isLoading: {
            default: false,
            type: Boolean,
        },
        isStickyFooter: {
            default: false,
            type: Boolean
        }
    },
    computed: {
        tdClasses() {
            return { 'border-top': this.isLoading }
        },
        tFootClasses() {
            return { 'position-sticky bottom-0': this.isStickyFooter }
        }
    }
}
</script>