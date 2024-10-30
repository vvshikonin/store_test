<template>
    <div class="d-flex justify-content-center mt-5" >
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
            <!-- <form @submit.prevent="" class="d-flex flex-column">
                <div class="mb-3 form-control-md" style="margin-top: 30px;">
                    <label for="Email" class="form-label">Email</label>
                    <input  type="Email" class="form-control" id="Email" required>
                </div>
                <div class="" style="margin-top: 15px; text-align: center;">
                    <button type="submit" class="btn btn-primary" style=" width: 327px">Сбросить пароль</button>
                </div>
            </form> -->
            <div class="d-flex justify-content-center p-5">
                Обратитесь к администратору
            </div>
        </div>
    </div>
</template>
