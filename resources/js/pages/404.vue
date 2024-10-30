<template>

    <div class="d-flex justify-content-center h-100 align-items-center mt-5 pt-5 flex-column w-100">
        <h1 class="m-5" style="font-size: 200px; color:#dadce0;">{{ this.randomKaomoji() }}</h1>
        <div class="m-5">
            <h2 class="text-muted opacity-75 text-center">404 - Страница не найдена</h2>
            <span class="fw-bold text-muted opacity-75 text-center">Проверьте адрес страницы и попробуйте ещё раз.</span>
        </div>
        <div>
            <a href="/#/" class="btn btn-outline-primary border-0 m-2">
                Вернуться на главную
            </a>
        </div>
    </div>

</template>