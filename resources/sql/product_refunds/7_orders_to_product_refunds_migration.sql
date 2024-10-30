INSERT INTO product_refunds (order_id, type, status, created_at, completed_at, product_location)
SELECT id, product_refund_type, product_refund_status, product_refund_created_at, product_refund_completed_at, product_location
FROM orders
WHERE orders.product_refund_status IS NOT NULL