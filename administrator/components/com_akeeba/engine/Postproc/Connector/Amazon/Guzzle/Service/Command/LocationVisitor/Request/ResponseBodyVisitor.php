<?php

namespace Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Command\LocationVisitor\Request;

use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Http\Message\RequestInterface;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Command\CommandInterface;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Description\Parameter;

/**
 * Visitor used to change the location in which a response body is saved
 */
class ResponseBodyVisitor extends AbstractRequestVisitor
{
    public function visit(CommandInterface $command, RequestInterface $request, Parameter $param, $value)
    {
        $request->setResponseBody($value);
    }
}
