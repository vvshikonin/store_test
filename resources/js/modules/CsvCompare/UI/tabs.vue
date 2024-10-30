<template>
    <ul class="nav nav-tabs mb-2 flex-nowrap" style="overflow-x: auto; overflow-y: hidden;">
        <li class="nav-item">
            <a @click.prevent href="#" class="nav-link active" aria-current="page">Деньги</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled">Контрагенты (в работе)</a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled">Реквизиты (в работе)</a>
        </li>
    </ul>
    <div>
        <slot></slot>
    </div>
</template>

<script>
export default{

}
</script>