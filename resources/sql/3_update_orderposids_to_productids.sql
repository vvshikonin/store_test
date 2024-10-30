UPDATE order_positions
LEFT JOIN store_positions ON store_positions.id = order_positions.store_position_id
SET order_positions.product_id = store_positions.product_id