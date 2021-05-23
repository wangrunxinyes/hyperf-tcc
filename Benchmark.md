# 压测性能测试
> 此压测性能仅供参考

# Wrk -c1000 -d10 -t60

>   server.php 配置如下
    worker_num = 2
    MODE = SWOOLE_BASE

使用了`Rate-limit`组件
```bash
$ wrk -c1000 -d10 -t60 http://localhost:9501/index/nsq?goods_id=5&coupon_id=0
60 threads and 1000 connections
  Thread Stats   Avg      Stdev     Max   +/- Stdev
    Latency     1.52s   397.03ms   1.92s    83.33%
    Req/Sec    27.56     27.51   131.00     69.62%
  3829 requests in 10.10s, 816.50KB read
  Socket errors: connect 0, read 3574, write 0, timeout 3817
Requests/sec:    379.16
Transfer/sec:     80.85KB

QPS 379/sec
新增订单 2053
```
接口代码如下

```php
/**
     * @GetMapping(path="nsq")
     * @RateLimit(create=100,consume=100,capacity=200,waitTimeout=2, limitCallback={RateController::class, "callback"})
     */
    public function nsq()
    {
        $q = di(RequestInterface::class);
        $goodsId = $q->input('goods_id', 1);
        $couponId = $q->input('coupon_id', 0);
        $res = di(Test::class)->handle($goodsId,$couponId);
        return $this->response->success($res);
    }
```
