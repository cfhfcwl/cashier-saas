<?php
declare(strict_types=1);

use think\migration\Migrator;

/**
 * 商品表
 *
 * price 用整数「分」（BIGINT UNSIGNED），不用 DECIMAL：
 * 收银金额参与大量计算与跨端传输，整数分从根上消除精度与序列化歧义。
 *
 * stock 是当前值；真正的账在 stock_logs（第 6 周），定期对账。
 *
 * deleted_at 软删除：商品被订单引用过就不能物理删，否则历史订单的明细成了悬空引用。
 */
class CreateProductsTable extends Migrator
{
    public function change(): void
    {
        $this->table('products', [
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
                'comment'   => '商品',
            ])
            ->addColumn('store_id', 'integer', [
                'comment' => '所属店铺',
            ])
            ->addColumn('category_id', 'integer', [
                'comment' => '分类 ID',
            ])
            ->addColumn('name', 'string', [
                'limit'   => 100,
                'comment' => '商品名',
            ])
            ->addColumn('barcode', 'string', [
                'limit'   => 32,
                'null'    => true,
                'default' => null,
                'comment' => '条码，扫码收银用',
            ])
            ->addColumn('price', 'biginteger', [
                'signed'  => false,
                'default' => 0,
                'comment' => '售价，单位：分',
            ])
            ->addColumn('stock', 'integer', [
                'default' => 0,
                'comment' => '当前库存',
            ])
            ->addColumn('stock_warn', 'integer', [
                'default' => 0,
                'comment' => '库存预警阈值，低于此值提醒',
            ])
            ->addColumn('image', 'string', [
                'limit'   => 255,
                'null'    => true,
                'default' => null,
            ])
            ->addColumn('status', 'boolean', [
                'default' => 1,
                'comment' => '1=上架 0=下架',
            ])
            ->addColumn('created_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('deleted_at', 'datetime', [
                'null'    => true,
                'default' => null,
                'comment' => '软删除',
            ])
            ->addIndex(['store_id', 'category_id'], ['name' => 'idx_store_category'])
            ->addIndex(['store_id', 'barcode'], ['unique' => true, 'name' => 'uk_store_barcode'])
            ->create();
    }
}
