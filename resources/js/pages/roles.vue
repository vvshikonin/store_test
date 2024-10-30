<template>
    <MainTable :meta="meta" :tableSettings="tableSettings" @per_page="onChangePerPage($event)"
        @add_element="$router.push('/roles/new')" @current_page="onChangeCurrentPage($event)"
        @confirm_filter="onConfirmFilter()" @clear_filter="onClearFilter()" :withHeadeSection="true">
        <template v-slot:header></template>
        <template v-slot:filters>
            <FilterInput v-model="name_filter" type="text" placeholder="Введите название" label="Название группы">
            </FilterInput>
        </template>
        <template v-slot:thead>
            <TableHeader @click="onSort('name')" :sort="setSortProp('name')">
                Название группы
            </TableHeader>
        </template>
        <template v-slot:tbody>
            <TableRow v-for="role in roles" :key="role" @click_row="toRole(role.id)">
                <TableCell>
                    {{ role.name }}
                </TableCell>
            </TableRow>
        </template>
    </MainTable>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { rolesAPI } from '../api/roles_api';
import MainTable from '../components/Tables/main_table.vue';
import TableHeader from '../components/Tables/th.vue';
import TableRow from '../components/Tables/tr.vue';
import TableCell from '../components/Tables/td.vue';
import FilterInput from '../components/inputs/filter_input.vue';

const router = useRouter();

let roles = ref([]);
let meta = ref({});

const tableSettings = reactive({
    isLoading: true,
    tableTitle: 'Группы пользователей',
    withExport: false,
    withFilters: true,
    withAddButton: true,
    withFooter: true,
    isCover: false,
    isNoEntries: false
});
const sort = reactive({ field: 'name', type: 'asc' });
const per_page = ref(25);
const current_page = ref(1);
const name_filter = ref(null);

async function loadRoles() {
    const selection = initRoleFilter();
    const response = await rolesAPI.index(selection);

    roles.value = response.data.data;
    meta.value = response.data.meta;
    console.log(roles);

    tableSettings.isLoading = false;
    tableSettings.isCover = false;
    tableSettings.isNoEntries = roles.value.length ? false : true;
}

function setSortProp(field) {
    let sortProp = {};
    if (field == sort.field) {
        sortProp.isActive = true;
        sortProp.type = sort.type;
    } else {
        sortProp.isActive = false;
    }
    return sortProp;
}

function onSort(field) {
    if (field == sort.field) {
        sort.type = sort.type == 'desc' ? 'asc' : 'desc';
    } else {
        sort.field = field;
        sort.type = 'asc';
    }
    tableSettings.isCover = true;
    loadRoles();
}

function onChangePerPage(event) {
    current_page.value = 1;
    per_page.value = event;
    tableSettings.isCover = true;

    loadRoles();
}

function onChangeCurrentPage(event) {
    current_page.value = event;
    tableSettings.isCover = true;

    loadRoles();
}

function onConfirmFilter() {
    tableSettings.isCover = true;
    current_page.value = 1;

    loadRoles();
}

function onClearFilter() {
    name_filter.value = null;
    tableSettings.isCover = true;

    loadRoles();
}

function initRoleFilter() {
    let selection = {};

    selection.per_page = per_page.value;
    selection.page = current_page.value;
    selection.sort_type = sort.type;
    selection.sort_field = sort.field;

    selection.name_filter = name_filter.value;

    return selection;
}

function toRole(id) {
    router.push('/roles/' + id + '/edit');
}

onMounted(loadRoles);
</script>