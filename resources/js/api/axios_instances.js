import axios from 'axios';
import { store } from '../store/store';
import { router } from '../routes';

/**
 * loginAPIInstance - axios инстанис используемый для авторизации
 * defaultAPIInstance - axios инстанс используемый для всех запросов требующих авторизации
 */

const loginConfig = {
    headers: {
        'Content-Type': 'application/json'
    }
}
const registerConfig = {
    headers: {
        'Content-Type': 'application/json'
    }
}
export const loginAPIInstance = axios.create(loginConfig);

const defaultConfig = {
    headers: {
        'Content-Type': 'application/json',
        'authorization': null,
    }
}

// Добавление JWT токена во все заголовки запроса через конфиг
const token = localStorage.getItem('token');
if (token) {
    defaultConfig.headers['authorization'] = `Bearer ${token}`;
}
export const defaultAPIInstance = axios.create(defaultConfig);
defaultAPIInstance.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {

            let message = "Произошла критическая ошибка.";
            let title = "Ошибка сервера: " + error.response.status + " " + error.response.statusText;

            if (error.response.status == 401) {
                message = 'Пользователь не авторизован.'
            }

            if (error.response.status == 403) {
                store.dispatch('loadUserData');
                title = "Ошибка 403. Запрещено."
                message = 'Недостаточно прав для совершения этого действия. Обратитесь к администратору.'
            }

            if (error.response.status == 418) {
                message = 'Невозможно приготовить кофе - сервер является чайником!'
            }

            if (error.response.status == 429) {
                message = 'Слишком много запросов. Попробуйте позже.'
            }

            if (error.response.status == 422) {
                title = "Ошибка 422. Некорректные данные."
                message = ""
                if (error.response.data.hasOwnProperty('errors')) {
                    const errors = error.response.data.errors;
                    for (let key in errors) {
                        if (errors.hasOwnProperty(key)) {
                            const errorMessages = errors[key];
                            errorMessages.forEach(errorMessage => {
                                message += errorMessage + "\n\n";
                            });
                        }
                    }
                    message = message.trim();
                } else {
                    message = error.response.data.message;
                }

            }
            if (error.response.status == 500) {
                title = "Ошибка 500. Внутренняя ошибка."
                message = 'Критическая ошибка сервера. Обратитесь к разработчику.'
            }

            store.dispatch('addNotification', {
                isShow: true,
                title,
                message,
                type: 'danger'
            })

            // setTimeout(() => {
            //     router.go(0);
            // }, 3000);
        }
        return Promise.reject(error);
    }
);
