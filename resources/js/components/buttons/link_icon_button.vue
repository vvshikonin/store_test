<template>
     <a class="ps-2 text-primary" style="text-decoration: none!important; cursor: pointer;"><font-awesome-icon class="pe-1" :icon="icon"/>{{ text }}</a>
</template>
<script>
export default{
    props:{
        icon: String,
        text: String
    }
}
</script>