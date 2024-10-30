<template >
<div v-if="settings.isShowTableSettings">
    <div class="position-fixed bg-secondary " style="width: 100vw; height:100vh; left:0; top:0; opacity:0.3; z-index: 100;"></div>
    <div class="modal d-block">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Колонки в таблице</h5>
                    <button @click="onClose()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-row flex-wrap">
                        <template v-for="column in newTableView">
                            <div v-if="column.text" class="form-check w-25">
                                <input class="form-check-input" type="checkbox" v-model="column.visability">
                                <label class="form-check-label">
                                    {{column.text}}
                                </label>
                            </div>
                        </template>
                    </div>
                </div>
                <div class="modal-footer" style="background-color: #f9fafb;">
                    <button @click="onClose()" type="button" class="btn btn-light bg-gradient" data-bs-dismiss="modal">Отмена</button>
                    <button @click="onSave()" type="button" class="btn btn-primary">Сохранить</button>
                </div>
            </div>
        </div>
    </div>
</div>

</template>
<script>
export default{
    inject:['settings', 'ls_name'],
    methods:{
        onClose: function(){
            this.settings.isShowTableSettings = !this.settings.isShowTableSettings;
        },
        onSave: function(){
            localStorage.setItem(this.ls_name+'-table', JSON.stringify(this.newTableView))
            this.settings.table = JSON.parse(localStorage.getItem(this.ls_name+'-table'));
            this.onClose();
        }
    },
    data(){
        return {
            newTableView: null,
        }
    },
    mounted(){
        this.newTableView = this.settings.table = JSON.parse(localStorage.getItem(this.ls_name+'-table'));
    }
}
</script>