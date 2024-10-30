UPDATE store_positions
LEFT JOIN products ON products.main_sku = store_positions.sku
SET store_positions.product_id = products.id;