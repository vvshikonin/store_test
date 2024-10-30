ALTER TABLE store_positions MODIFY product_id BIGINT UNSIGNED;
ALTER TABLE order_positions MODIFY product_id BIGINT UNSIGNED;

ALTER TABLE store_positions
ADD FOREIGN KEY (product_id) REFERENCES products(id);

ALTER TABLE order_positions
ADD FOREIGN KEY (product_id) REFERENCES products(id);