<template>
    <button @click="$emit('click')" :type="type" class="btn btn-primary">
        <font-awesome-icon :icon="icons.search"></font-awesome-icon>
        {{ text }}
    </button>
</template>

<script>
import faIcons from '../../mixins/fa-icons';
export default {
    mixins:[faIcons],
    props: {
        text: {
            type: String,
            default: 'Поиск'
        },
        type:{
            type: String,
            default: 'button'
        }
    }
}
</script>
