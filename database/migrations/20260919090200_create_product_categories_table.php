<?php
declare(strict_types=1);

use think\migration\Migrator;

/**
 * 商品分类
 *
 * (store_id, name) 联合唯一 —— 分类名只在同一店铺内唯一，
 * 不同店铺完全可以有同名分类（"饮料"谁家都有）。
 */
class CreateProductCategoriesTable extends Migrator
{
    public function change(): void
    {
        $this->table('product_categories', [
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
                'comment'   => '商品分类',
            ])
            ->addColumn('store_id', 'integer', [
                'comment' => '所属店铺',
            ])
            ->addColumn('name', 'string', [
                'limit'   => 50,
                'comment' => '分类名',
            ])
            ->addColumn('sort', 'integer', [
                'default' => 0,
                'comment' => '排序值，越小越靠前',
            ])
            ->addColumn('status', 'boolean', [
                'default' => 1,
                'comment' => '1=启用 0=停用',
            ])
            ->addColumn('created_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['store_id'], ['name' => 'idx_store'])
            ->addIndex(['store_id', 'name'], ['unique' => true, 'name' => 'uk_store_name'])
            ->create();
    }
}
