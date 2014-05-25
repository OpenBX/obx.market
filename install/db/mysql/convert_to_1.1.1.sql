ALTER TABLE obx_orders MODIFY COLUMN TIMESTAMP_X datetime not null;
ALTER TABLE obx_orders MODIFY COLUMN DATE_CREATED datetime not null;
ALTER TABLE obx_basket MODIFY COLUMN TIMESTAMP_X datetime not null;
ALTER TABLE obx_basket MODIFY COLUMN DATE_CREATED datetime not null;
ALTER TABLE obx_order_comments MODIFY COLUMN TIMESTAMP_X datetime not null;
ALTER TABLE obx_order_comments MODIFY COLUMN DATE_CREATED datetime not null;
