INSERT INTO invoice_products (id, invoice_id, product_id, price, amount, received, refused, created_at, updated_at)
SELECT ip.id, ip.invoice_id, sp.product_id, sp.price, ip.amount, ip.credited AS received, ip.money_refund AS refused, ip.created_at, ip.updated_at
FROM invoice_positions ip
JOIN store_positions sp ON ip.store_position_id = sp.id

