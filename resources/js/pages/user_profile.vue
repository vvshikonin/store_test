<template>
    <EntityEditPage :loadingCover="isCover" :isLoaded="isLoaded">
        <template v-slot:header>
            <h3>Пользователь {{ user.name }}</h3>
        </template>
        <template v-slot:content>
            <div class="d-flex flex-row w-100">
                <Card title="Аватар пользователя" class="w-50">
                    <div class="d-flex align-items-center flex-row p-3 w-100 position-relative">
                        <UserAvatar :src="previewAvatar" :avatar="user.avatar" :height="180" :width="180" :userName="user.name" :userColor="user.color"></UserAvatar>
                        
                        <button v-if="previewAvatar" @click="cleanAvatarPreview()" type="button"
                            style="height: 30px; width: 30px; top: 25px; left: 150px"
                            class="position-absolute btn btn-light border border-1 justify-content-center
                                rounded-circle d-flex align-items-center">
                            <font-awesome-icon icon="fa-solid fa-xmark" />
                        </button>
                        
                        <div class="d-flex flex-column w-75 pt-2 pb-2 user-avatar-input-wrapper">  
                            <div class="d-flex flex-row position-relative">
                                <button @click="toggleLoadForm()" style="width: 150px; z-index: 5; padding-left: .55rem!important; padding-right: .55rem!important" type="button" 
                                    class="btn justify-content-center align-items-center d-flex btn-primary ms-2">
                                    {{ loadButtonLabel }}
                                </button>
                                <input type="file" @change="onLoadFile()" ref="userAvatarInput"
                                    class="form-control position-absolute d-flex align-items-center justify-content-center"
                                    :style="{'width': showForm ? '70%' : '0'}"
                                    style="transition: .4s all; z-index: 4; left: .5rem"
                                    accept=".png,.jpg,.jpeg,.bmp,.webp"/>
                            </div>
                            <button style="width: 150px" type="button" @click="deleteAvatar()"
                                class="align-items-center justify-content-center d-flex ms-2 mt-1 btn btn-danger">
                                Удалить аватар
                            </button>
                        </div>
                    </div>
                </Card>
                <Card title="Данные пользователя" class="ms-1 w-50">
                    <div class="d-flex flex-column w-50 user-data-wrapper">
                        <inputDefault v-model="user.name" autocomplete="name" label="Имя"></inputDefault>
                        <inputDefault v-model="user.email" autocomplete="username" label="Email"></inputDefault>
                        <inputDefault v-model="user.roles" autocomplete="roles" :disabled="true" label="Роли"></inputDefault>

                    </div>
                    <div class="d-flex flex-column w-50 mt-3 pe-2">
                        <div class="h5 mb-4">Изменить пароль:</div>
                        <div class="d-flex flex-wrap user-password-wrapper">
                            <inputDefault v-model="user.new_password" autocomplete="new-password" label="Пароль" type="password" placeholder="Введите новый пароль"></inputDefault>
                            <inputDefault v-model="newPasswordConfirmation" autocomplete="confirm-new-password" label="Подтверждение пароля" type="password" placeholder="Введите новый пароль повторно"></inputDefault>
                            <span class="small text-danger" v-if="passwordConfirmationResult"> {{ passwordConfirmationResult }} </span>
                        </div>
                    </div>
                </Card>
            </div>
        </template>

        <template v-slot:save-box>
            <button @click="onSave()" type="button" class="btn btn-light me-2 border border-1"
                style="margin-left:40px" :disabled="!canSave">
                <font-awesome-icon class="me-2 text-success" icon="fa-solid fa-check" />
                Сохранить изменения
            </button>
        </template>

    </EntityEditPage>
</template>

<style>

    .user-data-wrapper > div {
        width: 90%!important;
    }
    .user-password-wrapper > div {
        width: 100%!important;
        margin-left: 0!important;
    }
    .user-password-wrapper label {
        padding-left: 0!important;
    }

</style>

<script>

import { mapGetters } from 'vuex';
import { userAPI } from '../api/user_api';

import EntityEditPage from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';
import UserAvatar from '../components/Other/user_avatar.vue';

import inputDefault from '../components/inputs/default_input.vue';
import SelectInput from '../components/inputs/select_input.vue';

export default {
    components: { EntityEditPage, Card, inputDefault, SelectInput, UserAvatar },
    computed: {
        ...mapGetters({ 
            userID: 'getUserID', user: 'getUserData', userAvatar: 'getUserAvatar'
        }),
        passwordConfirmationResult() {
            if (this.user.new_password && this.newPasswordConfirmation) {
                if (this.user.new_password === this.newPasswordConfirmation) {
                    return ''
                } else {
                    return 'Пароли не совпадают!'
                }
            }
        },
        canSave() {
            if (this.user.new_password || this.newPasswordConfirmation) {
                if (this.user.new_password === this.newPasswordConfirmation) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        },
    },
    data() {
        return {
            currentName: null,
            currentEmail: null,
            previewAvatar: null,
            newPasswordConfirmation: null,
            isLoaded: false,
            isCover: false,
            showForm: false,
            loadButtonLabel: 'Загрузить аватар'
        }
    },
    mounted() {
        this.$store.dispatch('loadUserData').then(() => {
            if(this.userActiveStatus === 0){
                this.$router.push('/forbidden');
            }
            this.show();
        });
    },
    methods: {
        async show() {        
            const res = await userAPI.show(this.userID);
            this.initUserData(res);
        },
        initFormData(usePut) {
            const formData = this.makeFormData(this.user, usePut);

            formData.set('name', this.currentName);
            formData.set('email', this.currentEmail);

            if (this.$refs.userAvatarInput.files.length) {
                formData.append('new_avatar', this.$refs.userAvatarInput.files[0]);
            }
            return formData;
        },
        toggleLoadForm() {
            this.showForm = !this.showForm;
            this.loadButtonLabel = this.showForm ? 'Скрыть форму' : 'Загрузить аватар';
        },
        async initUserData(res) {
            if (res) this.user = res.data.data;
            this.currentName = this.user.name;
            this.currentEmail = this.user.email;
            this.user.new_password = null;
            this.isLoaded = true;
        },
        cleanAvatarPreview() {
            this.previewAvatar = null;
            this.$refs.userAvatarInput.value = '';
        },
        async deleteAvatar() {
            this.isCover = true;
            await userAPI.delete_avatar(this.userID).then((res) => {
                this.showToast("ОК", "Аватар успешно удален", "success");
                this.user.avatar = null;
                this.showForm = false;
                this.loadButtonLabel = 'Загрузить аватар';
                this.isCover = false;
            });
        },
        async onSave() {
            this.isCover = true;
            const formData = this.initFormData(true);
            await this.$store.dispatch('updateUserData', {userId: this.userID, formData: formData});
            this.$refs.userAvatarInput.value = '';
            this.previewAvatar = null;
            this.showForm = false;
            this.loadButtonLabel = 'Загрузить аватар';
            this.showToast("ОК", "Изменения сохранены", "success");
            this.isCover = false;
        },
        onLoadFile() {
            let files = this.$refs.userAvatarInput.files;
            if (FileReader && files && files.length) {
                let fr = new FileReader();               
                fr.onload = (event) => {
                    this.previewAvatar = event.target.result;
                }
                fr.readAsDataURL(files[0])
            }
        }
    },
}
</script>