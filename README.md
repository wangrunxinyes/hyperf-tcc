# hyperf-tcc

    基于Hyperf框架的分布式事务TCC组件，因为作者很少写Composer组件有些组件
    确实不需要太高的版本，但是因为太懒所以直接复用了之前项目的 composer 包版本配置
    已经经过 AB -N 1000 -N 100 压测测试, 无失败事务, 全可回滚, 数据无异常
    
## 安装方式

    # composer 安装
    composer require h6play/hyperf-tcc
    # 发布资源
    php bin/hyperf.php vendor:publish h6play/hyperf-tcc
    # 执行迁移
    php bin/hyperf.php migrate

## 功能列表

 - [X] 实现TCC操作实现接口 `TccOption`
 - [X] 实现事务处理 `Tcc`
 - [X] 实现事务编排 `Tcc->rely(...)`
 - [X] 实现事务重试 `NSQ消息订阅`
 - [X] 实现无法回滚事务记录并通知 `Database`

## Composer依赖

 - 欢迎提PR
 - `"hyperf/nsq": "^2.1"`
 - `"hyperf/redis": "~2.1.0"`
 - `"hyperf/database": "~2.1.0"`
 - `"hyperf/snowflake": "^2.1"`
 - `"hyperf/logger": "~2.1.0"`
 - `"hyperf/db-connection": "~2.1.0"`

## 使用方式

 - `Tcc` 事务创建
    - 对于事务进行流程编排
    - 对于事务的启动操作
 - `TccOption` 事务操作
    - 需要针对性实现 `try, confirm, cancel` 方法
    - 例如 `商品库存锁定`,  `商品库存扣除` 等
    - 在 `H6Play\TccTransaction\Example\Tcc` 下能看到很多演示写法
    - 不应当把 `复杂的参数` 放到该类中去例如对象等, 因为它会作为一个 `序列化的类` 存放到 `redis` 中
    - 如果操作类 `参数过多`, 或者 `属性` 中 `对象过多` 会造成 `存储负担`
    - 不推荐在 `TccOption` 操作类中写业务逻辑, 它应当作为一个调用服务的封装
 - `TccState` 事务状态
    - 存放操作和状态并且序列化到缓存中
 - `Coordinator/TccCoordinator` 事务协调者 `NSQ消费者` 需要加入到消费进程
    - 请先继承该类实现 `NSQ消费者进程绑定 @Consumer 注解`
    - 具体查看 [Hyperf NSQ文档](https://hyperf.wiki/2.0/#/zh-cn/nsq)

### 代码演示

 - 下面的代码演示都在 `Example` 中有实现
 - `Example\Tcc\*` 事务操作项
 - `Example\Service\*` 微服务实现类
 - `Example\Test` 模拟下单接口
 - `Example\database.sql` 演示案例数据库脚本, 测试前先导入

```php
use H6Play\TccTransaction\Tcc;
use H6Play\TccTransaction\Example\Tcc\GoodsLockTcc;
use H6Play\TccTransaction\Example\Tcc\CouponLockTcc;
use H6Play\TccTransaction\Example\Tcc\OrderTcc;
use H6Play\TccTransaction\Example\Tcc\GoodsSubTcc;
use H6Play\TccTransaction\Example\Tcc\CouponSubTcc;
use H6Play\TccTransaction\Example\Tcc\OrderMessageTcc;
use H6Play\TccTransaction\Example\Tcc\OrderStatisticsTcc;

$goodsId = 1;
$couponId = 0;
$tcc = new Tcc;
$tcc
    ->tcc(1, new GoodsLockTcc($goodsId)) // 商品库存锁定
    ->tcc(2, new CouponLockTcc($couponId)) // 优惠券锁定
    ->tcc(3, new OrderTcc) // 创建订单
    ->tcc(4, new GoodsSubTcc) // 扣减库存
    ->tcc(5, new CouponSubTcc) // 占用优惠券
    ->tcc(6, new OrderMessageTcc) // 创建订单消息
    ->tcc(7, new OrderStatisticsTcc) // 订单统计
    ->rely([          // 配置执行流程
        // 外层步骤是逐步执行的
        // [...] 内层步骤是同步执行的
        [1, 2],       // 1,2 锁定库存, 锁定优惠券
        [3],          // 3 创建订单
        [4, 5, 6, 7], // 4,5,6,7 扣减库存, 占用优惠券, 订单消息, 订单统计
    ])->begin(); // 开启事务
```

## 实现原理

## 学习案例