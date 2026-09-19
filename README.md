# cashier-saas

多租户门店收银系统 —— 覆盖「门店 → 商品 → 会员 → 订单 → 支付 → 对账」完整闭环。

> 这是我的核心作品项目。我做过 10 年 PHP 后端 + 一年收银机/收银软件创业，这个项目把两段经历合在了一起：用工程化的方式，实现我真实踩过坑的那个业务。

![CI](https://github.com/cfhfcwl/cashier-saas/actions/workflows/ci.yml/badge.svg)

## 技术栈

| 层 | 选型 |
|---|---|
| 框架 | ThinkPHP 8（PHP 8.2） |
| 数据库 | MySQL 8（表结构全部由 migration 管理） |
| 缓存/并发 | Redis 7（Lua 原子扣减库存、幂等锁、缓存） |
| 队列 | think-queue（redis 驱动，订单异步处理） |
| 部署 | Docker Compose（nginx + php-fpm + mysql + redis，一键启动） |
| CI | GitHub Actions（依赖校验 + 语法检查 + 敏感文件检查） |
| 前端 | 小程序（扫码点单）+ Vue3 / Element Plus（商户后台） |

## 快速开始

```bash
# 前置要求：Docker Desktop（WSL2 后端）
git clone https://github.com/cfhfcwl/cashier-saas.git
cd cashier-saas

# 1. 配置环境变量
cp .env.docker.example .env
#    修改 DB_PASS / REDIS_PASS 为你自己的密码

# 2. 一键启动（首次构建 3-10 分钟）
docker compose up -d --build

# 3. 从零建库：迁移 + 示例数据
docker compose exec php php think migrate:run
docker compose exec php php think seed:run

# 4. 访问
#    API:        http://localhost:8081
#    商户后台:   http://localhost:8081/admin（规划中）
```

不需要装 PHP、MySQL、Nginx、宝塔——环境本身就是代码。

## 功能规划

| 模块 | 状态 | 说明 |
|---|---|---|
| 多租户门店模型 | ✅ | stores / store_users，所有业务表带 `store_id` |
| 商品与分类 | ✅ | 分类树、商品档案、成本/售价（整数分）、库存预警 |
| 会员体系 | ✅ | 会员归属店铺、积分流水（只增不改） |
| 订单 + 库存三层防护 | 🔨 | Redis Lua 原子扣减 → MySQL 条件更新兜底 → 定时对账 |
| 支付回调幂等 | 📋 | 唯一索引 + Redis SETNX + 状态机校验 |
| 扫码点单小程序 | 📋 | 微信支付 V3 |
| 商户后台 | 📋 | 对账报表、RBAC 三级角色 |

## 设计决策（为什么这么做）

**1. 金额一律用整数「分」（BIGINT），不用 DECIMAL**
支付网关、对账场景中浮点误差是真实事故来源。整数分运算零误差，展示层再除以 100。

**2. 多租户隔离在应用层，不在数据库层**
所有业务表带 `store_id`，联合索引最左列永远是它；查询通过模型作用域强制注入，杜绝漏加条件。

**3. 会员归属店铺，不归属平台**
`(store_id, phone)` 联合唯一——独立商户不会接受会员数据放在平台手里，这是 SaaS 常见的产品决策，不是纯技术问题。

**4. 库存扣减三层防护**
Redis Lua 保证原子性扛并发；MySQL `stock >= ?` 条件更新做最终兜底；定时任务对账发现漂移。压测目标：200 并发 0 超卖。

**5. 支付回调幂等三件套**
`(out_trade_no)` 唯一索引 + Redis `SETNX` 抢锁 + 订单状态机校验非法流转。微信回调重发 10 次，只入账 1 笔。

## 目录结构

```
cashier-saas/
├── app/
│   ├── controller/     # 控制器（瘦层，≤20 行）
│   ├── service/        # 业务逻辑
│   ├── model/          # 模型（表名不带前缀）
│   └── validate/       # 参数校验
├── database/
│   ├── migrations/     # 表结构（唯一事实来源）
│   └── seeds/          # 演示数据
├── docker/
│   └── nginx/          # 伪静态与安全配置
├── docker-compose.yml
├── Dockerfile          # 两阶段构建
├── Makefile
└── .github/workflows/ci.yml
```

## Roadmap 与进度

进度同步更新于本文件的「功能规划」表。当前迭代：订单与库存并发安全（详见 commit 历史）。

## 关于我

10 年 PHP 后端（ThinkPHP），曾创办收银机/收银软件公司，熟悉门店收银、支付对接、会员营销的完整业务链路。正在寻找远程 / 西安方向的后端岗位。

- GitHub: [cfhfcwl](https://github.com/cfhfcwl)
- 另一个工程化练习项目: [CatLifeNews](https://github.com/cfhfcwl/CatLifeNews)

## License

MIT
