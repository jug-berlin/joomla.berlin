<?php

namespace Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Command\LocationVisitor\Request;

use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Http\Message\RequestInterface;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Command\CommandInterface;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Description\Parameter;

/**
 * Visitor used to apply a parameter to a request's query string
 */
class QueryVisitor extends AbstractRequestVisitor
{
    public function visit(CommandInterface $command, RequestInterface $request, Parameter $param, $value)
    {
        $request->getQuery()->set($param->getWireName(), $this->prepareValue($value, $param));
    }
}
