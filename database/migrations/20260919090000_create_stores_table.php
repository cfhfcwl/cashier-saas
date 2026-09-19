<?php
declare(strict_types=1);

use think\migration\Migrator;

/**
 * 店铺表 —— 多租户的根
 *
 * 整个系统的所有业务数据都挂在 store_id 下。
 * owner 的登录账号不在这里，在 store_users（role = owner）。
 */
class CreateStoresTable extends Migrator
{
    public function change(): void
    {
        $this->table('stores', [
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
                'comment'   => '店铺（多租户根）',
            ])
            ->addColumn('name', 'string', [
                'limit'   => 100,
                'comment' => '店铺名称',
            ])
            ->addColumn('address', 'string', [
                'limit'   => 255,
                'null'    => true,
                'default' => null,
            ])
            ->addColumn('status', 'boolean', [
                'default' => 1,
                'comment' => '营业状态 1=营业 0=休息',
            ])
            ->addColumn('expire_at', 'datetime', [
                'null'    => true,
                'default' => null,
                'comment' => 'SaaS 服务到期时间（预留）',
            ])
            ->addColumn('created_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->create();
    }
}
