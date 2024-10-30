<template>
    <div class="shadow-sm border border-1 bg-white rounded-1 mb-3" :class="{'card-collapse': !isExpand}" style="height: fit-content;">
        <div @click="toggleExpand()" v-if="title" :class="titleBorder ? 'border-bottom' : ''" class="card-header d-flex p-2 flex-row align-items-center">
            <h6 class="text-dark m-3">{{ title }}</h6>
            <span class="ms-auto">
                <slot name="top"></slot>
            </span>
            <div v-if="toggleable" class="ms-auto expander text-muted d-flex justify-content-center align-items-center">
                <font-awesome-icon icon="fa-solid fa-chevron-up" :rotation="!isExpand ? 90 : 0" style="transition: all 0.2s ease 0s;"/>
            </div>
        </div>
        <Transition>
            <div v-if="isExpand" class="d-flex flex-row flex-wrap justify-content-start pb-5">
                <slot name="default">default slot</slot>
            </div>
        </Transition>
    </div>
</template>
<style>
.expander {
    width: 45px;
    height: 45px;
    cursor: pointer;
}

.card-header:hover .expander{
    color: #0d6efd !important;
}

.card-collapse{
    height: fit-content !important;
}
</style>
<script>
export default {
    props: {
        title: {
            type: String,
            default: 'title prop'
        },
        titleBorder: {
            type: Boolean,
            default: true,
        },
        toggleable:{
            type:Boolean,
            default: false
        }

    },
    data() {
        return {
            isExpand: true,
        }
    },
    methods: {
        toggleExpand() {
            if (this.toggleable) 
                this.isExpand = !this.isExpand;
        }
    }
}
</script>
