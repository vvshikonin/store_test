# Склад
Складское приложение разработано для внутреннего использования в компании и предназначено для учета товаров на складе, отслеживания движения товаров, организации приемки и отгрузки товаров, а также других операций, связанных с управлением складом.

## Установка и Запуск
1. Склонируйте репозиторий:
   ```bash
   git clone https://github.com/Prof-TE/store.git
   ```
2. Установите необходимые зависимости:
    ```bash
    composer install
    ```
    ```bash
    npm install
    ```
3. Произведите первоначальную настройку:
    ```bash
    cp .env.example .env
    ```
4. Запустите приложение:
    ```bash
    php artisan serve
    ```