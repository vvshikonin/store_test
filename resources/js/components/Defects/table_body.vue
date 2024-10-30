<template>
    <tbody>
        <template v-for="defect, index in defects">
            <tr  :class="{'table-secondary': (defect.status == 2 || defect.status == 4), 'table-danger': defect.status == 3}">
                <td class="align-middle col-1 text-nowrap" style="max-width: 150px;"> {{defect.created_at ? moment(defect.created_at).format('DD.MM.YYYY') : '-'}} </td>
                <td class="align-middle col-1">  
                    <a target="_blank" :href="'https://babylissrus.retailcrm.ru/orders/'+ defect.crm_id +'/edit'" style="text-decoration: none;">
                        <div class="d-flex align-items-center">
                            <img dalt="RetailCRM" src="https://s3-s1.retailcrm.tech/ru-central1/retailcrm-static/branding/retailcrm/logo/logo_icon_core.svg" style="width: 18px; height: 18px;">
                            <span class="ps-2">{{defect.number}}</span>
                        </div>
                    </a>
                </td>
                <td class="align-middle col-1 text-nowrap" style="max-width: 150px;"> {{defect.sku}} </td>
                <td class="align-middle col-3" :title="defect.product_name" style="max-width: 300px;"> 
                    <div class="text-truncate">
                        <span >{{defect.product_name}}</span> 
                    </div>
                </td>
                <td class="align-middle col-1 text-nowrap"> {{defect.contractor_name}}</td>
                <td class="align-middle col-1"> {{defect.price}}</td>
                <td class="align-middle" style="width: 1%;"> {{defect.amount}}</td>
                <td class="align-middle" style="width: 1%;"> {{defect.sum}} ₽</td>

                <td class="align-middle col-2 fw-bold" > {{defect.defect_status}} </td>

                <td class="align-middle col-3"> 
                    <div class="input-group input-group-sm">
                        <input @blur="onUpdate(defect)" type="text" class="form-control border-0 bg-transparent rounded-0" :disabled="!checkPermission('defect_comment_update')"  placeholder="Добавить комментарий" v-model="defect.comment">
                    </div>
                </td>
                <td class="align-middle col-3"> 
                    <div v-if="defect.defect_status != 'Ожидается'" class="form-check">
                        <input @change="onUpdate(defect)" class="form-check-input" type="checkbox" v-model="defect.money_refund" :disabled="!checkPermission('defect_mf_update')">
                    </div>
                </td>
            </tr>
        </template>
    </tbody>
</template>
<script>
import moment from 'moment';
import { mapGetters } from 'vuex';
export default{
    methods:{
        moment: function (date) {
            return moment(date);
        },
        onUpdate: function(defect){
            let id = defect.id;
            let comment = defect.comment;
            let status = defect.status;
            this.$store.dispatch('updateDefects', {id, comment, status});
        },
        isInputDisabled: function(){
            if(this.roleId === 1 || this.roleId === 2) {
                return false;
            }
            return true;
        }
    },
    computed: {
        ...mapGetters({ defects: 'getDefects', roleId: 'getRoleId'}),
    },
}
</script>