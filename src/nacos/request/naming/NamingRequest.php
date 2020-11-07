<?php


namespace nacos\request\naming;


use nacos\NacosConfig;
use nacos\NamingConfig;
use nacos\util\LogUtil;
use nacos\request\Request;
use nacos\util\ReflectionUtil;

class NamingRequest extends Request
{

    protected function getParameterAndHeader()
    {
        $headers = [];
        $parameterList = [];

        $properties = ReflectionUtil::getProperties($this);
        foreach ($properties as $propertyName => $propertyValue) {
            if (in_array($propertyName, $this->standaloneParameterList)) {
                // 忽略这些参数
            } else {
                $parameterList[$propertyName] = $propertyValue;
            }
        }

        if ($this instanceof RegisterInstanceNaming) {
            $parameterList["ephemeral"] = NamingConfig::getEphemeral();
        }

        if (NacosConfig::getIsDebug()) {
            LogUtil::info(strtr("parameterList: {parameterList}, headers: {headers}", [
                "parameterList" => json_encode($parameterList),
                "headers" => json_encode($headers)
            ]));
        }
        return [$parameterList, $headers];
    }

}