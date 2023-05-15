<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2023-05-12 09:58
 */

namespace dtm\api;

class RequestBranch
{
    public string $method;

    public array $body = [];

    public string $branchId;

    public string $op;

    public string $url;

    public array $branchHeaders = [];

}