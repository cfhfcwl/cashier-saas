<?php
declare(strict_types=1);

use think\migration\Migrator;

/**
 * 积分流水表 —— append-only，禁止 UPDATE
 *
 * change 有正有负：earn 加分、consume 扣分、adjust 店员手动修正。
 * 会员当前积分 = 该会员所有流水之和，members.points 只是缓存。
 *
 * 好处：任何一笔积分都能追溯到"哪张订单、谁改的、为什么"，
 * 对账和客诉取证都靠它。没有流水表的积分系统等于一本不能对账的账。
 */
class CreateMemberPointsLogsTable extends Migrator
{
    public function change(): void
    {
        $this->table('member_points_logs', [
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
                'comment'   => '积分流水（只增不改）',
            ])
            ->addColumn('store_id', 'integer', [
                'comment' => '所属店铺',
            ])
            ->addColumn('member_id', 'integer', [
                'comment' => '会员 ID',
            ])
            ->addColumn('change', 'integer', [
                'comment' => '变动值，正数加分、负数扣分',
            ])
            ->addColumn('type', 'string', [
                'limit'   => 16,
                'comment' => 'earn 消费获得 / consume 积分抵扣 / adjust 手动修正',
            ])
            ->addColumn('order_id', 'integer', [
                'null'    => true,
                'default' => null,
                'comment' => '关联订单，手动修正时为空',
            ])
            ->addColumn('remark', 'string', [
                'limit'   => 255,
                'null'    => true,
                'default' => null,
                'comment' => '备注（adjust 时必填）',
            ])
            ->addColumn('created_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['store_id'], ['name' => 'idx_store'])
            ->addIndex(['member_id', 'created_at'], ['name' => 'idx_member_time'])
            ->create();
    }
}
