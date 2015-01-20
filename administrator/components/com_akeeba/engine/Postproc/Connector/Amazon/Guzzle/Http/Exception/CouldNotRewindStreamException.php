<?php

namespace Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Http\Exception;

use Akeeba\Engine\Postproc\Connector\Amazon\Guzzle\Common\Exception\RuntimeException;

class CouldNotRewindStreamException extends RuntimeException implements HttpException {}
