<template>
    <div class="d-flex justify-content-center mt-5">
        <div class="border rounded p-5 bg-white" style="width: 500px;">
            <div class="text-center p-3 border-bottom">
                <h3>
                    <font-awesome-icon icon="fa-solid fa-dolly" size="lg" class="pe-2 text-primary"/>
                    <span>Prof-TE Склад</span>
                </h3>
            </div>
            <div class="d-felx justify-content-between mt-3">
                <router-link class="link-primary text-decoration-none me-3" s to="/login">Авторизация</router-link>
                <router-link class="link-primary text-decoration-none"  to="/registration">Регистрация</router-link>
            </div>
            <form @submit.prevent="onSingUp" class="d-flex flex-column mt-3" >
                <div>
                    <div class="mb-3 form-control-md">
                    <label for="name" class="form-label">Имя пользователя</label>
                    <input v-model="name" minlength="2" type="text" class="form-control" id="name" required>
                    </div>
                    <div class="mb-3 form-control-md">
                        <label for="email" class="form-label">Email</label>
                        <input v-model="email" minlength="2" type="email" class="form-control" id="email" required>
                    </div>
                    <div class="mb-3 form-control-md">
                        <label for="newPassword" class="form-label">Пароль</label>
                        <input v-model="password" type="password" minlength="4" class="form-control" id="newPassword"
                            required>
                    </div>
                    <div class="mb-3 form-control-md">
                        <label for="passwordConfirm" class="form-label">Подтвердите пароль</label>
                        <input v-model="passwordConfirmation" type="password" minlength="4" class="form-control"
                            id="passwordConfirm" required>
                    </div>
                    <div class="" style="text-align:center; margin-top: 25px; display: block;">
                        <button type="submit" class="btn btn-primary" style=" width: 327px;">Зарегистрироваться</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</template>

<script>
export default {
    data() {
        return {
            register: true,
            registerIsFailed: false,
            registerIsValid: false,
            name: null,
            email: null,
            password: null,
            passwordConfirmation: null,
        }
    },
    methods: {
        onSingUp: function () {
            if (this.password == this.passwordConfirmation) {
                this.registerIsValid = true;
                this.register = false;
                this.registerIsFailed = false;
            } else {
                this.registerIsFailed = true;
                this.register = false;
                this.registerIsValid = false;
            }
            this.$store.dispatch(
                    'onSingUp', 
                    { 
                        name: this.name, email: this.email, password: this.password, passwordConfirmation: this.passwordConfirmation 
                    }
                ).then(() => {
                    this.showToast('OK', 'Запрос на регистрацию отправлен.', 'succsess')
                }
            );
        }
    }
}
</script>