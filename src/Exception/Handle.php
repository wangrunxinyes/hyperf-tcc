<?php


namespace H6Play\TccTransaction\Exception;


use H6Play\TccTransaction\Consumer\TccState;
use Hyperf\DbConnection\Db;

class Handle
{
    public function handle(string $tccId, TccState $state, \Throwable $e) {
        Db::table('tcc_fail')->insert([
            'iid' => $tccId,
            'options' => serialize($state),
            'created_at' => date('Y-m-d H:i:s', $state->createAt),
            'exception' => json_encode([
                'class' => get_class($e),
                'message' => $e->getMessage(),
                'location' => $e->getLine() . '#' . $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ], JSON_UNESCAPED_UNICODE)
        ]);
    }
}