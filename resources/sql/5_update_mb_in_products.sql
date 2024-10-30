UPDATE products
LEFT JOIN maintained_balances ON maintained_balances.sku = products.main_sku
SET products.maintained_balance = maintained_balances.amount