<?php

namespace Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Plugin\Cache;

use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Common\Exception\InvalidArgumentException;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Http\Message\RequestInterface;
use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Http\Message\Response;

/**
 * Determines if a request can be cached using a callback
 */
class CallbackCanCacheStrategy extends DefaultCanCacheStrategy
{
    /** @var callable Callback for request */
    protected $requestCallback;

    /** @var callable Callback for response */
    protected $responseCallback;

    /**
     * @param \Closure|array|mixed $requestCallback  Callable method to invoke for requests
     * @param \Closure|array|mixed $responseCallback Callable method to invoke for responses
     *
     * @throws InvalidArgumentException
     */
    public function __construct($requestCallback = null, $responseCallback = null)
    {
        if ($requestCallback && !is_callable($requestCallback)) {
            throw new InvalidArgumentException('Method must be callable');
        }

        if ($responseCallback && !is_callable($responseCallback)) {
            throw new InvalidArgumentException('Method must be callable');
        }

        $this->requestCallback = $requestCallback;
        $this->responseCallback = $responseCallback;
    }

    public function canCacheRequest(RequestInterface $request)
    {
        return $this->requestCallback
            ? call_user_func($this->requestCallback, $request)
            : parent::canCacheRequest($request);
    }

    public function canCacheResponse(Response $response)
    {
        return $this->responseCallback
            ? call_user_func($this->responseCallback, $response)
            : parent::canCacheResponse($response);
    }
}
