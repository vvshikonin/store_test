<template>
    <tbody>
        <tr v-for="productRefund, index in productRefunds">
            <td class="align-middle col-1 text-nowrap" style="max-width: 150px;"> {{productRefund.sku}} </td>
            <td class="align-middle col-3" :title="productRefund.product_name" style="max-width: 300px;"> 
                <div class="text-truncate">
                    <span >{{productRefund.product_name}}</span> 
                </div>
            </td>
            <td class="align-middle" style="width: 1%;"> {{productRefund.amount}}</td>
            <td class="align-middle col-1"> {{productRefund.price}}</td>
            <td class="align-middle col-2 text-nowrap"> {{productRefund.contractor_name}}</td>
            <td class="align-middle col-1"> {{productRefund.type ? 'Поставщику' : 'На склад'}} </td>
            <td class="align-middle col-1">{{ productRefund.product_location }}</td>
            <td class="align-middle col-1">  
                <a target="_blank" :href="'https://babylissrus.retailcrm.ru/orders/'+ productRefund.crm_id +'/edit'" style="text-decoration: none;">
                    <div class="d-flex align-items-center">
                        <img dalt="RetailCRM" src="https://s3-s1.retailcrm.tech/ru-central1/retailcrm-static/branding/retailcrm/logo/logo_icon_core.svg" style="width: 18px; height: 18px;">
                        <span class="ps-2">{{productRefund.order_number}}</span>
                    </div>
                </a>
            </td>
            <td class="align-middle col-3"> 
                <div class="input-group input-group-sm">
                    <input @blur="onUpdateComment(productRefund)" :disabled="isInputDisabled()" type="text" class="form-control border-0 bg-transparent rounded-0"  placeholder="Добавить комментарий" v-model="productRefund.comment">
                </div>
            </td>
            <td class="align-middle col-1 text-nowrap"> {{moment(productRefund.created_at).format('DD.MM.YYYY')}} </td>
            <td class="align-middle col-1 text-nowrap"> {{productRefund.completed_at ? moment(productRefund.completed_at).format('DD.MM.YYYY') : '-'}} </td>
        </tr>
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
        onUpdateComment: function(productRefund){
            let id = productRefund.id;
            let comment = productRefund.comment;
            this.$store.dispatch('updateProductRefund', {id, comment});
        },
        isInputDisabled: function(){
            if(this.roleId === 1 || this.roleId === 2 || this.roleId === 3 || this.roleId === 4) {
                return false;
            }
            return true;
        }
    },
    computed: {
        ...mapGetters({ productRefunds: 'getProductRefund', roleId: 'getRoleId'}),
    },
}
</script>