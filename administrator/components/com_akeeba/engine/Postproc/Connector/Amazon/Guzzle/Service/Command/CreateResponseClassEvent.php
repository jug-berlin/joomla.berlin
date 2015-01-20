<?php

namespace Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Service\Command;

use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Common\Event;

/**
 * Event class emitted with the operation.parse_class event
 */
class CreateResponseClassEvent extends Event
{
    /**
     * Set the result of the object creation
     *
     * @param mixed $result Result value to set
     */
    public function setResult($result)
    {
        $this['result'] = $result;
        $this->stopPropagation();
    }

    /**
     * Get the created object
     *
     * @return mixed
     */
    public function getResult()
    {
        return $this['result'];
    }
}
