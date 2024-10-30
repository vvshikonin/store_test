<template>
    <div class="p-2 border rounded text-center order-status-bg " :class="statusBgClass()" style="width: 260px; height: auto">
        <strong >{{ statusName }}</strong>
    </div>
</template>

<style>

    .order-status-bg {
        height: 40px;
        background-position: top 6px left 8px;
        background-repeat: no-repeat;
        background-size: 24px;
    }
    .order-status-new {
        background-color: rgb(254, 165, 48);
        border-color:  rgb(254, 165, 48)!important;
        color: #ffffff;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M12.5 4h-1a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5zm-6.255 6.334A6 6 0 0 1 12 6a6 6 0 0 1 3.2 11.07 1.48 1.48 0 0 0-.7 1.27v.16a.5.5 0 0 1-.5.5h-4a.5.5 0 0 1-.5-.5v-.16a1.5 1.5 0 0 0-.7-1.27 6 6 0 0 1-2.555-6.736zM8 12a4 4 0 1 0 8 0 4 4 0 0 0-8 0zm2 9.59a1 1 0 0 0 .29.7l.42.42a1 1 0 0 0 .7.29h1.18a1 1 0 0 0 .7-.29l.42-.42a1 1 0 0 0 .29-.7V21h-4v.59zM20.13 4.58l-.71-.71a.48.48 0 0 0-.7 0L17.3 5.28a.51.51 0 0 0 0 .71l.7.71a.51.51 0 0 0 .71 0l1.41-1.42a.48.48 0 0 0 .01-.7zM5.28 3.87 6.7 5.28a.51.51 0 0 1 0 .72l-.7.7a.51.51 0 0 1-.72 0L3.87 5.28a.48.48 0 0 1 0-.7l.71-.71a.48.48 0 0 1 .7 0zM22.5 11h-2a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h2a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm-21 0h2a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-approval {
        background-color: rgba(254, 165, 48, 0.2);
        border-color:  rgba(254, 165, 48, 0.2)!important;
        color: #bc6b01;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23E18001'%3E%3Cpath fill-rule='evenodd' d='M18 2a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4H8a3 3 0 0 0-2.15.85l-3 3a.49.49 0 0 1-.35.15.5.5 0 0 1-.5-.5V6a4 4 0 0 1 4-4h12zM8 16h10a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v11.88l.46-.42A5 5 0 0 1 8 16zm-1.5-3h7a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5zm11-4h-11a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-assembling {
        background-color:rgba(101, 40, 215, 0.1);
        border-color:  rgba(101, 40, 215, 0.1)!important;
        color: #4a2aa5;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%236528D7'%3E%3Cpath fill-rule='evenodd' d='M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12a10 10 0 0 0 10 10zm0-18a8 8 0 1 1 0 16 8 8 0 0 1 0-16zm-.19 14a.77.77 0 0 1-.53-.22l-3.13-3.13a.5.5 0 0 1 0-.71l.71-.71a.49.49 0 0 1 .7 0L11 14.67V6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v8.17l1.44-1.45a.5.5 0 0 1 .71 0l.7.71a.5.5 0 0 1 0 .71l-3.13 3.14a.75.75 0 0 1-.53.22h-.38z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-delivery {
        background-color:rgba(0, 94, 235, 0.13);
        border-color:  rgba(0, 94, 235, 0.13)!important;
        color: #0044b7;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%230052CC'%3E%3Cpath fill-rule='evenodd' d='M3.169 2.445a1 1 0 0 1 1.386-.277l3 2A1 1 0 0 1 8.001 5v11.17A3.008 3.008 0 0 1 9.83 18H18a1 1 0 1 1 0 2H9.83A3.001 3.001 0 1 1 6 16.17V5.536L3.447 3.832a1 1 0 0 1-.277-1.387zM7 18h-.01a1 1 0 1 0 .02 0H7zM9 7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-8a2 2 0 0 1-2-2V7zm4 0h-2v8h8V7h-2v3h-4V7z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-complete {
        background-color:rgba(34, 201, 147, 0.15);
        border-color:  rgba(34, 201, 147, 0.15)!important;
        color: #168561;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%2319976E'%3E%3Cpath fill-rule='evenodd' d='M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm3.12-11.51a.5.5 0 0 1 .7 0l.5.53a.5.5 0 0 1 0 .71l-5.62 5.62a.48.48 0 0 1-.7 0l-2.35-2.37a.5.5 0 0 1 0-.71l.53-.53a.48.48 0 0 1 .7 0l1.5 1.49 4.74-4.74z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-cancel {
        background-color:rgba(255, 83, 83, 0.15);
        border-color:  rgba(255, 83, 83, 0.15)!important;
        color: #b73232;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23DD4040'%3E%3Cpath fill-rule='evenodd' d='M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10A10 10 0 0 0 12 2zm0 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16zm2.56-11.09.53.53a.48.48 0 0 1 0 .7L13.24 12l1.85 1.86a.48.48 0 0 1 0 .7l-.53.53a.48.48 0 0 1-.7 0L12 13.24l-1.86 1.85a.48.48 0 0 1-.7 0l-.53-.53a.48.48 0 0 1 0-.7L10.76 12l-1.85-1.86a.48.48 0 0 1 0-.7l.53-.53a.48.48 0 0 1 .7 0L12 10.76l1.86-1.85a.48.48 0 0 1 .7 0z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-vozvrat {
        background-color: rgba(175, 185, 195, 1);
        border-color: rgba(175, 185, 195, 1)!important;
        color: #fff;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M16.004 8A4 4 0 1 1 8 7.998 4 4 0 0 1 16.004 8Zm-2 0a2 2 0 1 0-4.001 0 2 2 0 0 0 4 0Zm5.892 10.55-1.89-3.89A3.001 3.001 0 0 0 15.263 13H8.742a3.001 3.001 0 0 0-2.691 1.66L4.11 18.55A1 1 0 0 0 5 20h14.006a1 1 0 0 0 .89-1.45Zm-12.055-3L6.621 18h10.764l-1.22-2.45a1 1 0 0 0-.9-.55H8.741a1 1 0 0 0-.9.55Z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-denezhnyi-vozvrat {
        background-color: rgba(147, 33, 33, 1);
        border-color: rgba(147, 33, 33, 1)!important;
        color: #fff;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M16.004 8A4 4 0 1 1 8 7.998 4 4 0 0 1 16.004 8Zm-2 0a2 2 0 1 0-4.001 0 2 2 0 0 0 4 0Zm5.892 10.55-1.89-3.89A3.001 3.001 0 0 0 15.263 13H8.742a3.001 3.001 0 0 0-2.691 1.66L4.11 18.55A1 1 0 0 0 5 20h14.006a1 1 0 0 0 .89-1.45Zm-12.055-3L6.621 18h10.764l-1.22-2.45a1 1 0 0 0-.9-.55H8.741a1 1 0 0 0-.9.55Z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-zadvoenie {
        background-color: rgb(0, 94, 255);
        border-color: rgb(0, 94, 255)!important;
        color: #fff;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M16.004 8A4 4 0 1 1 8 7.998 4 4 0 0 1 16.004 8Zm-2 0a2 2 0 1 0-4.001 0 2 2 0 0 0 4 0Zm5.892 10.55-1.89-3.89A3.001 3.001 0 0 0 15.263 13H8.742a3.001 3.001 0 0 0-2.691 1.66L4.11 18.55A1 1 0 0 0 5 20h14.006a1 1 0 0 0 .89-1.45Zm-12.055-3L6.621 18h10.764l-1.22-2.45a1 1 0 0 0-.9-.55H8.741a1 1 0 0 0-.9.55Z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-oshibka-na-sklade {
        background-color: rgb(255, 83, 83);
        border-color: rgb(255, 83, 83)!important;
        color: #fff;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M16.004 8A4 4 0 1 1 8 7.998 4 4 0 0 1 16.004 8Zm-2 0a2 2 0 1 0-4.001 0 2 2 0 0 0 4 0Zm5.892 10.55-1.89-3.89A3.001 3.001 0 0 0 15.263 13H8.742a3.001 3.001 0 0 0-2.691 1.66L4.11 18.55A1 1 0 0 0 5 20h14.006a1 1 0 0 0 .89-1.45Zm-12.055-3L6.621 18h10.764l-1.22-2.45a1 1 0 0 0-.9-.55H8.741a1 1 0 0 0-.9.55Z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-udalen {
        background-color: rgb(132, 83, 223);
        border-color: rgb(132, 83, 223)!important;
        color: #fff;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M16.004 8A4 4 0 1 1 8 7.998 4 4 0 0 1 16.004 8Zm-2 0a2 2 0 1 0-4.001 0 2 2 0 0 0 4 0Zm5.892 10.55-1.89-3.89A3.001 3.001 0 0 0 15.263 13H8.742a3.001 3.001 0 0 0-2.691 1.66L4.11 18.55A1 1 0 0 0 5 20h14.006a1 1 0 0 0 .89-1.45Zm-12.055-3L6.621 18h10.764l-1.22-2.45a1 1 0 0 0-.9-.55H8.741a1 1 0 0 0-.9.55Z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-nedostatochno-ostatkov {
        background-color: rgb(255, 83, 83);
        border-color: rgb(255, 83, 83)!important;
        color: #fff;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M16.004 8A4 4 0 1 1 8 7.998 4 4 0 0 1 16.004 8Zm-2 0a2 2 0 1 0-4.001 0 2 2 0 0 0 4 0Zm5.892 10.55-1.89-3.89A3.001 3.001 0 0 0 15.263 13H8.742a3.001 3.001 0 0 0-2.691 1.66L4.11 18.55A1 1 0 0 0 5 20h14.006a1 1 0 0 0 .89-1.45Zm-12.055-3L6.621 18h10.764l-1.22-2.45a1 1 0 0 0-.9-.55H8.741a1 1 0 0 0-.9.55Z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }
    .order-status-oplacheno-zamena-brak {
        background-color: rgb(188,107,1);
        border-color: rgb(188,107,1)!important;
        color: #fff;
        background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' fill='%23fff'%3E%3Cpath fill-rule='evenodd' d='M16.004 8A4 4 0 1 1 8 7.998 4 4 0 0 1 16.004 8Zm-2 0a2 2 0 1 0-4.001 0 2 2 0 0 0 4 0Zm5.892 10.55-1.89-3.89A3.001 3.001 0 0 0 15.263 13H8.742a3.001 3.001 0 0 0-2.691 1.66L4.11 18.55A1 1 0 0 0 5 20h14.006a1 1 0 0 0 .89-1.45Zm-12.055-3L6.621 18h10.764l-1.22-2.45a1 1 0 0 0-.9-.55H8.741a1 1 0 0 0-.9.55Z' clip-rule='evenodd'/%3E%3C/svg%3E");
    }

</style>

<script>

export default {
    props: {
        groupCode: String,
        statusName: String,
    },
    methods: {
        statusBgClass() {
            switch (this.groupCode) {
                case 'new':
                    return { 'order-status-new': true};
                case 'complete':
                    return { 'order-status-complete': true };
                case 'cancel':
                    return { 'order-status-cancel': true };
                case 'assembling':
                    return { 'order-status-assembling': true };
                case 'delivery':
                    return { 'order-status-delivery': true };
                case 'vozvrat':
                    return { 'order-status-vozvrat': true };
                case 'approval':
                    return { 'order-status-approval': true };
                case 'new-approval':
                    return { 'order-status-new-approval': true };
                case 'denezhnyi-vozvrat':
                    return { 'order-status-denezhnyi-vozvrat': true };
                case 'zadvoenie':
                    return { 'order-status-zadvoenie': true };
                case 'udalen':
                    return { 'order-status-udalen': true };
                case 'oshibka-na-sklade':
                    return { 'order-status-oshibka-na-sklade': true };
                case 'gruppa-dlia-testov':
                    return { 'order-status-gruppa-dlia-testov': true };
                case 'gruppa-dlia-testov-1':
                    return { 'order-status-gruppa-dlia-testov-1': true };
                case 'nedostatochno-ostatkov':
                    return { 'order-status-nedostatochno-ostatkov': true };
                case 'oplacheno-zamena-brak':
                    return { 'order-status-nedostatochno-ostatkov': true };
            }
        }
    }
}
</script>
