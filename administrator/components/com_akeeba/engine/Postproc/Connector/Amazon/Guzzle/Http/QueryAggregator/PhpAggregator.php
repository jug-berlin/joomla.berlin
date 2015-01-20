<?php

namespace Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Http\QueryAggregator;

use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Http\QueryString;

/**
 * Aggregates nested query string variables using PHP style []
 */
class PhpAggregator implements QueryAggregatorInterface
{
    public function aggregate($key, $value, QueryString $query)
    {
        $ret = array();

        foreach ($value as $k => $v) {
            $k = "{$key}[{$k}]";
            if (is_array($v)) {
                $ret = array_merge($ret, self::aggregate($k, $v, $query));
            } else {
                $ret[$query->encodeValue($k)] = $query->encodeValue($v);
            }
        }

        return $ret;
    }
}
