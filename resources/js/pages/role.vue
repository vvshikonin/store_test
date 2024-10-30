<template>
    <EntityLayout :withDeleteButton="isEditing" :isLoaded="isLoaded" :loadingCover="isCover"
        :entityName="'роль &quot;' + originalRole.name + '&quot;'" :withSaveButton="true" @save="sendSaveRequest()"
        @exit="onExit()" @destroy="sendDeleteRequest()">
        <template v-slot:header>
            <h3>Группа пользователей "{{ originalRole.name }}"</h3>
        </template>
        <template v-slot:content>
            <Card title="Сведения о группе пользователей">
                <inputDefault label="Название группы" placeholder="Введите название" v-model="role.name" :required="true" />
            </Card>
            <Card title="Доступы для группы пользователей">
                <div v-if="!permissions.length" class="d-flex justify-content-center align-items-center w-100 p-5 m-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <template v-else>
                    <div class="d-flex flex-row flex-wrap justify-content-start">
                        <div v-for="permissionGroup, groupIndex in  permissions" class="bg-light p-3 m-1 border rounded-1"
                            style="max-height: 400px; overflow-y: auto;">
                            <div class="mb-2 d-flex align-center">
                                <input class="form-check-input me-1" :id="groupIndex"
                                    @change.prevent="onPermissionGroupCheck(permissionGroup, $event)"
                                    style="font-size: 14px" type="checkbox" v-bind="groupCheckBoxProps(permissionGroup)">
                                <label class="ps-1 fw-bolder" :for="groupIndex" style="font-size: 14px">{{
                                    permissionGroup.name
                                }}</label>
                            </div>
                            <div v-for="permission, permissionIndex in permissionGroup.permissions"
                                class="ms-3 d-flex align-center">
                                <input v-model="role.permissions" class="form-check-input me-1"
                                    :id="groupIndex + '_' + permissionIndex" type="checkbox" :value="permission.name"
                                    style="font-size: 14px;" @change="onPermissionCheck(permission.id, permission.name)">
                                <label class="ps-1" :for="groupIndex + '_' + permissionIndex" style="font-size: 14px;">{{
                                    permission.label }}</label>
                            </div>
                        </div>
                    </div>
                </template>

            </Card>
        </template>
    </EntityLayout>
</template>

<script>
import { rolesAPI } from '../api/roles_api';
import { permissionAPI } from '../api/permission_api';
import EntityLayout from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';
import inputDefault from '../components/inputs/default_input.vue';

export default {
    components: { EntityLayout, Card, inputDefault },
    data() {
        return {
            id: this.$route.params.role_id,
            role: {},
            isCover: false,
            isLoaded: false,
            originalRole: {},
            newPermissions: [],
            deletedPermissions: [],
            permissions: [],
        }
    },
    methods: {
        // Метод для загрузки данных продукта из API
        async loadRole() {
            const response = await rolesAPI.show(this.id);
            this.isLoaded = true;
            this.roleInit(response);
        },
        roleInit(response) {
            this.role = response.data.data;
            this.originalRole = JSON.parse(JSON.stringify(response.data.data));
            this.newPermissions = [];
            this.deletedPermissions = [];
        },
        newRoleInit() {
            this.role.name = "";
            this.role.permissions = [];
            this.originalRole.name = "Новая роль";
            this.originalRole.permissions = [];
        },
        onPermissionCheck(permissionId, permissionName) {
            if (this.role.permissions.includes(permissionName)) {
                if (!this.originalRole.permissions.includes(permissionName))
                    this.newPermissions.push(permissionId);
                this.deletedPermissions = this.removeItemFromArray(this.deletedPermissions, permissionId);
            } else {
                if (this.originalRole.permissions.includes(permissionName))
                    this.deletedPermissions.push(permissionId)
                this.newPermissions = this.removeItemFromArray(this.newPermissions, permissionId);
            }
        },
        makeRequest() {
            return {
                name: this.role.name,
                newPermissions: this.newPermissions,
                deletedPermissions: this.deletedPermissions
            }
        },
        async sendSaveRequest() {
            this.isCover = true;
            const request = this.makeRequest()
            if (this.isEditing) {
                const id = this.originalRole.id;
                const response = await rolesAPI.update(id, request);
                this.roleInit(response);
                this.showToast('Обновление данных', 'Данные о роли обновлены', 'success')
            } else {
                const response = await rolesAPI.store(request);
                this.showToast('Роль создана', 'Новая роль успешно создана', 'success');
                this.roleInit(response);
                this.$router.push('/roles/' + response.data.data.id + '/edit');
            }
            this.isCover = false;

        },
        async sendDeleteRequest() {
            const id = this.originalRole.id;
            const res = await rolesAPI.destroy(id);
            this.showToast('Удалено', 'Роль успешно удалена', 'success');
            this.$router.push('/roles');
        },
        onExit() {
            this.$router.push('/roles');
        },
        async loadPermissions() {
            this.permissions = (await permissionAPI.index()).data.data;
        },
        onPermissionGroupCheck(permissionGroup, event) {
            permissionGroup.permissions.forEach(groupPermission => {
                if (!this.role.permissions.includes(groupPermission.name) && event.target.checked) {
                    this.role.permissions.push(groupPermission.name)
                } else if (this.role.permissions.includes(groupPermission.name) && !event.target.checked) {
                    this.role.permissions = this.removeItemFromArray(this.role.permissions, groupPermission.name);
                }
                this.onPermissionCheck(groupPermission.id, groupPermission.name)
            });

        },
        groupCheckBoxProps(permissionGroup) {
            let permissions = [];
            permissionGroup.permissions.forEach(permission => {
                permissions.push(permission.name);
            });

            let count = this.role.permissions.reduce((count, permission) => count + (permissions.includes(permission) ? 1 : 0), 0)
            // console.log(count + " " + permissions.length);

            let checked = false;
            let indeterminate = false;

            if (permissions.length == count) {
                checked = true;
            } else if (count > 0 && count < permissions.length) {
                indeterminate = true
            }

            return {
                value: permissions,
                checked,
                indeterminate,
            }
        }
    },
    computed: {
        isEditing() {
            return !!this.$route.params.role_id
        },
    },
    mounted() {
        if (this.isEditing) {
            this.loadRole();
        } else {
            this.newRoleInit();
            this.isLoaded = true;
        }
        this.loadPermissions();
    }
}
</script>