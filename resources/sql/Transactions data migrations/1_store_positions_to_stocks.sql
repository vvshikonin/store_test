INSERT INTO stocks (id, product_id, contractor_id, price, amount, saled, user_comment, is_sale, created_at, updated_at, deleted_at) SELECT id, product_id, contractor_id, price, real_stock, saled_amount, user_comment, is_sale, created_at, updated_at, deleted_at FROM store_positions;