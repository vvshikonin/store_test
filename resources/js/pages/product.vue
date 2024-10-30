<template>
    <EntityLayout :loadingCover="isCover" :CancelDisabled="!isLoaded"
        :entityName="'товар &quot;' + product.name + '&quot;'" :isLoaded="isLoaded" :withSaveButton="canUserEdit"
        :withDeleteButton="canUserDelete" @save="sendSaveRequest()" @exit="onExit()" @destroy="destroyProduct()">
        <template v-slot:header>
            <div class="bg-primary bg-gradient shadow-sm text-white rounded w-100 p-3">
                <div>
                    <h3 v-if="isEditing">Товар "<strong>{{ product.name }}</strong>"</h3>
                    <h3 v-else class="ms-3">Создание нового товара</h3>
                    <p v-if="isDirty" style="color:red; font-size:16px">*</p>
                </div>
                <div>
                    <div v-if="product.creator?.name" class="d-inline me-1">
                        <small>Создал:</small>
                        <strong class="ps-1">{{ product.creator.name }}</strong>
                    </div>
                    <div v-if="product.created_at" class="d-inline me-1">
                        <small>Создан:</small>
                        <strong class="ps-1">{{ formatDate(product?.created_at, 'DD.MM.YYYY HH:mm:ss') }}</strong>
                    </div>
                    <div v-if="product.updater?.name" class="d-inline me-1">
                        <small>Изменил:</small>
                        <strong class="ps-1">{{ product.updater.name }}</strong>
                    </div>
                    <div v-if="product?.updated_at" class="d-inline me-1">
                        <small>Изменён:</small>
                        <strong class="ps-1">{{ formatDate(product?.updated_at, 'DD.MM.YYYY HH:mm:ss') }}</strong>
                    </div>
                </div>
            </div>
        </template>
        <template v-slot:content>
            <Card title="Сведения о товаре">
                <inputDefault label="Название товара" placeholder="Введите название" v-model="product.name"
                    :required="true" :disabled="!canUserEdit" />
                <Selector required v-if="isBrandsLoaded" label="Бренд" :options="brands" v-model="product.brand_id"
                    :disabled="!canUserEdit" :placeholder="'Бренд не выбран'" />
                <inputDefault type="number" min="0" label="Поддерживаемый остаток" v-model="product.maintained_balance"
                    :disabled="!canUserEdit" />
                <inputDefault v-if="isEditing" label="Поставщики" placeholder="Поставщики не найдены"
                    v-model="product.contractor_names" :disabled="true" />
                <inputDefault label="Комментарий для товара" :disabled="!canUserEdit" v-model="product.user_comment" />
                <inputDefault type="number" min="0" label="РРЦ" :disabled="!canUserEdit" v-model="product.rrp" />
            </Card>
            <div class="d-flex flex-row justify-content-between">
                <Card title="Список артикулов товара" class="w-75 me-3">
                    <div class="p-3 w-100 border-bottom d-flex justify-content-end">
                        <button style="font-size: 14px;" type="button"
                            v-if="isEditing && checkPermission('product_merge')" @click="openWantToMergeModal()"
                            class="ms-3 btn btn-primary">
                            <font-awesome-icon icon="fa-solid fa-code-merge" size="lg" /> Слияние товаров </button>
                        <AddButton v-if="canUserEdit" @on_click="newSkuMenuOpened = true" label="Добавить артикул">
                        </AddButton>
                    </div>
                    <div class="d-flex flex-row justify-content-between w-100">
                        <MainTable :tableSettings="skuTablesSettings"
                            class=" border-bottom-0 border-top-0 border-end-0 rounded-0 w-100"
                            style="border-left: 0px solid!important; border-bottom-right-radius: 0px!important;">
                            <template v-slot:thead>
                                <template v-if="product.sku_list.length">
                                    <HeaderCell style="border-top: 0px solid!important;">Артикулы</HeaderCell>
                                    <HeaderCell align="center" style="border-top: 0px solid!important;">Основной
                                    </HeaderCell>
                                    <HeaderCell align="center" style="border-top: 0px solid!important;"></HeaderCell>
                                </template>
                            </template>
                            <template v-slot:tbody>
                                <TableRow v-for="(sku, index) in product.sku_list" :key="sku">
                                    <TableCell>{{ product.sku_list[index] }}</TableCell>
                                    <TableCell align="center">
                                        <input type="radio" name="main_sku" :value="sku" v-model="product.main_sku"
                                            :disabled="!canUserEdit">
                                    </TableCell>
                                    <TableCell align="right" width="80">
                                        <TrashButton v-if="canUserEdit && product.sku_list.length >= 2"
                                            @on_click="deleteSkuFromList(index)"
                                            :isAlowed="product.sku_list.length >= 2">
                                        </TrashButton>
                                    </TableCell>
                                </TableRow>
                                <!-- <TableRow v-if="!product.sku_list.length">
                                    <TableCell align="center">
                                        <button @click="newSkuMenuOpened = true" class="btn btn-outline-primary border-0"
                                            style="font-size: 14px;" type="button">Добавить артикул</button>
                                    </TableCell>
                                </TableRow> -->
                            </template>
                        </MainTable>
                    </div>
                </Card>
                <Card v-if="isEditing" title="Статистика по товару" class="w-25" style="min-height: 290px;">
                    <div v-if="isEditing" class="d-flex">
                        <div class="d-flex flex-row-reverse">
                            <div class="d-flex flex-column">
                                <span class="ps-1 mt-3" style="font-size: 14px;">{{ totalStoreRealStock }}</span>
                                <span class="ps-1" style="font-size: 14px;">{{ totalOrderReserved }}</span>
                                <span class="ps-1" style="font-size: 14px;">{{ totalFreeStock }}</span>
                                <span class="ps-1" style="font-size: 14px;">{{ totalInvoiceWaitingAmount }}</span>
                                <span class="ps-1" style="font-size: 14px;">{{ totalStoreSaledAmount }}</span>
                                <span v-if="product.avg_price" class="ps-1" style="font-size: 14px;">{{ product.avg_price.priceFormat(true) }}</span>
                                <span v-if="minPrice > 0 && minPrice != maxPrice && minPrice !== Infinity" class="ps-1"
                                    style="font-size: 14px;">{{ minPrice.priceFormat(true) }}</span>
                                <span v-if="maxPrice > 0 && minPrice != maxPrice" class="ps-1 mb-3"
                                    style="font-size: 14px;">{{
                                    maxPrice.priceFormat(true) }}</span>
                                <span v-if="minPrice == maxPrice" class="ps-1 mb-3" style="font-size: 14px;">{{
                                    minPrice.priceFormat(true) }}</span>
                            </div>
                            <div class="d-flex flex-column ps-4 me-4">
                                <span class="text-muted ps-1 mt-3" style="font-size: 14px;">Всего осталось на
                                    складе:</span>
                                <span class="text-muted ps-1" style="font-size: 14px;">Зарезервированно:</span>
                                <span class="text-muted ps-1" style="font-size: 14px;">Свободный остаток:</span>
                                <span class="text-muted ps-1" style="font-size: 14px;">Всего ожидается:</span>
                                <span class="text-muted ps-1" style="font-size: 14px;">Всего продано:</span>
                                <span v-if="product.avg_price" class="text-muted ps-1" style="font-size: 14px;">Средневзвешенная цена:</span>
                                <span v-if="minPrice > 0 && minPrice != maxPrice && minPrice !== Infinity"
                                    class="text-muted ps-1" style="font-size: 14px;">Минимальная закупочная цена:</span>
                                <span v-if="maxPrice > 0 && minPrice != maxPrice" class="text-muted ps-1 mb-3"
                                    style="font-size: 14px;">Максимальная закупочная цена:</span>
                                <span v-if="minPrice == maxPrice" class="text-muted ps-1 mb-3"
                                    style="font-size: 14px;">Закупочная
                                    цена:</span>
                            </div>
                        </div>
                    </div>
                    <button style="font-size: 14px;" type="button" v-if="isEditing && canUserCorrect"
                        @click="openCorrectModal()" class="ms-4 mt-3 btn btn-primary">
                        <font-awesome-icon icon="fa-regular fa-pen-to-square" size="lg" /> Скорректировать остаток
                    </button>
                </Card>
            </div>
            <div v-if="isEditing" class="d-flex flex-row justify-content-between">
                <Card title="Остатки по поставщикам" class="w-50">
                    <div class="custom-control custom-checkbox ml-3">
                        <label class="custom-control-label ps-3 pe-1" for="hasRealStockCheckbox">Скрыть позиции без
                            остатка</label>
                        <input type="checkbox" class="custom-control-input" id="hasRealStockCheckbox"
                            v-model="hasRealStock">
                    </div>
                    <div class="d-flex flex-row justify-content-between w-100">
                        <MainTable :tableSettings="stocksTablesSettings"
                            class="border-bottom-0 border-top-0 border-end-0 rounded-0 w-100"
                            style="border-left: 0px solid!important; border-bottom-right-radius: 0px!important;">
                            <template v-slot:thead>
                                <HeaderCell align="left" style="border-top: 0px solid!important;">Поставщик</HeaderCell>
                                <HeaderCell align="left" style="border-top: 0px solid!important;">Комментарий
                                </HeaderCell>
                                <HeaderCell align="center" style="border-top: 0px solid!important;">Остаток</HeaderCell>
                                <HeaderCell align="center" style="border-top: 0px solid!important;">Ожидается
                                </HeaderCell>
                                <HeaderCell align="center" style="border-top: 0px solid!important;">Закупочная цена
                                </HeaderCell>
                                <HeaderCell align="center" style="border-top: 0px solid!important;">Выгодно купили
                                </HeaderCell>
                            </template>
                            <template v-slot:tbody>
                                <TableRow v-for="stock in filteredStorePositions" :key="stock.sku">
                                    <TableCell align="left"> {{ stock.contractor_name }} </TableCell>
                                    <TableCell>
                                        <TextAreaPopup width="140px" v-model="stock.user_comment" :disabled="!canUserEdit"
                                            @change="onChangeStock(stock)" placeholder="Добавить комментарий">
                                        </TextAreaPopup>
                                    </TableCell>
                                    <TableCell align="center"> {{ stock.real_stock }} </TableCell>
                                    <TableCell align="center"> {{ stock.expected }} </TableCell>
                                    <TableCell align="center"> {{ stock.price.priceFormat(true) }} </TableCell>
                                    <TableCell align="center"> 
                                        <input :disabled="!canUserChangeIsProfitablePurchase" type="checkbox" v-model="stock.is_profitable_purchase" 
                                            @change="onChangeStock(stock)">
                                    </TableCell>
                                </TableRow>
                            </template>
                        </MainTable>
                    </div>
                </Card>
                <Card title="Управление распродажей" class="w-25" style="margin-left: 20px;">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center m-2">
                            <label for="saleType" class="text-muted ps-1 text-start" style="font-size: 14px;">Тип
                                распродажи:</label>
                            <select id="saleType" v-model="product.sale_type" class="form-control">
                                <option value="auto">Автоматически</option>
                                <option :disabled="!product.avg_price" value="multiplier">Фиксированный множитель</option>
                                <option value="nonsale">Без распродажи</option>
                            </select>
                        </div>
                        <!-- <div class="d-flex align-items-center m-2">
                            <label for="fixedPrice" class="text-muted ps-1 text-start"
                                style="font-size: 14px;">Фиксированная
                                продажная цена:</label>
                            <input type="number" id="fixedPrice" v-model="fixedPrice" :disabled="saleType === 'auto'"
                                class="form-control">
                        </div> -->
                        <div class="d-flex flex-column align-items-start m-2">
                            <label for="multiplier" class="text-muted ps-1 text-start mb-2"
                                style="font-size: 14px;">Фиксированный множитель
                                наценки:</label>
                            <input type="number" id="multiplier" step="0.01" v-model="product.sale_multiplier" :disabled="product.sale_type !== 'multiplier'"
                                class="form-control mb-2">
                            <!-- Формула подсчёта цены avg_price * на множитель = продажная цена-->
                            <span v-if="product.avg_price && product.sale_type === 'multiplier'" class="text-muted ps-1 text-start" style="font-size: 14px;">
                                {{ product.avg_price.priceFormat(true) }} * {{ product.sale_multiplier }} =
                                {{ (product.avg_price * product.sale_multiplier).priceFormat(true) }}
                            </span>
                        </div>
                        <!-- <div class="d-flex align-items-center m-2">
                            <label for="markup" class="text-muted ps-1 text-start"
                                style="font-size: 14px;">Фиксированный процент
                                наценки:</label>
                            <input type="number" id="markup" v-model="markup" :disabled="saleType === 'auto'"
                                class="form-control">
                        </div> -->
                        <!-- <div class="d-flex align-items-center m-2">
                            <label for="strikethrough_multiplier" class="text-muted ps-1 pe-1 text-start"
                                style="font-size: 14px;">Множитель зачёркнутой цены:</label>
                            <input type="number" id="strikethrough_multiplier" step="0.01" v-model="product.strikethrough_multiplier" :disabled="product.sale_type !== 'multiplier'"
                                class="form-control">
                        </div> -->
                        <div class="d-flex align-items-center m-2">
                            <label for="is_sale" class="text-muted ps-1 pe-1 text-start"
                                style="font-size: 14px;">Распродажа активна:</label>
                            <TableCheckbox id="is_sale" :disabled="true" v-model="product.is_sale"></TableCheckbox>
                        </div>
                    </div>
                </Card>
                <Card title="История распродаж" class="w-25" style="margin-left: 20px;">
                    <div class="w-100">
                        <MainTable :tableSettings="saleHistoryTablesSettings"
                            class="border-bottom-0 border-top-0 border-end-0 rounded-0 w-100"
                            style="border-left: 0px solid!important; border-radius: 0px!important; max-height: 320px; overflow-y: auto;">
                            <template v-slot:thead>
                                <HeaderCell align="center" style="border-top: 0px solid!important;">Цена продажи</HeaderCell>
                                <HeaderCell align="center" style="border-top: 0px solid!important;">Цена без скидки (зачеркнутая)</HeaderCell>
                                <HeaderCell align="center" style="border-top: 0px solid!important;">Дата начала</HeaderCell>
                                <HeaderCell align="center" style="border-top: 0px solid!important;">Дата окончания</HeaderCell>
                            </template>
                            <template v-slot:tbody>
                                <TableRow v-for="record in product.sale_history.slice().reverse()" :key="record">
                                    <TableCell align="center"> {{ record.sale_price.priceFormat(true) }} </TableCell>
                                    <TableCell align="center"> {{ (record.sale_price / 0.85).priceFormat(true) }} </TableCell>
                                    <TableCell align="center"> {{ formatDate(record.created_at) }} </TableCell>
                                    <TableCell align="center"> {{ formatDate(record.end_date) }} </TableCell>
                                </TableRow>
                            </template>
                        </MainTable>
                    </div>
                </Card>
            </div>
            <div class="d-flex flex-row justify-content-between">
                <Card title="Счета с этим товаром" v-if="isEditing && checkPermission('invoice_read')"
                    class="w-50 me-3">
                    <div class="d-flex flex-row" v-if="product.invoices.length !== 0">
                        <FilterInput v-model="invoiceFilter" placeholder="Номер счета" class="m-2"
                            style="min-width: fit-content;">
                        </FilterInput>
                        <FilterSelect v-model="invoiceStatusFilter" placeholder="Статус счета" class="m-2"
                            style="min-width: fit-content;" :options="invoiceStatusOptions"></FilterSelect>
                    </div>
                    <MainTable :tableSettings="tablesSettings"
                        class="border-start-0 border-top border-end-0 border-bottom-0 w-100"
                        v-if="product.invoices.length !== 0" style="max-height: 500px; overflow-y: auto;">
                        <template v-slot:thead>
                            <HeaderCell style="width: 50px !important; border-top: 0px solid !important;">Номер счёта
                            </HeaderCell>
                            <HeaderCell style="border-top: 0px solid !important;" align="center">Поставщик</HeaderCell>
                            <HeaderCell style="width: 50px !important; border-top: 0px solid !important;"
                                align="center"> Кол-во</HeaderCell>
                            <HeaderCell style="width: 50px !important; border-top: 0px solid !important;"
                                align="center"> Опр-но</HeaderCell>
                            <HeaderCell style="width: 50px !important; border-top: 0px solid !important;"
                                align="center"> Отказ</HeaderCell>
                            <HeaderCell style="border-top: 0px solid !important;" align="center">Цена за шт.
                            </HeaderCell>
                            <HeaderCell style="border-top: 0px solid !important;" align="center">Создан</HeaderCell>
                            <HeaderCell style="border-top: 0px solid !important;">Статус</HeaderCell>
                        </template>
                        <template v-slot:tbody>
                            <TableRow v-for="invoice in filteredInvoices" :key="invoice"
                                @click_row="openInvoice(invoice.id)">
                                <TableCell style="width: 50px !important;">
                                    <a :href="'#/invoices/' + invoice.id + '/edit'">{{ invoice.number }}</a>
                                </TableCell>
                                <TableCell align="center">{{ invoice.contractor_name }}</TableCell>
                                <TableCell style="width: 50px !important;" align="center">{{ invoice.amount }}
                                </TableCell>
                                <TableCell style="width: 50px !important;" align="center">{{ invoice.received }}
                                </TableCell>
                                <TableCell style="width: 50px !important;" align="center">{{ invoice.refused }}
                                </TableCell>
                                <TableCell align="center">{{ invoice.price.priceFormat(true) }}</TableCell>
                                <TableCell align="center">{{ formatDate(invoice.created_at) }}</TableCell>
                                <TableCell>
                                    <InvoiceStatus :fullsize="false" :statusCode="invoice.status"></InvoiceStatus>
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-slot:tfoot>
                            <span>Всего в счетах: {{ totalInvoiceAmount }}</span>
                            <span class="ps-5">Оприходовано: {{ totalInvoiceCredited }}</span>
                            <span class="ps-5">Ожидается: {{ totalInvoiceWaitingAmount }}</span>
                            <span class="ps-5">Отказов: {{ totalRefundCount }}</span>
                        </template>
                    </MainTable>
                    <div class="text-muted mt-5 d-flex justify-content-center align-items-center w-100 h-100" v-else
                        style="min-width: 740px !important;"> Нет счетов с этим товаром </div>
                </Card>
                <Card v-if="isEditing" title="Заказы с этим товаром" class="w-50">
                    <div class="d-flex flex-row" v-if="product.orderPositions.length !== 0">
                        <FilterInput v-model="orderFilter" placeholder="Номер заказа или CRM ID" class="m-2"
                            style="min-width: fit-content;"></FilterInput>
                        <FilterSelect v-model="orderStatusFilter" placeholder="Статус заказа" class="m-2"
                            style="min-width: fit-content;" :options="orderStatusOptions"></FilterSelect>
                    </div>
                    <MainTable :tableSettings="tablesSettings"
                        class="border-start-0 border-top border-end-0 border-bottom-0 w-100"
                        v-if="product.orderPositions.length !== 0" style="max-height: 500px; overflow-y: auto;">
                        <template v-slot:thead>
                            <HeaderCell style="border-top: 0px solid !important; height: 53px!important;">Номер заказа
                            </HeaderCell>
                            <HeaderCell style="border-top: 0px solid !important;" align="center">Кол-во </HeaderCell>
                            <HeaderCell style="border-top: 0px solid !important;" align="center">Поставщик </HeaderCell>
                            <HeaderCell style="border-top: 0px solid !important;" align="center">Статус заказа
                            </HeaderCell>
                            <HeaderCell style="border-top: 0px solid !important;" align="center">Состояние</HeaderCell>
                        </template>
                        <template v-slot:tbody>
                            <TableRow v-for="order in filteredOrders" :key="order">
                                <TableCell>
                                    <a :href="'https://babylissrus.retailcrm.ru/orders/' + order.crm_id + '/edit'"
                                        target="_blank"> {{ order.number }} </a>
                                </TableCell>
                                <TableCell align="center">{{ order.amount }}</TableCell>
                                <TableCell align="center">{{ order.contractor_name }}</TableCell>
                                <TableCell align="center">
                                    <OrderStatus :statusName="order.order_status" :groupCode="order.group_code">
                                    </OrderStatus>
                                </TableCell>
                                <TableCell align="center">{{ orderState(order.state) }}</TableCell>
                            </TableRow>
                        </template>
                        <template v-slot:tfoot>
                            <span>Всего в заказах: {{ totalOrderAmount }}</span>
                            <span v-if="totalOrderReserved" class="ps-4">Резерв: {{ totalOrderReserved }}</span>
                            <span v-if="totalOrderClosed" class="ps-4">Отгружено: {{ totalOrderClosed }}</span>
                            <span v-if="totalOrderCanceled" class="ps-4">Отменено: {{ totalOrderCanceled }}</span>
                            <span v-if="totalOrderRefund" class="ps-4">Возвратов: {{ totalOrderRefund }}</span>
                            <span v-if="totalOrderDefect" class="ps-4">Браков: {{ totalOrderDefect }}</span>
                            <span v-if="totalOrderUnknown" class="ps-4">Неизвестно: {{ totalOrderUnknown }}</span>
                        </template>
                    </MainTable>
                    <div class="text-muted mt-5 d-flex justify-content-center align-items-center w-100 h-100" v-else
                        style="min-width: 740px;"> Нет заказов с этим товаром </div>
                </Card>
            </div>
            <Toasters :toasts="toasts"></Toasters>
            <DefaultModal @close_modal="mergeWithProduct.isNeedToMerge = false" v-if="mergeWithProduct.isNeedToMerge"
                title="Вы хотите сделать слияние двух товаров?">
                <div class=" p-2">
                    <p v-if="mergeWithProduct.isWantToMerge">Вы выбрали товар с которым хотите провести слияние.
                        Убедитесь в корректности данных.</p>
                    <p v-else>Найден другой товар с артикулом, который Вы попытались добавить в список вариантов
                        артикулов. Хотите сделать слияние двух товаров?</p>
                    <p>После завершения слияния оба товара будут удалены. Вместо них появится третий товар со всеми
                        данными из первых двух.</p>
                    <p v-if="mergeWithProduct.isWantToMerge">Вот некоторые сведения о выбранном товаре:</p>
                    <p v-else>Вот некоторые сведения о найденном товаре с таким же артикулом:</p>
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Название:</td>
                                <td>{{ mergeWithProduct.name }}</td>
                            </tr>
                            <tr>
                                <td>Основной артикул:</td>
                                <td>{{ mergeWithProduct.main_sku }}</td>
                            </tr>
                            <tr v-if="mergeWithProduct.sku_list.length > 1">
                                <td>Варианты артикулов:</td>
                                <td>{{ mergeWithProduct.sku_list }}</td>
                            </tr>
                            <tr v-if="mergeWithProduct.brand_id">
                                <td>Бренд:</td>
                                <td>{{ mergeWithProduct.brand_name }}</td>
                            </tr>
                            <tr v-if="mergeWithProduct.maintained_balance">
                                <td>Поддерживаемый остаток:</td>
                                <td>{{ mergeWithProduct.maintained_balance }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer div d-flex justify-content-between shadow-lg pt-2">
                    <button type="button" :disabled="isCover" class="btn bg-gradient btn-light border ms-2 me-2"
                        @click="mergeWithProduct.isNeedToMerge = false">Отмена</button>
                    <button type="button" :disabled="isCover" class="btn btn-outline-primary ms-2 me-2"
                        @click="isShowProductSelect = true, mergeWithProduct.isNeedToMerge = false">Выбрать другой
                        товар</button>
                    <button type="button" :disabled="isCover" class="btn btn-outline-primary ms-2 me-2"
                        @click="showDublicateProduct()">Открыть выбранный товар</button>
                    <button type="button" :disabled="isCover" class="btn btn-primary ms-2 me-2"
                        @click="sendMergeRequest()">Провести
                        слияние</button>
                    <i class="ms-2 mt-2 pb-2">Вы сможете внести изменения в готовый товар после процедуры слияния, в
                        случае необходимости.</i>
                </div>
            </DefaultModal>
            <DefaultModal :width="'500px'" v-if="newSkuMenuOpened" @close_modal="newSkuMenuOpened = false"
                title="Добавить новый вариант артикула">
                <div class="p-3">
                    <input v-model="new_sku" type="text" class="form-control" style="height: 30px; font-size: 13px;"
                        placeholder="Артикул">
                </div>
                <div class="d-flex justify-content-end p-2 mb-0 bg-light">
                    <button class="btn bg-gradient btn-light border m-1" :disabled="isCover"
                        @click="newSkuMenuOpened = false">Отмена</button>
                    <button type="button" class="btn btn-primary m-1" :disabled="!new_sku || isCover"
                        @click="addNewSkuListRow(new_sku)">Добавить</button>
                </div>
            </DefaultModal>
            <DefaultModal :width="'700px'" v-if="isCorrcectModalOpened" title="Скорректировать остаток"
                @close_modal="closeCorrectModal()">
                <div class="d-flex flex-column justify-content-between" style="max-height: 500px; overflow-y: auto;">
                    <span>Товар {{ product.name }}</span>
                    <MainTable>
                        <template v-slot:thead>
                            <HeaderCell>Поставщик</HeaderCell>
                            <HeaderCell align="center">Закупочная цена</HeaderCell>
                            <HeaderCell align="center">Старый остаток</HeaderCell>
                            <HeaderCell>Новый остаток</HeaderCell>
                        </template>
                        <template v-slot:tbody>
                            <TableRow v-for="stock in product.storePositions" :key="stock">
                                <TableCell> {{ stock.contractor_name }} </TableCell>
                                <TableCell align="center"> {{ stock.price.priceFormat(true) }} </TableCell>
                                <TableCell align="center"> {{ stock.real_stock }} </TableCell>
                                <TableCell align="center">
                                    <input class="form-control" style="font-size: 13px; width: 70px;" type="number"
                                        step="1" :min="0" v-model="stock.new_real_stock">
                                </TableCell>
                            </TableRow>
                        </template>
                    </MainTable>
                </div>
                <div class="d-flex justify-content-end p-2 mb-0 bg-white">
                    <button type="button" class="btn btn-primary m-1" @click="onCorrect()">Внести корректировку</button>
                </div>
            </DefaultModal>
            <ProductModal v-if="isShowProductSelect" @close_modal="isShowProductSelect = false"
                @select_product="onSelectProduct($event)">
            </ProductModal>
            <Card v-if="isEditing" title="История транзакций">
                <MainTable @export="onTransactionsExport()" class="border-start-0 border-end-0 border-bottom-0 w-100"
                    :tableSettings="transactionsTablesSettings">
                    <template v-slot:thead>
                        <HeaderCell class="border-top-0" title="В какой момент была произведена транзакция">Дата и время
                        </HeaderCell>
                        <HeaderCell class="border-top-0" title="Где была произведена транзакция">Источник</HeaderCell>
                        <HeaderCell class="border-top-0" align="center" title="Кто произвёл транзакцию">Пользователь
                        </HeaderCell>
                        <HeaderCell class="border-top-0" align="center"
                            title="На сколько изменился остаток у позиции товара после проведения транзакции">Количество
                        </HeaderCell>
                        <HeaderCell class="border-top-0" align="center"
                            title="Общий остаток товара на момент после проведения транзакции"> Остаток у товара
                        </HeaderCell>
                        <HeaderCell class="border-top-0" align="center"
                            title="Остаток у товарной позиции на момент после проведения транзакции"> Остаток у позиции
                        </HeaderCell>
                        <HeaderCell class="border-top-0" align="center" title="Поставщик позиции товара">Поставщик
                        </HeaderCell>
                        <HeaderCell class="border-top-0" title="Закупочная цена позиции товара">Цена</HeaderCell>
                    </template>
                    <template v-slot:tbody>
                        <TableRow v-for="transaction in productTransactions" :key="transaction">
                            <TableCell>{{ formatDate(transaction.created_at, "DD.MM.YYYY HH:mm:ss") }}</TableCell>
                            <TableCell>
                                <ProductTransactionLink :transaction="transaction" />
                            </TableCell>
                            <TableCell align="center"
                                v-if="transaction.transactionable_type == 'App\\Models\\V1\\Stock'"> Склад</TableCell>
                            <TableCell align="center" v-else>{{ transaction.user_name }}</TableCell>
                            <TableCell align="center">{{ transactionAmountToString(transaction) }}</TableCell>
                            <TableCell align="center">{{ transaction.total_amount_at_transaction }}</TableCell>
                            <TableCell align="center">{{ transaction.amount_at_transaction }}</TableCell>
                            <TableCell align="center"> {{ transaction.stock_contractor_name || 'Поставщик неизвестен' }}
                            </TableCell>
                            <TableCell> {{ transaction.stock_price.priceFormat(true) || 'Без цены' }} </TableCell>
                        </TableRow>
                    </template>
                    <template v-slot:tfoot>
                        <span v-if="productTransactions"> Всего транзакций: {{ productTransactions.length }} </span>
                    </template>
                </MainTable>
            </Card>
        </template>
    </EntityLayout>
</template>
<script>
import { productAPI } from "../api/products_api.js";
import { brandsAPI } from "../api/brand_api.js";
import { contractorAPI } from "../api/contractor_api.js";
import { storePositionAPI } from '../api/store_positions_api';
// import routerMixin from "../utils/routerMixins.js";
import PageWrapper from "../components/Layout/page_wrapper.vue";
import MainTable from '../components/Tables/main_table.vue';
import HeaderCell from '../components/Tables/th.vue';
import TableRow from '../components/Tables/tr.vue';
import TableCell from '../components/Tables/td.vue';
import TextInput from '../components/inputs/text_input.vue';
import inputDefault from '../components/inputs/default_input.vue';
import EntityLayout from '../components/Layout/entity_edit_page.vue';
import Card from '../components/Layout/card.vue';
import InvoiceStatus from '../components/Other/invoice_status.vue';
import OrderStatus from "../components/Other/OrderStatus.vue";
import ProductTransactionLink from "../components/Other/ProductTransactionLink.vue";

import FilterInput from "../components/inputs/filter_input.vue";
import FilterSelect from "../components/inputs/filter_select_searchable.vue";

import Selector from '../components/inputs/select_input.vue';
import TextAreaPopup from '../components/popups/table_textarea_popup.vue';
import Toasters from '../components/popups/toaters.vue';
import DefaultModal from '../components/modals/default_modal.vue';
import ProductModal from '../components/modals/product_select_modal.vue';
import MultiSelect from '../components/inputs/multiselect_input.vue';
import AddButton from '../components/inputs/add_button.vue';
import TrashButton from '../components/inputs/trash_button.vue';

import MarkIcon from '../components/inputs/MarkIcon.vue';
import TableCheckbox from '../components/inputs/table_checkbox.vue'

export default {
    // mixins: [routerMixin],
    components: {
        PageWrapper,
        MainTable,
        HeaderCell,
        TableRow,
        TableCell,
        TextInput,
        inputDefault,
        EntityLayout,
        Card,
        InvoiceStatus,
        OrderStatus,
        ProductTransactionLink,
        FilterInput,
        FilterSelect,
        Selector,
        Toasters,
        DefaultModal,
        ProductModal,
        MultiSelect,
        AddButton,
        TrashButton,
        MarkIcon,
        TableCheckbox,
        TextAreaPopup
    },

    data() {
        return {
            // ID продукта, полученный из параметра маршрута
            id: this.$route.params.product_id,

            // Данные продукта, загружаемые из API
            product: {},

            //
            originalProduct: null,

            // Данные брендов, загружамеые из API
            brands: null,

            // Данные поставщиков, загружамеые из API
            contractors: null,

            //
            productTransactions: {},

            // Настройки таблицы
            tablesSettings: {
                tableTitle: null, // Заголовок таблицы
                isLoading: false, // Флаг загрузки данных
                isNoEntries: false, // Флаг отсутствия записей в таблице
                isCover: false, // Флаг использования обложки для таблицы
                withFilters: false, // Флаг использования фильтров в таблице
                withExport: false, // Флаг использования экспорта данных из таблицы
                withFooter: true,
                isStickyFooter: true
            },
            skuTablesSettings: {
                tableTitle: null, // Заголовок таблицы
                isLoading: false, // Флаг загрузки данных
                isNoEntries: false, // Флаг отсутствия записей в таблице
                isCover: false, // Флаг использования обложки для таблицы
                withFilters: false, // Флаг использования фильтров в таблице
                withExport: false, // Флаг использования экспорта данных из таблицы
                withFooter: false
            },
            stocksTablesSettings: {
                tableTitle: null, // Заголовок таблицы
                isLoading: false, // Флаг загрузки данных
                isNoEntries: false, // Флаг отсутствия записей в таблице
                isCover: false, // Флаг использования обложки для таблицы
                withFilters: false, // Флаг использования фильтров в таблице
                withExport: false, // Флаг использования экспорта данных из таблицы
                withFooter: false
            },
            saleHistoryTablesSettings: {
                tableTitle: null,
                isLoading: false,
                isNoEntries: false,
                isCover: false,
                withFilters: false,
                withExport: false,
                withFooter: false
            },
            transactionsTablesSettings: {
                tableTitle: null, // Заголовок таблицы
                isLoading: true, // Флаг загрузки данных
                isNoEntries: false, // Флаг отсутствия записей в таблице
                isCover: false, // Флаг использования обложки для таблицы
                withFilters: false, // Флаг использования фильтров в таблице
                withExport: true, // Флаг использования экспорта данных из таблицы
                withFooter: true
            },

            // Флаг, указывающий, загружены ли данные продукта
            isLoaded: false,

            //
            isCover: false,

            // Флаг, указывающий, загружены ли данные брендов
            isBrandsLoaded: false,

            // Флаг, указывающий, загружены ли данные поставщиков
            isContractorsLoaded: false,

            // Новый SKU, добавляемый в список SKU продукта
            new_sku: null,

            // Массив используемых на странице уведомлений
            toasts: [],

            //
            mergeWithProduct: {
                isNeedToMerge: false,
                isWantToMerge: false,
                // isNeedToClarife: false,
            },

            //
            isShowProductSelect: false,

            //
            isShowDeleteModal: false,

            //
            newSkuMenuOpened: false,

            //
            isCorrcectModalOpened: false,

            //параметры фильтрации счетов
            invoiceFilter: '',
            invoiceStatusFilter: null,
            invoiceStatusOptions: [],

            //параметры фильтрации заказов
            orderFilter: '',
            orderStatusOptions: [],
            orderStatusFilter: null,

            hasRealStock: true,

            // saleType: 'auto',
        };
    },
    methods: {
        // Метод для загрузки данных продукта из API
        async loadProduct() {
            const response = await productAPI.show(this.id);
            this.product = response.data.data;
            this.fillNewRealStocks();

            this.invoiceStatusOptions = [
                { id: 0, name: 'Ожидается' },
                { id: 1, name: 'Частично оприходован' },
                { id: 2, name: 'Оприходован' },
                { id: 3, name: 'Отменён' }
            ];
            this.getUniqueOrderStatuses(this.product.orderPositions);
            this.isLoaded = true;
            this.originalProduct = JSON.parse(JSON.stringify(response.data.data));

            this.loadTransactions();
        },

        async loadTransactions() {
            const response = await productAPI.getTransactions(this.product);
            this.productTransactions = response.data;
            this.transactionsTablesSettings.isLoading = false;
            this.transactionsTablesSettings.isNoEntries = this.productTransactions.length ? false : true;
        },

        // Метод для загрузки брендов из API
        async loadBrands() {
            const response = await brandsAPI.index();
            this.brands = response.data.data;
            this.isBrandsLoaded = true;
        },

        // Метод для загрузки поставщиков из API
        async loadContractors() {
            const response = await contractorAPI.index();
            this.contractors = response.data.data;
            this.isContractorsLoaded = true;
        },
        fillNewRealStocks() {
            this.product.storePositions = this.product.storePositions.map(stock => {
                return { ...stock, new_real_stock: stock.real_stock };
            });
        },
        async onChangeStock(stock) {
            const data = await storePositionAPI.update(stock);
        },
        async sendSaveRequest() {
            this.isCover = true;
            if (!this.product.sku_list || !this.product.main_sku) {
                this.showToast('Не удалось сохранить товар', 'Укажите для товара как минимум один артикул', 'warning');
                this.isCover = false;
                return false;
            }
            if (this.isEditing) {
                const response = await productAPI.update({ product: this.product });
                this.showToast('Сохранено', 'Изменения в товаре успешно сохранены', 'success');
                this.isCover = false;
                this.originalProduct = JSON.parse(JSON.stringify(this.product))
                return true;
            } else {
                const response = await productAPI.store({ product: this.product });
                console.log(response.data);
                this.product = response.data.data;
                this.$router.push('/products/' + response.data.data.id + '/edit');
                this.originalProduct = JSON.parse(JSON.stringify(this.product))
                this.showToast('Сохранено', 'Новый товар успешно создан', 'success');
                this.isCover = false;
                this.loadTransactions();
                return true;
            }
        },

        async sendNewSkuRequest(sku) {
            this.isCover = true;

            const { data } = await productAPI.checkSku({ sku });

            console.log(data);

            if (data && data.id === this.product.id) {
                this.isCover = false;
                return null;
            } else if (data) {
                if (this.isEditing) {
                    this.mergeWithProduct = data;
                    this.showToast('Не удалось добавить артикул', 'Артикул уже привязан к другому товару. Вы можете сделать слияние двух товаров с одинаковыми артикулами', 'warning');
                } else {
                    this.showToast('Не удалось добавить артикул', 'Артикул уже привязан к другому товару. Вы можете добавить нужный артикул к уже существующему товару', 'warning');
                }
                this.newSkuMenuOpened = false;
                this.isCover = false;
                return false;
            }
            this.isCover = false;
            return true;
        },

        // Метод для добавления новой строки в список SKU продукта
        async addNewSkuListRow(sku) {
            const lowerCaseSku = sku.toLowerCase();
            if (this.product.sku_list.some(existingSku => existingSku.toLowerCase() === lowerCaseSku)) {
                // Если SKU уже существует, выводим сообщение об ошибке
                this.showToast('Не удалось добавить артикул', 'Такой артикул уже существует у этого товара', 'warning');
            } else {
                // Добавляем новый SKU в список SKU продукта, если такого артикула нет у других товаров
                const result = await this.sendNewSkuRequest(sku);
                if (result) {
                    this.product.sku_list.push(sku);
                    if (this.product.sku_list.length === 1) {
                        this.product.main_sku = sku;
                    }
                    // this.showToast('Артикул добавлен', 'Вариант артикула для товара успешно добавлен', 'success');
                } else if (result === false) {
                    // Указываем, что этот товар можно слить с другим товаром
                    if (this.isEditing) {
                        this.mergeWithProduct.isNeedToMerge = true;
                    }
                } else if (result === null) {
                    this.showToast('Не удалось добавить артикул', 'Этот артикул уже привязан к этому товару. Откатите или сохраните текущие изменения, чтобы добавить его вновь', 'warning');
                }
                this.newSkuMenuOpened = false;
                this.new_sku = null;
            }
        },

        // Удалить вариант артикула из списка
        deleteSkuFromList(deleted_sku_index) {
            // Проверяем, что удаляемый артикул не последний в списке
            if (this.product.sku_list.length >= 2) {
                // Заменяем main_sku, если необходимо
                if (this.product.main_sku === this.product.sku_list[deleted_sku_index]) {
                    // Проверяем позицию удаляемого sku, для корректного обновления main_sku
                    if (deleted_sku_index !== 0) {
                        this.product.main_sku = this.product.sku_list[0];
                    } else {
                        this.product.main_sku = this.product.sku_list[1];
                    }
                    // Уведомляем пользователя о замене main_sku
                    this.showToast('Основной артикул изменён', 'Основной артикул товара был автоматически заменён', 'info');
                }
                // Вырезаем удаляемый sku
                this.product.sku_list.splice(deleted_sku_index, 1);
            }
        },

        //
        showDublicateProduct() {
            window.open('#/products/' + this.mergeWithProduct.id + '/edit', '_blank');
        },

        //
        sendMergeRequest() {
            this.isCover = true;
            productAPI.merge(this.id, this.mergeWithProduct.id).then((res) => {
                this.$router.push('/products/' + res.data.data.id + '/edit');
                this.product = res.data.data;
                this.mergeWithProduct.isNeedToMerge = false;
                this.originalProduct = JSON.parse(JSON.stringify(this.product))
                this.isCover = false;
                this.showToast('Успешно', 'Слияние двух товаров успешно завершено', 'success');
            });
        },

        //
        openInvoice(invoice_id) {
            this.$router.push('/invoices/' + invoice_id + '/edit');
        },

        //
        openWantToMergeModal() {
            this.isShowProductSelect = true;
        },

        //
        onSelectProduct(product) {
            console.log(product);
            if (product.id === this.product.id) {
                this.showToast('Ошибка', 'Невозможно сделать слияние товара с самим собой. Выберите другой товар', 'warning');
            } else {
                this.isShowProductSelect = false;
                this.mergeWithProduct = product;
                this.mergeWithProduct.isWantToMerge = true;
                this.mergeWithProduct.isNeedToMerge = true;

                const modal_contrainer = document.querySelector('#modal-container');
                modal_contrainer.style.zIndex = 9999;
            }
        },
        async onCorrect() {
            this.isCover = true;
            this.closeCorrectModal();

            const response = await productAPI.correct(this.product);
            this.product = response.data.data;

            this.fillNewRealStocks();

            if (response.data) {
                this.showToast('Остатки скорректированы', 'Остатки у товара успешно скорректированы', 'success')
            }

            this.isCover = false;

        },
        onExit() {
            this.$router.push('/');
        },

        onDeleteClick() {
            isShowDeleteModal = true;
        },

        //
        async destroyProduct() {
            if (this.isEditing) {
                try {
                    const response = await productAPI.destroy(this.id);
                    if (response.data) {
                        this.showToast('Товар удалён', 'Товар ' + this.product.main_sku + ' успешно удалён', 'success');
                        this.$router.push('/');
                    } else {
                        // this.showToast('Не удалось удалить товар', 'Товар ' + this.product.main_sku + ' не был удалён. ' + response.data.message, 'warning');
                    }
                } catch (error) {
                    // this.showToast('Ошибка при удалении товара', 'Произошла ошибка при удалении товара: ' + error.response.data.message, 'warning');
                }
            }
        },

        //метод заполняет массив со статусами, которые используются у заказов товара
        getUniqueOrderStatuses(data) {
            this.orderStatusOptions = data.reduce((unique, order) => {
                const status = {
                    id: order.order_status,
                    name: order.order_status
                }
                if (!unique.some(obj => obj.id === status.id)) {
                    unique.push(status)
                }
                return unique
            }, []);
        },
        openCorrectModal() {
            this.isCorrcectModalOpened = true;
        },
        closeCorrectModal() {
            this.isCorrcectModalOpened = false;
        },
        orderState(state) {
            const stateDictionary = {
                "reserved": "Зарезервирован",
                "outcomed": "Отгружен",
                "refund": "Возврат",
                "defect": "Брак",
                "canceled": "Отменен",
                null: "Неизвестно"
            };

            return stateDictionary[state];
        },
        transactionAmountToString(transaction) {
            if (transaction.transactionable_type == 'App\\Models\\V1\\Stock') {
                return transaction.amount;
            } else if (transaction.type == 'In') {
                return "+" + transaction.amount;
            } else if (transaction.type == 'Out') {
                return "-" + transaction.amount;
            }
        },
        async onTransactionsExport() {
            const response = await productAPI.transactions_export(this.product.id)
            this.downloadFile(response, 'Транзакции арт.' + this.product.main_sku + ' по остаткам');
        },
        downloadFile(response, fileName) {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', fileName + '.xlsx');
            document.body.appendChild(link);
            link.click();
        },
    },
    mounted() {
        // Загружаем данные при монтировании компонента
        if (this.isEditing) {
            this.loadProduct();
        } else {
            this.product = {};
            this.product.name = "";
            this.contractor_ids = [];
            this.product.sku_list = [];
            this.product.brand_id = null;
            this.product.invoices = [];
            this.product.storePositions = [];
            this.product.orderPositions = [];
            this.isLoaded = true;
        }
        this.loadBrands();
    },
    computed: {
        filteredInvoices() {
            let invoiceFilter = this.invoiceFilter.toLowerCase().trim();
            let statusFilter = this.invoiceStatusFilter;
            return this.product.invoices.filter(invoice => {
                return (
                    invoice.number.toLowerCase().includes(invoiceFilter) &&
                    (statusFilter ? (invoice.status == this.invoiceStatusFilter) : true)
                );
            })
        },
        filteredOrders() {
            let orderFilter = this.orderFilter.toLowerCase().trim();
            let statusFilter = this.orderStatusFilter;
            return this.product.orderPositions.filter(order => {
                return (
                    (order.number.toLowerCase().includes(orderFilter) || order.crm_id == orderFilter) &&
                    (statusFilter ? (order.order_status == this.orderStatusFilter) : true)
                );
            })
        },
        isDirty() {
            return JSON.stringify(this.product) !== JSON.stringify(this.originalProduct)
        },
        productId() {
            return this.$route.params.product_id;
        },
        isEditing() {
            return !!this.productId;
        },
        totalStoreRealStock() {
            return this.product.storePositions.reduce((total, storePosition) => total + parseInt(storePosition.real_stock), 0)
        },
        totalOrderAmount() {
            return this.product.orderPositions.reduce((total, order) => total + parseInt(order.amount), 0);
        },
        totalOrderClosed() {
            return this.product.orderPositions.reduce((total, order) => {
                if (order.state === "outcomed") {
                    return total + parseInt(order.amount);
                }
                return total;
            }, 0);
        },
        totalOrderReserved() {
            return this.product.orderPositions.reduce((total, order) => {
                if (order.state === "reserved" && order.deleted_at === null) {
                    return total + parseInt(order.amount);
                }
                return total;
            }, 0);
        },
        totalOrderRefund() {
            return this.product.orderPositions.reduce((total, order) => {
                if (order.state === "refund" && order.deleted_at === null) {
                    return total + parseInt(order.amount);
                }
                return total;
            }, 0);
        },
        totalOrderDefect() {
            return this.product.orderPositions.reduce((total, order) => {
                if (order.state === "defect" && order.deleted_at === null) {
                    return total + parseInt(order.amount);
                }
                return total;
            }, 0);
        },
        totalOrderCanceled() {
            return this.product.orderPositions.reduce((total, order) => {
                if (order.state === "canceled" && order.deleted_at === null) {
                    return total + parseInt(order.amount);
                }
                return total;
            }, 0);
        },
        totalOrderUnknown() {
            return this.product.orderPositions.reduce((total, order) => {
                if (order.state === null && order.deleted_at === null) {
                    return total + parseInt(order.amount);
                }
                return total;
            }, 0);
        },
        totalInvoiceWaitingAmount() {
            return this.product.invoices.reduce((total, invoice) => total + parseInt(invoice.amount - invoice.received - invoice.refused), 0);
        },
        totalStoreSaledAmount() {
            return this.product.storePositions.reduce((total, storePosition) => total + parseInt(storePosition.saled_amount), 0);
        },
        totalFreeStock() {
            const result = this.totalStoreRealStock - this.totalOrderReserved;
            if (result < 0) {
                return 0;
            } else {
                return result;
            }
        },
        totalInvoiceAmount() {
            return this.product.invoices.reduce((total, invoice) => total + parseInt(invoice.amount), 0);
        },
        totalInvoiceCredited() {
            return this.product.invoices.reduce((total, invoice) => total + parseInt(invoice.received), 0);
        },
        totalRefundCount() {
            return this.product.invoices.reduce((total, invoice) => total + parseInt(invoice.refused), 0);
        },
        minPrice() {
            return Math.min(...this.product.invoices.map(invoice => invoice.price));
        },
        maxPrice() {
            return Math.max(...this.product.invoices.map(invoice => invoice.price));
        },
        canUserEdit() {
            return this.checkPermission('product_update') || (this.checkPermission('product_create') && !this.isEditing);
        },
        canUserDelete() {
            return this.checkPermission('product_delete') && this.isEditing;
        },
        canUserCorrect() {
            return this.checkPermission('product_stocks_correct') && this.isEditing;
        },
        filteredStorePositions() {
            if (this.hasRealStock) {
                return this.product.storePositions.filter(stock => stock.real_stock > 0);
            }
            return this.product.storePositions;
        },
        canUserChangeIsProfitablePurchase() {
            return this.checkPermission('product_is_profitable_purchase_update') && this.isEditing;
        }
    },

    watch: {
        product: {
            handler() {
                this.isDirty = true
            },
            deep: true
        }
    }
}

</script>

<style scoped>
.textarea-table-popup textarea {
    width: 200px;
}
</style>
