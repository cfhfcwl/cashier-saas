<?php
declare(strict_types=1);

use think\migration\Migrator;

/**
 * 会员表
 *
 * (store_id, phone) 联合唯一 —— 会员属于店铺，不属于平台：
 * 同一个人在 A 店和 B 店是两条独立会员记录（积分、余额互不相通）。
 * 这是业务决策，不是技术妥协——独立商户不会接受"我的会员数据存在平台手里"。
 *
 * points 是缓存值（可随时由积分流水重算），真正的账在 member_points_logs。
 */
class CreateMembersTable extends Migrator
{
    public function change(): void
    {
        $this->table('members', [
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
                'comment'   => '会员（归属店铺）',
            ])
            ->addColumn('store_id', 'integer', [
                'comment' => '所属店铺',
            ])
            ->addColumn('phone', 'string', [
                'limit'   => 20,
                'comment' => '手机号',
            ])
            ->addColumn('nickname', 'string', [
                'limit'   => 50,
                'null'    => true,
                'default' => null,
            ])
            ->addColumn('points', 'integer', [
                'default' => 0,
                'comment' => '积分缓存值，权威数据在积分流水表',
            ])
            ->addColumn('status', 'boolean', [
                'default' => 1,
                'comment' => '1=正常 0=停用',
            ])
            ->addColumn('created_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['store_id'], ['name' => 'idx_store'])
            ->addIndex(['store_id', 'phone'], ['unique' => true, 'name' => 'uk_store_phone'])
            ->create();
    }
}
