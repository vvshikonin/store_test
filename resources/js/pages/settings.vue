<template>
    <EntityEditPage :entityName="pageParams.entityName" :isLoaded="pageParams.isLoaded"
        :withDeleteButton="pageParams.withDeleteButton" :withSaveButton="pageParams.withSaveButton"
        :loadingCover="pageParams.isCover">
        <template v-slot:header>
            <span><h2>Настройки приложения</h2></span>
        </template>
        <template v-slot:content>
            <Card title="Настройки приложения">
                <span class="d-flex w-100" v-for="setting in settings">
                    <SelectInput :label="setting.label" v-model="setting.name" :options="value"></SelectInput>
                    <span class="d-flex align-items-center"> Подробное описание опции </span>
                </span>
            </Card>
        </template>
    </EntityEditPage>
</template>



<script>
import { settingsAPI } from '../api/settings_api'

import EntityEditPage from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';

import inputDefault from '../components/inputs/default_input.vue';
import SelectInput from '../components/inputs/select_input.vue';

export default {
    components: { EntityEditPage, Card, inputDefault, SelectInput },
    data() {
        return {
            settings: [],
            pageParams: {
                entityName: 'Настройки',
                isLoaded: false,
                isCover: false,
                withDeleteButton: false,
                withSaveButton: true,
            }
        }
    },
    mounted(){
        this.$store.dispatch('loadUserData');
        this.index();
    },
    methods: {
        async index() {
            const res = await settingsAPI.index()
            this.settings = res.data.data;
            this.pageParams.isLoaded = true;
        }
    }
}

</script>
