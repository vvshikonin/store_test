<template>
    <div class="d-inline">
        <small>{{ label }}:</small>
        <strong>
            <time class="ps-1"> {{ formatDate(datetime, 'DD.MM.YYYY HH:mm:ss') }}</time>
        </strong>
    </div>
</template>

<script>
export default {
    props: {
        label: {
            type: String,
            default: 'labelProp'
        },
        datetime: {
            type: String,
            default: null,
        }
    }
}
</script>
