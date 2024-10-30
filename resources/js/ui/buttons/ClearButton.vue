<template>
    <button @click="$emit('click')" :type="type" class="btn btn-light border text-danger">
        <font-awesome-icon :icon="icons.clear"></font-awesome-icon>
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
            default: 'Очистить'
        },
        type:{
            type: String,
            default: 'button'
        }
    }
}
</script>
