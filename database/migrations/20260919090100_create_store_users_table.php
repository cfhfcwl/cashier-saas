<?php
declare(strict_types=1);

use think\migration\Migrator;

/**
 * 店员 / 登录账号表
 *
 * RBAC 三级角色直接用 role 字段：owner（店主）> manager（店长）> cashier（收银员）。
 * 三级角色不值得单开角色表——如果哪天角色需要"可配置的权限点"，
 * 再演进成 role / permission / role_permission 三张表，这是预留的演进方向，不是过度设计。
 */
class CreateStoreUsersTable extends Migrator
{
    public function change(): void
    {
        $this->table('store_users', [
                'engine'    => 'InnoDB',
                'collation' => 'utf8mb4_unicode_ci',
                'comment'   => '店员与登录账号',
            ])
            ->addColumn('store_id', 'integer', [
                'comment' => '所属店铺',
            ])
            ->addColumn('phone', 'string', [
                'limit'   => 20,
                'comment' => '登录手机号，全局唯一',
            ])
            ->addColumn('password', 'string', [
                'limit'   => 255,
                'comment' => 'bcrypt 哈希',
            ])
            ->addColumn('nickname', 'string', [
                'limit'   => 50,
                'comment' => '姓名 / 显示名',
            ])
            ->addColumn('role', 'string', [
                'limit'   => 16,
                'default' => 'cashier',
                'comment' => 'owner / manager / cashier',
            ])
            ->addColumn('status', 'boolean', [
                'default' => 1,
                'comment' => '1=在职 0=停用',
            ])
            ->addColumn('last_login_at', 'datetime', [
                'null'    => true,
                'default' => null,
            ])
            ->addColumn('created_at', 'datetime', ['null' => true, 'default' => null])
            ->addColumn('updated_at', 'datetime', ['null' => true, 'default' => null])
            ->addIndex(['store_id'], ['name' => 'idx_store'])
            ->addIndex(['phone'], ['unique' => true, 'name' => 'uk_phone'])
            ->create();
    }
}
