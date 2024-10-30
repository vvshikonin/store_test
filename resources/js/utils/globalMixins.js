import { mapGetters } from 'vuex';
import moment from 'moment';

export default {
    methods: {
        removeItemFromArray: function (array, value) {
            const index = array.indexOf(value);
            if (index > -1) {
                array.splice(index, 1);
            }
            return array;
        },
        makeFormData(data, isPutMethod) {
            const formData = new FormData();
            for (const key in data) {
                if (Array.isArray(data[key])) {
                    data[key].forEach((item, index) => {
                        if (typeof item === 'object') {
                            Object.keys(item).forEach(itemKey => {
                                formData.append(`${key}[${index}][${itemKey}]`, item[itemKey]);
                            });
                        } else {
                            formData.append(`${key}[${index}]`, item);
                        }

                    });
                } else{
                    formData.append(key, data[key]);
                }
            }

            if (isPutMethod) formData.append('_method', 'put');

            return formData;
        },
        showToast(title, message, type = null) {
            const toast = {
                isShow: true,
                title: title,
                message: message,
                type: type
            };
            this.$store.dispatch('addNotification', toast);
        },
        randomKaomoji() {
            let kaomoji_array = [
                '¯\\_(ツ)_/¯',
                '(≥o≤)',
                "(='X'=)",
                '(·_·)',
                '(o^^)o',
                "\\(^Д^)/",
                '(;-;)',
                '(^-^*)',
                '(>_<)',
                '(·.·)',
                '(;-;)',
                '\\(o_o)/',
                'ಠ﹏ಠ'
            ];
            return kaomoji_array[Math.floor(Math.random() * kaomoji_array.length)];
        },
        checkPermission(permission) {
            return this.permissions.includes(permission);
        },
        moment(date, format = 'DD.MM.YYYY') {
            return date ? moment(date).format(format) : null;
        },
        /**
         * Форматирует дату.
         *
         * @param {string} date
         * @param {string} format
         * @returns {string}
         */
        formatDate(date, format) {
            return this.moment(date, format) || '—';
        },
    },
    computed: {
        ...mapGetters({ permissions: 'getUserPermissions' })
    }
}
