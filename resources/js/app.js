require('./bootstrap');


// Импорт библиотек
import { createApp } from 'vue';
import { library } from '@fortawesome/fontawesome-svg-core'
import { faSort, faSortUp, faSortDown, faXmark, faCheck, faPlus, faMinus, faAngleLeft, faAngleRight, faChevronUp, faChevronRight, faChevronDown, faFileInvoice, faBoxesStacked,
    faDolly, faImage, faBoxesPacking, faArrowsSpin, faRotateLeft, faWindowRestore, faRightFromBracket, faLock, faBoxOpen, faCartShopping, faFilter, faTrash,
    faCheckDouble, faCircleChevronRight, faGear, faFileContract, faClipboard, faArrowLeft, faRotate, faTriangleExclamation, faListCheck, faList, faTableList, faTableColumns, faWarehouse, faStore,
    faXmarksLines, faDownload,faTag, faBox, faPen, faClock, faArrowsLeftRight, faEquals, faNotEqual, faTruckFast, faLightbulb, faCirclePlus,faMagnifyingGlass,
    faCheckCircle, faTimesCircle, faInfoCircle, faUser, faUsers, faUserTie, faBan, faCodeMerge, faPlay, faCircle, faSquare, faBars, faMoneyCheckDollar, faCashRegister,
    faFileCircleXmark, faFile, faArrowRotateRight, faPaperclip, faFloppyDisk, faBagShopping, faFileImport, faFileCsv, faMoneyBill
} from '@fortawesome/free-solid-svg-icons'
import {
     faSquarePlus,  faPenToSquare, faCreditCard, faCircleQuestion, faCircleXmark, faClock as faClockRegular, faCopy
} from '@fortawesome/free-regular-svg-icons'
import { FontAwesomeIcon, FontAwesomeLayers, FontAwesomeLayersText  } from '@fortawesome/vue-fontawesome'

// Импорт хранилища
import { store } from './store/store'

// Импорт роутов
import { router } from './routes'

// Импорт компонентов
import App from './components/app.vue';

library.add(faSort, faSortUp, faSortDown, faXmark, faCheck, faPlus, faMinus, faAngleLeft, faAngleRight, faChevronUp, faChevronRight, faChevronDown, faFileInvoice, faBoxesStacked,
    faDolly, faImage, faBoxesPacking, faCreditCard, faCartShopping, faArrowsSpin, faRotateLeft, faWindowRestore, faRightFromBracket, faLock, faBoxOpen, faFilter,
    faSquarePlus, faClock, faPenToSquare, faTrash, faCheckDouble, faCircleChevronRight, faGear, faFileContract, faClipboard, faArrowLeft, faRotate,
    faTriangleExclamation, faListCheck, faList, faTableList, faTableColumns, faWarehouse, faStore, faXmarksLines, faDownload, faTag, faCircleQuestion, faBox, faTag, faCircleXmark, faPen,
    faArrowsLeftRight, faEquals, faNotEqual, faClockRegular, faTruckFast, faMagnifyingGlass, faLightbulb, faCirclePlus, faCheckCircle, faTimesCircle, faInfoCircle,
    faCopy, faUser, faUsers, faBan, faCodeMerge, faPlay, faCircle, faSquare, faBars, faMoneyCheckDollar, faUserTie, faCashRegister, faFileCircleXmark, faFile,
    faArrowRotateRight, faPaperclip, faFloppyDisk, faBagShopping, faFileImport, faFileCsv, faMoneyBill
)

import * as prototype from './utils/prototypes';
import globalMixins from './utils/globalMixins';

// Применение прототипов
prototype

// Инициализация приложения
const app = createApp(App);

app
.use(router)
.use(store)
.component("font-awesome-icon", FontAwesomeIcon)
.component('font-awesome-layers', FontAwesomeLayers)
.component('font-awesome-layer-text', FontAwesomeLayersText)
.mixin(globalMixins)
.mount('#app');


// включение реактивности в Injection
app.config.unwrapInjectedRef = true;
