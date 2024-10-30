<template>
    <div v-if="this.$root.$data.isLoading">
        <div class="position-fixed bg-secondary " style="width: 100vw; height:100vh; left:0; top:0; opacity:0.3; z-index: 100;"></div>
        <div class="position-fixed  d-flex justify-content-center align-items-center" style="width: 100vw; height:100vh; left:0; top:0; z-index: 100">
            <div class="spinner-border text-primary" role="status"></div>
            <span class="fw-bold text-primary ms-3">Если долго грузится ctrl+F5</span>
        </div>
    </div>
</template>