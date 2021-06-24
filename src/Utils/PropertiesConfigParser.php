<?php 

declare(strict_types=1);

namespace Nacos\Utils;

class PropertiesConfigParser
{
    public static function parse(string $content)
    {
        $values = [];
        $lines = explode("\n", $content);
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) {
                continue;
            }

            // 只接受字母开头的配置项（过滤注释等内容）
            $firstChar = substr($line, 0, 1);
            if (!preg_match('/[A-Za-z]/', $firstChar)) {
                continue;
            }

            // 使用 = 分离键值
            $kv = explode('=', $line, 2);
            if (count($kv) !== 2) {
                continue;
            }

            list($key, $value) = $kv;
            $key = trim($key);
            $value = static::translateValue($value);
            $values[$key] = $value;
        }
        return $values;
    }

    /**
     * translateValue 
     *
     * @param string $value
     *
     * @return string
     */
    protected static function translateValue(string $value)
    {
        $value = trim($value);
        if (in_array($value,['true','false'])) {
            return (bool) $value;
        }

        // 去除首尾引号
        if (strlen($value) > 1) {
            $start = substr($value, 0, 1);
            $end = substr($value, -1);
            if (($start === '"' && $end === '"') || ($start === "'" && $end === "'")) {
                return substr($value, 1, -1);
            }
        }
        return $value;
    }
}
