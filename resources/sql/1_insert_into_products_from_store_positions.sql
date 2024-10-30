INSERT INTO products (main_sku, name, sku_list)
SELECT sku, MAX(name), CONCAT('["',sku, '"]' ) FROM store_positions
GROUP BY sku