<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2023-05-12 09:57
 */

namespace dtm\api;

interface ApiInterface
{
    public function getProtocol(): string;

    public function generateGid(): string;

    public function prepare(array $body);

    public function submit(array $body);

    public function abort(array $body);

    public function registerBranch(array $body);

    public function query(array $body);

    public function queryAll(array $body);

    public function transRequestBranch(RequestBranch $requestBranch);
}