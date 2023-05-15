<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-client/blob/master/LICENSE
 */
namespace Dtm\Exception;

use Dtm\Constants\Result;

class FailureException extends RequestException
{
    public $message = Result::FAILURE;

    public $code = Result::FAILURE_STATUS;
}
