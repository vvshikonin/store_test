<template>
    <div class="input-group mb-3">
        <span class="input-group-text" id="basic-addon3">{{title}}</span>
        <input :value="start" @input="$emit('update:start', $event.target.value)" type="number" class="form-control" placeholder="от">
        <span class="input-group-text" id="basic-addon3">-</span>
        <input :value="end" @input="$emit('update:end', $event.target.value)" type="number" class="form-control" placeholder="до">
    </div>
</template>
<script>
export default{
    props: ['title', 'start', 'end'],
    emits: ['update:start', 'update:end'],
}
</script>