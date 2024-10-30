<template>
    <MainTable :meta="meta" :tableSettings="tableSettings" @per_page="changePerPage($event)"
        @current_page="changeCurrentPage($event)" @confirm_filter="loadWithCover()" @clear_filter="clearFilter()">
        <template v-slot:filters>
            <FilterInput v-model="params.name_filter" label="Имя пользователя" placeholder="Введите имя"></FilterInput>
        </template>
        <template v-slot:thead>
            <TableHeader :sort="setSortProp('name')" @click="sort('name')">
                Имя пользователя
            </TableHeader>
            <TableHeader :sort="setSortProp('email')" @click="sort('email')">
                Email
            </TableHeader>
        </template>
        <template v-slot:tbody>
            <TableRow v-for="user in users" @click_row="toUser(user.id)">
                <TableCell>
                    <div class="d-flex flex-row align-items-center">
                        <UserAvatar class="me-3" :avatar="user.avatar" :userName="user.name" :userColor="user.color"></UserAvatar>
                        {{ user.name }}
                    </div>
                </TableCell>
                <TableCell>
                    {{ user.email }}
                </TableCell>
            </TableRow>
        </template>
    </MainTable>
</template>

<script>
import { userAPI } from '../api/user_api'

import MainTable from '../components/Tables/main_table.vue'
import TableHeader from '../components/Tables/th.vue'
import TableRow from '../components/Tables/tr.vue'
import TableCell from '../components/Tables/td.vue'
import FilterInput from '../components/inputs/filter_input.vue'
import UserAvatar from '../components/Other/user_avatar.vue'

export default {
    components: { MainTable, TableHeader, TableRow, TableCell, FilterInput, UserAvatar },
    data() {
        return {
            users: [],
            meta: [],
            tableSettings: {
                isLoading: true,
                tableTitle: 'Пользователи',
                withExport: false,
                withFilters: true,
                withAddButton: false,
                withFooter: true,
                isNoEntries: false,
                isCover: false,
            },
            params: {
                page: 1,
                per_page: 25,
                sort_field: 'name',
                sort_type: 'asc'
            }
        }
    },
    mounted() {
        this.index()
    },
    methods: {
        async index() {
            const res = await userAPI.index(this.params)
            this.users = res.data.data;
            this.meta = res.data.meta;
            this.tableSettings.isLoading = false;
            this.tableSettings.isNoEntries = res.data.data.length ? false : true;
        },

        async loadWithCover() {
            this.tableSettings.isCover = true;
            await this.index()
            this.tableSettings.isCover = false;
        },

        async changePerPage(per_page) {
            this.updateParams({ per_page });
            await this.loadWithCover();
        },
        async changeCurrentPage(page) {
            this.updateParams({ page });
            await this.loadWithCover();
        },
        async clearFilter() {
            this.updateParams({ name_filter: null });
            await this.loadWithCover();
        },
        async sort(sort_field) {
            let sort_type = null;
            if (this.params.sort_field == sort_field)
                sort_type = this.params.sort_type == 'asc' ? 'desc' : 'asc'
            else sort_type = 'asc';

            this.updateParams({ sort_field, sort_type })
            this.loadWithCover()
        },

        toUser(id) {
            this.$router.push('users/' + id + '/edit');
        },

        setSortProp(field) {
            let isActive = this.params.sort_field == field;
            let type = this.params.sort_type;
            return { isActive, type }
        },

        updateParams(paramsToUpdate) {
            this.params = { ...this.params, ...paramsToUpdate };
        },


    }
}
</script>