<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2023-05-12 09:58
 */

namespace dtm\api;

use Google\Protobuf\GPBEmpty;
use Google\Protobuf\Internal\Message;

class RequestBranch
{
    public string $method;

    public array $body = [];

    public string $branchId;

    public string $op;

    public string $url;

    public array $branchHeaders = [];

    public Message $grpcArgument;

    public array $grpcMetadata = [];

    public array $grpcDeserialize = [GPBEmpty::class, 'decode'];

    public array $grpcOptions = [];

    public string $jsonRpcServiceName = '';

    public array $jsonRpcServiceParams = [];

    public string $phase2Url = '';
}