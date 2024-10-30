export default {
    created(){
        let route = this.$route.matched[0];
        if(!route) return;
        route.leaveGuards = new Set([function(to, from, next){ 
            alert('beforeRouteLeave сработал')
            next();
        }]);
    }
}