import { createStore } from 'vuex';
import { authModule } from './modules/auth_module';
import { userModule } from './modules/user_module';
import { contractorModule } from './modules/contractor_module';
import { productModule } from './modules/product_module';
import { moneyRefundModule } from './modules/money_refunds_module';
import { productRefundModule } from './modules/product_refund_module';
import { defectModule } from './modules/defect_module';
import { notificationModule } from './modules/toaster_module';
import { appSettingsModule } from './modules/app_settings_module';
import { legalEntityModule } from './modules/legalEntity_module';
import { paymentMethodModule } from './modules/payment_method_module';
import { brandModule } from './modules/brand_module';

import invoiceModule from './modules/invoice_module';
import contractorRefundModule from './modules/contractor_refund_module';

// Создание хранилища
export const store = createStore({
  modules: {
    authModule,
    userModule,
    contractorModule,
    productRefundModule,
    moneyRefundModule,
    defectModule,
    productModule,
    notificationModule,
    appSettingsModule,
    legalEntityModule,
    paymentMethodModule,
    brandModule,

    invoiceModule,
    contractorRefundModule
  },
});
