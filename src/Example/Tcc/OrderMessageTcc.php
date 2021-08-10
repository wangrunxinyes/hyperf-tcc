<?php

namespace YogCloud\TccTransaction\Example\Tcc;

use YogCloud\TccTransaction\Example\Service\OrderService;
use YogCloud\TccTransaction\TccOption;

class OrderMessageTcc extends TccOption
{
    /**
     * @var int
     */
    protected $msgId;

    public function try()
    {
        // 获取订单信息
        $orderId = (int) $this->tcc->get(OrderTcc::class)['id'];
        // 创建订单消息
        $service = new OrderService();
        $this->msgId = $service->createMessage($orderId, '订单创建成功');
    }

    public function confirm()
    {
        // 空操作
    }

    public function cancel()
    {
        // 删除订单消息
        $service = new OrderService();
        $service->deleteMessage($this->msgId);
    }
}
