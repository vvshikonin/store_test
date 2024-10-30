<template>
    <div v-if="userName"  
        class="d-flex position-relative justify-content-center align-items-center border border-2 rounded-circle bg-opacity-75"
        style="pointer-events: none; object-fit: cover; background-position: center center; background-size: cover;" 
        :style="{
            'background-image': avatar || src ? 'url(' + currentAvatar + ')' : 'none',
            'background-color': 'rgba(' + userColor + ',' + bgOpacity +')',
            'border-color': 'rgba(' + userColor + ', 1)!important',
            'width': width + 'px',
            'height': height + 'px'
            }">
        <!-- <img v-if="currentAvatar" :src="currentAvatar" class="rounded-circle w-100 h-100"> -->
        <h3 v-if="currentAvatar == null" class="mb-0 text-white align-middle user-select-none" :style="{ 'font-size': fontSize + 'px'}">{{ userName.toUpperCase()[0] }}</h3> 
    </div>
</template>
<script>

export default{
    data() {
        return {
            fontSize: null,
        }
    },
    props: {
        avatar: {
            default: null,
            type: String,
        },
        userName:String,
        userColor:String,
        width: {
            default: 45,
            type: Number,
        },
        height: {
            default: 45,
            type: Number,
        },
        bgOpacity:{
            default: '0.75',
            type: String
        },
        src: {
            default: null,
            type: String
        }
    },
    mounted() {
        this.fontSize = this.height * 0.5;
    },
    computed: {
        currentAvatar(){
            if (this.src == null && this.avatar == null) {
                return null;
            }
            if (this.src !== null) {
                return this.src;
            } else {
                return 'storage/' + this.avatar;
            }
        }
    }
}
</script>