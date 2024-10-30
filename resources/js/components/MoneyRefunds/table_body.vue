<template>
    <tbody >
        <template v-for="refund in moneyRefunds" :key="refund.id" >
            <tr :class="{'group-rows' : isShowPosition(refund.id)}" class="main-row">
                <td  style="width: 1%;" >
                    <div class="d-flex justify-content-center" >
                        <button @click="onShowPositions(refund.id)" type="button" class="btn btn-outline-link border-0">
                            <div class="d-flex align-items-center text-muted" :class="{'group-rows' : isShowPosition(refund.id)}">
                                <font-awesome-icon icon="fa-solid fa-chevron-right" :rotation="isShowPosition(refund.id)? 90 : null" style="transition: .1s;"/>
                            </div>
                        </button>
                    </div>
                </td>
                <td v-if="settings.table[1].visability" class="col-1 align-middle index">{{refund.id}}</td>
                <td v-if="settings.table[2].visability" class="col-2 align-middle ">
                    <a v-if="refund.type" target="_blank" :href="'https://babylissrus.retailcrm.ru/orders/'+ refund.crm_id +'/edit'" style="text-decoration: none;">
                        <div class="d-flex align-items-center">
                            <img dalt="RetailCRM" src="https://s3-s1.retailcrm.tech/ru-central1/retailcrm-static/branding/retailcrm/logo/logo_icon_core.svg" style="width: 18px; height: 18px;">
                            <span class="ps-2">{{refund.number}}</span>
                        </div>
                    </a>
                    <a v-else target="_blank" :href="getInvoiceLink(refund.invoice_id)"  style="text-decoration: none;">
                        <div class="d-flex align-items-center">
                            <font-awesome-icon icon="fa-solid fa-clipboard" size="sm" style="width: 18px; height: 18px;"/>
                            <span class="ps-2">{{refund.number}}</span>
                        </div>
                    </a>
                </td>
                <td v-if="settings.table[3].visability" class="col-2 align-middle">{{refund.contractors_names}}</td>
                <td v-if="settings.table[4].visability" class="col-1 align-middle">{{refund.sum ? (refund.sum).toFixed(2) : ''}} ₽</td>
                <td v-if="settings.table[5].visability" class="align-middle" style="width:1%">
                    <div class="form-check d-flex justify-content-center">
                        <input @change="onUpdate(refund)" class="form-check-input" type="checkbox" v-model="refund.status" :disabled="!checkPermission('money_refund_update')">
                    </div>
                </td>
            </tr>
            <Transition >
                <tr v-show="isShowPosition(refund.id)" :class="{'group-rows' : isShowPosition(refund.id)}" class="sub-row">
                    <td colspan="6" class="m-0 p-0" >
                        <template v-if="positions[refund.id]">
                            <table class="table table-borderless table-light  m-0" >  
                                <caption class="bg-light ps-2 pb-3">
                                    <span>Счёт/заказ № {{refund.number}}</span>
                                </caption>
                                <thead>
                                    <tr>
                                        <th>№</th>
                                        <th>Артикул</th>
                                        <th>Название</th>
                                        <th>Поставщик</th>
                                        <th>Цена</th>
                                        <th>Кол-во</th>
                                        <th>Сумма</th>
                                    </tr>
                                </thead>
                                <tbody v-for="position, index in positions[refund.id]">
                                    <tr>
                                        <td class="col-1">{{index+1}}</td>
                                        <td class="col-1">{{position.sku}}</td>
                                        <td class="col-3">{{position.name}}</td>
                                        <td class="col-2">{{position.contractor_name}}</td>
                                        <td class="col-1">{{position.price ? (position.price).toFixed(2) : ''}} ₽</td>
                                        <td class="col-1">{{position.amount}}</td>
                                        <td class="col-1">{{(position.price * position.amount).toFixed(2)}} ₽</td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                        <div v-else class="bg-light p-2 d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </td>
                </tr>
            </Transition>
        </template>
    </tbody>
</template>
<style>
    .main-row.group-rows, .sub-row.group-rows{
        border-left: 2px solid  #0d6efd; 
    }
    .main-row.group-rows div.group-rows, .main-row.group-rows td, .sub-row caption{
        color: #0d6efd!important;
    }
    .main-row.group-rows:nth-child(4n+3), .sub-row.group-rows:nth-child(4n+4){
        border-left: 2px solid  #6c757d!important; 
    }
    .main-row.group-rows:nth-child(4n+3) div.group-rows, .main-row.group-rows:nth-child(4n+3) td, .sub-row:nth-child(4n+4) caption{
        color: #6c757d!important;
    }
</style>
<script>
import { mapGetters } from 'vuex';
export default {
    data(){
        return {
            positionsVisability:{} 
        }
    },
    computed: {
        ...mapGetters({  moneyRefunds: 'getMoneyRefund', settings: 'getMoneyRefundSettings', positions: 'getMoneyRefundPositions', roleId: 'getRoleId'}),
    },
    methods: {
        onUpdate: function(refund){
            let id = refund.id;
            this.$store.dispatch('updateMoneyRefund', {id, refund})
        },
        onShowPositions: function(id){
            if(!this.positions[id]){
                this.$store.dispatch('showMoneyRefund', id);
            }
            this.positionsVisability[id] = !this.positionsVisability[id]
        },
        isShowPosition: function(id){
            return this.positionsVisability[id];
        },
        getInvoiceLink: function(invoice_id){
            return location.protocol + '//' + location.host + '/#/invoices/' + invoice_id +'/edit';
        },
        isInputDisabled: function(){
            if(this.roleId === 1 || this.roleId === 2 || this.roleId === 3) {
                return false;
            }
            return true;
        }
    },
    beforeUnmount(){
        this.$store.dispatch('clearPositions');
    }
}
</script>