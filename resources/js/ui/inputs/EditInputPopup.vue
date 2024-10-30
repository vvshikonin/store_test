<template>
    <div class="popup-container">
        <div @click="togglePopup" class="popup-toggle">
            <slot name="button-content"></slot>
        </div>
        <div v-if="isOpen" class="popup-content d-flex flex-row align-items-center">
            <input class="form-control me-2" v-model="inputValue" 
                placeholder="Введите новое имя" style="font-size: 12px" />
            <font-awesome-icon @click="acceptInput" class="text-success me-2" icon="fa-solid fa-check" size="lg" />
            <font-awesome-icon @click="closePopup" class="text-danger" icon="fa-solid fa-xmark" size="lg" />
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            isOpen: false,
            inputValue: ''
        };
    },
    methods: {
        togglePopup() {
            this.isOpen = !this.isOpen;
        },
        closePopup() {
            this.isOpen = false;
            this.inputValue = '';
        },
        acceptInput() {
            if (this.inputValue) {
                this.$emit('input-changed', this.inputValue);
            }
            this.isOpen = false;
            this.inputValue = '';
        },
        outsideClick(event) {
            if (!this.$el.contains(event.target)) {
                this.closePopup();
            }
        }
    },
    mounted() {
        document.addEventListener('click', this.outsideClick);
    },
    destroyed() {
        document.removeEventListener('click', this.outsideClick);
    }
}
</script>

<style scoped>
.popup-container {
    position: relative;
    display: inline-block;
}

.popup-content {
    position: absolute;
    width: 250px;
    top: 100%;
    right: 100%;
    border: 1px solid #ccc;
    border-radius: 4px;
    background-color: #fff;
    padding: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    z-index: 501;
}
</style>