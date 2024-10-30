<template>
    <EntityEditPage :isLoaded="isLoaded" :withSaveButton="true" @save="update()" @exit="$router.push('/users')"
        :loadingCover="isCover" :withDeleteButton="false">
        <template v-slot:header>
            <h3>Пользователь {{ originalUser.name }}</h3>
        </template>
        <template v-slot:content>
            <div class="d-flex flex-row">
                <Card title="Основные данные о пользователе" class="w-75"  style="height: unset">
                    <inputDefault autocomplete="name" v-model="currentUser.name" label="Имя" :required="true" placeholder="Имя пользователя">
                    </inputDefault>
                    <inputDefault autocomplete="username" v-model="currentUser.email" label="Email" :required="true"
                        placeholder="Email пользователя">
                    </inputDefault>
                    <SelectInput v-model="currentUser.is_active" label="Активирован" :required="true"
                        :options="[{ id: 0, name: 'Нет' }, { id: 1, name: 'Да' }]"></SelectInput>
                </Card>
                <Card title="Группы пользователя" class="ms-3 w-25 "  style="height: unset">
                    <div v-if="!roles.length" class="d-flex justify-content-center align-items-center w-100 p-5 m-5">
                        <div class="spinner-border text-primary " role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div v-else class="bg-light p-3 border rounded m-3 mb-0" style="max-height: 300px; overflow-y: auto;">
                        <div v-for="role, index in roles" class="d-flex align-center">
                            <input @change="toggleRoles(role)" v-model="currentUser.roles" class="form-check-input me-1"
                                :id="'role_' + index" type="checkbox" :value="role.name" style="font-size: 14px;">
                            <label :for="'role_' + index" class="ps-1" style="font-size: 14px;">{{ role.name }}</label>
                        </div>
                    </div>
                </Card>

            </div>
            <Card title="Смена пароля" class="w-25" style="height: unset">

                    <inputDefault v-model="password" type="new-password" label="Пароль" placeholder="Новый пароль"
                        class="w-100">
                    </inputDefault>
                    <inputDefault v-model="passwordConfirm" type="confirm-new-password" label="Подтверждение пароля"
                        placeholder="Подтверждение нового пароля" class="w-100"></inputDefault>
                </Card>

        </template>
    </EntityEditPage>
</template>

<script>
import { userAPI } from '../api/user_api'
import { rolesAPI } from '../api/roles_api'

import EntityEditPage from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';

import inputDefault from '../components/inputs/default_input.vue';
import SelectInput from '../components/inputs/select_input.vue';
export default {
    components: { EntityEditPage, Card, inputDefault, SelectInput },
    data() {
        return {
            originalUser: {},
            currentUser: {},
            roles: [],
            new_roles_ids: [],
            deleted_roles_ids: [],
            password: null,
            passwordConfirm: null,
            isLoaded: false,
            isCover: false,
        }
    },
    mounted() {
        this.show();
        this.indexRoles();
    },
    methods: {
        async show() {
            const id = this.$route.params.user_id;
            const res = await userAPI.show(id);
            this.initUserData(res);
            this.isLoaded = true;
        },
        async update() {
            this.isCover = true;
            const id = this.originalUser.id;
            const request = this.makeRequest()
            const res = await userAPI.update(id, request);
            this.initUserData(res);
            this.isCover = false;
            this.showToast('Обновление данных', 'Данные пользователя обновлены', 'success')
        },
        async indexRoles() {
            this.roles = (await rolesAPI.index()).data.data
        },

        initUserData(res) {
            this.originalUser = res.data.data;
            this.currentUser = JSON.parse(JSON.stringify(res.data.data));
            this.new_roles_ids = [];
            this.deleted_roles_ids = [];
        },
        checkPassword() {
            if (this.password || this.passwordConfirm)
                if (this.password != this.passwordConfirm) {
                    this.showToast('Обновление пароля', 'Пароли не совпадают', 'danger')
                } else {
                    return this.password;
                }
        },
        toggleRoles(role) {
            if (this.currentUser.roles.includes(role.name)) {
                if (!this.originalUser.roles.includes(role.name))
                    this.new_roles_ids.push(role.id)
                this.deleted_roles_ids = this.removeItemFromArray(this.deleted_roles_ids, role.id);
            } else {
                if (this.originalUser.roles.includes(role.name))
                    this.deleted_roles_ids.push(role.id)
                this.new_roles_ids = this.removeItemFromArray(this.new_roles_ids, role.id);
            }
        },
        makeRequest() {
            return {
                name: this.currentUser.name,
                email: this.currentUser.email,
                is_active: this.currentUser.is_active,
                new_password: this.checkPassword(),
                new_roles_ids: this.new_roles_ids,
                deleted_roles_ids: this.deleted_roles_ids,
                '_method':'put'
            }
        }
    }
}
</script>
