<template>
    <div class="shadow-sm border border-1 bg-white rounded-1 mb-3" style="height: fit-content;">
        <div v-if="title" class="d-flex p-2 flex-row align-items-center border-bottom">
            <h6 class="text-dark m-3">{{ title }}</h6>
        </div>
        <div class="d-flex flex-row flex-wrap justify-content-start pb-5">
            <slot name="default">default slot</slot>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        title: {
            type: String,
            default: 'title prop'
        },
    },
}
</script>
