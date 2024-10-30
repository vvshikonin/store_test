<template>

    <Table>

        <template v-slot:filters>
            <FilterInput v-model="params.product" label="Товар в инвентаризации" placeholder="Название или артикул"></FilterInput>
            <FilterSelectSearch v-model="params.status" :options="inventoryStatuses" label="Статус"></FilterSelectSearch>
            <FilterSelectSearch v-model="params.user" :options="users" label="Пользователь"></FilterSelectSearch>
            <FilterInputBetween type="date" label="Дата проведения"
                v-model:start="params.date_start_filter"
                v-model:end="params.date_end_filter"
                v-model:equal="params.date_equal_filter"
                v-model:notEqual="params.date_notEqual_filter">
            </FilterInputBetween>
        </template>

        <template v-slot:thead>
            <TH field="id" class="col-1">
                #
            </TH>
            <TH class="col-4">
                Название
            </TH>
            <TH field="is_completed" class="col-2" align="center">
                Статус
            </TH>
            <TH class="col-2">
                Создал
            </TH>
            <TH class="col-2">
                Обновил
            </TH>
            <TH field="created_at" class="col-2">
                Дата проведения
            </TH>
        </template>

        <template v-slot:tbody>
            <TR @click_row="toInventory(inventory.id)" v-for="inventory in inventories">
                <TD class="col-1">
                    {{ inventory.id }}
                </TD>
                <TD class="col-4">
                    Инвентаризация №{{ inventory.id }}
                </TD>
                <TD class="col-2">
                    <div class="p-2 border rounded text-white text-center" :class="statusClass(inventory.is_completed)">
                        {{ inventory.is_completed ? 'Завершена' : 'В процессе'}}
                    </div>
                </TD>
                <TD class="col-2">
                    {{ inventory.username }}
                </TD>
                <TD class="col-2">
                    {{ inventory.updated_by }}
                </TD>
                <TD class="col-2">
                    {{ inventory.created_at }}
                </TD>
            </TR>
        </template>

    </Table>

</template>

<script>
import { inventoryAPI } from '../api/inventory_api.js';

import IndexTableMixin from '../utils/indexTableMixin.js'

export default {
    data() {
        return {
            inventories: [],
            users: [],
            inventoryStatuses: [
                {id: 0, name: 'В процессе'},
                {id: 1, name: 'Завершена'}
            ]

        }
    },
    mixins: [IndexTableMixin],

    methods: {
        initSettings() {
            this.settings.tableTitle = 'Инвентаризации';
            this.settings.createButtonText = 'Начать инвентаризацию';

            this.settings.withCreateButton = this.checkPermission('inventory_create')
            this.settings.withHeader = false;
            this.settings.withExport = false;
            this.settings.isLoading = true;
            this.settings.saveParams = false;
            this.settings.withBottomBox = false;
            this.settings.withFilterTemplates = true;

            this.settings.indexAPI = params => inventoryAPI.index(params);

            this.onInitData = res => {
                this.inventories = res.data.data;
                this.getUniqueUsernames(this.inventories);
            }

            this.onInitParamsDefault = defaultParams => {
                defaultParams.sort_field = this.params.sort_field || 'created_at';
                defaultParams.sort_type = this.params.sort_type || 'desc';
            }

            this.onClickCreateButton = () => this.onCreateInventory();
        },

        statusClass(complete) {
            if (complete) {
                return { 'bg-success': true, 'border-success': true };
            } else {
                return { 'bg-primary': true, 'border-secondary': true };
            }
        },

        toInventory(id) {
            this.$router.push('/inventories/' + id + '/edit');
        },

        async onCreateInventory() {
            await inventoryAPI.store().then((res) => {
                this.$router.push('/inventories/' + res.data.id + '/edit');
                this.showToast('Создание', 'Инвентаризация успешно начата!', 'success');
            })
        },

        getUniqueUsernames(data) {
            this.users = data.reduce((unique, inv) => {
                const user = {
                    id: inv.user_id,
                    name: inv.username
                }
                if (!unique.some(obj => obj.id === user.id)) {
                    unique.push(user)
                }
                return unique
            }, []);
        },
    },
}

</script>
