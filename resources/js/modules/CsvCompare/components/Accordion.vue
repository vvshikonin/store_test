<template>
    <div class="container-xxl mt-3">
        <div class="accordion" id="accordionExample">
            <AccordionItem v-for="upload in uploads" :key="upload.upload_index" :upload="upload" :filters="filters"/>
        </div>
    </div>
</template>

<script>
import AccordionItem from './AccordionItem.vue';
export default {
    components: {
        AccordionItem
    },
    props: {
        isLoading: Boolean,
        uploads: Array,
        filters: Object,
    },
}
</script>