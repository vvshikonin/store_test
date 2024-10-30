INSERT INTO transactionables (transactionable_id, transactionable_type, stock_id, type, amount, created_at, updated_at, deleted_at)
SELECT s.id, 'Stock', s.id, 'In', s.amount, NOW(), NOW(), NULL
FROM stocks s;
