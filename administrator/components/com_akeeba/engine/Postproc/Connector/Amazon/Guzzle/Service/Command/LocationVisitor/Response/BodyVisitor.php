<?php

namespace Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Command\LocationVisitor\Response;

use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Command\CommandInterface;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Http\Message\Response;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Description\Parameter;

/**
 * Visitor used to add the body of a response to a particular key
 */
class BodyVisitor extends AbstractResponseVisitor
{
    public function visit(
        CommandInterface $command,
        Response $response,
        Parameter $param,
        &$value,
        $context =  null
    ) {
        $value[$param->getName()] = $param->filter($response->getBody());
    }
}
