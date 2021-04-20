<?php

namespace Nacos\Utils;

use Nacos\Models\ServiceInstance;

class RandomByWeightSelector
{
    /**
     * 权重+随机选择 instance
     *
     * @param ServiceInstance[] $instances
     * @return ServiceInstance
     * @throws \Exception
     */
    public static function select(array $instances)
    {
        $length = count($instances);

        // 只有一项，不需要负载
        if (1 === $length) {
            return $instances[0];
        }

        $sameWeight = true;                                 // 是否所有 instance 权重相同
        $firstWeight = $instances[0]->getWeightDouble();    // 第一个 instance 的权重
        $weights = [0 => $firstWeight];                     // 所有 instance 的权重字典
        $totalWeight = $firstWeight;                        // 总权重

        for ($i = 1; $i < $length; $i++) {
            $weight = $instances[$i]->getWeightDouble();
            $weights[$i] = $weight;
            $totalWeight += $weight;
            // 检查所有权重是否相同
            if ($sameWeight && $weight != $firstWeight) {
                $sameWeight = false;
            }
        }

        // 总权重 > 0 且 权重不一致时：带权重随机
        if ($totalWeight > 0 && false === $sameWeight) {
            $offset = random_int(0, $totalWeight - 1);
            for ($i = 0; $i < $length; $i++) {
                $offset -= $weights[$i];
                if ($offset < 0) {
                    return $instances[$i];
                }
            }
        }

        // 所有权重一致 or 总权重为 0，随机取一个
        return $instances[random_int(0, $length - 1)];
    }
}
