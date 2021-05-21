<?php


namespace H6Play\TccTransaction\Example\Tcc;


use H6Play\TccTransaction\Example\Service\OrderService;
use H6Play\TccTransaction\TccOption;

class OrderStatisticsTcc extends TccOption
{

    public function try()
    {
        # 增加订单统计
        $service = new OrderService;
        $service->incOrderStatistics();
    }

    public function confirm()
    {
        # 空操作
    }

    public function cancel()
    {
        # 减少订单统计
        $service = new OrderService;
        $service->decOrderStatistics();
    }
}