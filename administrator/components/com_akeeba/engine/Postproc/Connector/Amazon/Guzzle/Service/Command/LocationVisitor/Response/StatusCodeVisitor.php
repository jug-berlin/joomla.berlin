<?php

namespace Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Command\LocationVisitor\Response;

use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Http\Message\Response;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Description\Parameter;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Command\CommandInterface;

/**
 * Location visitor used to add the status code of a response to a key in the result
 */
class StatusCodeVisitor extends AbstractResponseVisitor
{
    public function visit(
        CommandInterface $command,
        Response $response,
        Parameter $param,
        &$value,
        $context =  null
    ) {
        $value[$param->getName()] = $response->getStatusCode();
    }
}
