<template>
    <div class="d-flex justify-content-center mt-5">
        <div class="border rounded-1 bg-white p-5" style="width: 500px;">
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
            <form @submit.prevent="onSubmit" class="d-flex flex-column mt-3">
                <div class="mb-3 form-control-md">
                    <label for="exampleInputEmail1" class="form-label">Email</label>
                    <input v-model="email" type="email" class="form-control" id="exampleInputEmail1"
                        aria-describedby="emailHelp" required>
                </div>
                <div class="mb-3 form-control-md">
                    <label for="exampleInputPassword1" class="form-label">Пароль</label>
                    <input v-model="password" type="password" class="form-control" id="exampleInputPassword1" required>
                    <div class="mt-3">
                        <router-link class="link-primary text-decoration-none" style="font-size: 14px;" to="/forgot-password">Забыли Пароль?</router-link>
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    <button type="submit" class="btn btn-primary" style=" width: 327px">Войти</button>
                </div>

            </form>
        </div>
    </div>
</template>

<script>

export default {

    data() {
        return {
            email: null,
            password: null,
            authIsFailed: false
        }
    },
    methods: {
        onSubmit() {

            this.$store.dispatch('onLogin', {
                email: this.email,
                password: this.password
            })
                .then(() => {
                    document.location.reload();
                }).catch((error) => {
                    this.showToast(error.response.status, error.response.data, 'danger');
                });


        }
    }
}
</script>