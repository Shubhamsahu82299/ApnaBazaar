-- Add price columns to orders table to store prices at the time of order
-- This will help maintain historical price data for BCP calculations

ALTER TABLE `orders` 
ADD COLUMN `buy_price_at_order_time` DECIMAL(10,2) DEFAULT 0.00 AFTER `deliveryPaymentMethod`,
ADD COLUMN `sell_price_at_order_time` DECIMAL(10,2) DEFAULT 0.00 AFTER `buy_price_at_order_time`;

-- Add comments to explain the purpose of these columns
-- buy_price_at_order_time: The buy price of the product/variant when the order was placed
-- sell_price_at_order_time: The sell price of the product/variant when the order was placed
-- This ensures that even if prices change later, we maintain the original pricing for historical orders
