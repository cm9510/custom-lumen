<?php
namespace App\Service;

class Redis
{
    private $redis;

    public function __construct(array $config)
    {
        try {
            if(!extension_loaded('redis')){
                throw new \Exception('没安装redis扩展~~');
            }
            $this->redis = new \Redis();
            $this->redis->connect($config['host'], $config['port'], $config['timeout']);
            if(isset($config['password']) && $config['password'] != ''){
                $this->redis->auth($config['password']);
            }
        }catch (\Exception $e){
            exit('redis连接错误：'.$e->getMessage());
        }
    }

    /**
     * 获取redis客户端
     * @param int $index
     * @return \Redis
     */
    public function getRedis(int $index = 0): \Redis
    {
        if($index > 0){
            $this->redis->select($index);
        }
        return $this->redis;
    }

    public function setKey(string $key, string $value, int $expire = 0): bool
    {
        if($expire > 0) return $this->redis->setex(trim($key), $expire, $value);
        return $this->redis->set(trim($key), $value);
    }

    public function getKey(string $key):string
    {
        return $this->redis->get(trim($key));
    }

    public function delKey(string $key):int
    {
        return $this->redis->del(trim($key));
    }

    public function keyTtl(string $key)
    {
        return $this->redis->ttl($key);
    }

    public function multiSet(array $data): bool
    {
        return $this->redis->mset($data);
    }

    public function increment(string $key, int $step = 0): int
    {
        if($step > 0) return $this->redis->incrBy(trim($key), $step);
        return $this->redis->incr(trim($key));
    }

    public function decrement(string $key, int $step = 0): int
    {
        if($step > 0) return $this->redis->decrBy(trim($key), $step);
        return $this->redis->decr(trim($key));
    }

    public function push(string $dire, string $key, $value)
    {
        $dire = strtolower($dire);
        if(!in_array(strtolower($dire), ['l','r'])) return false;
        if($dire == 'l'){
            if(is_array($value)) return $this->redis->lPush(trim($key), ...$value);
            return $this->redis->lPush(trim($key), $value);
        }else{
            if(is_array($value)) return $this->redis->rPush(trim($key), ...$value);
            return $this->redis->rPush(trim($key), $value);
        }
    }
    public function close()
    {
        $this->redis->close();
    }
}
