<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-client/blob/master/LICENSE
 */

namespace Dtm;

use Dtm\Api\ApiInterface;
use Dtm\Constants\Protocol;
use Dtm\Constants\TransType;
use Dtm\Context\TransContext;
use Dtm\Exception\UnsupportedException;
use Google\Protobuf\Internal\Message;

class Saga extends AbstractTransaction
{
    protected array $orders = [];

    protected bool $concurrent = false;

    protected ApiInterface $api;

    public function __construct(ApiInterface $api)
    {
        $this->api = $api;
    }

    public function init(?string $gid = null)
    {
        if ($gid === null) {
            $gid = $this->generateGid();
        }
        TransContext::init($gid, TransType::SAGA, '');
    }

    public function add(string $action, string $compensate,  $payload)
    {
        TransContext::addStep([
            'action' => $action,
            'compensate' => $compensate,
        ]);
        switch ($this->api->getProtocol()) {
            case Protocol::HTTP:
            case Protocol::JSONRPC_HTTP:
                TransContext::addPayload([json_encode($payload)]);
                break;
            case Protocol::GRPC:
                /* @var $payload Message */
                TransContext::addBinPayload([$payload->serializeToString()]);
                break;
            default:
                throw new UnsupportedException('Unsupported protocol');
        }

        return $this;
    }

    public function addBranchOrder(int $branch, array $preBranches)
    {
        $this->orders[$branch] = $preBranches;
        return $this;
    }

    public function enableConcurrent()
    {
        $this->concurrent = true;
    }

    public function submit()
    {
        $this->addConcurrentContext();
        return $this->api->submit(TransContext::toArray());
    }

    public function addConcurrentContext()
    {
        if ($this->concurrent) {
            TransContext::setCustomData(json_encode([
                'concurrent' => $this->concurrent,
                'orders' => $this->orders ?: null,
            ]));
        }
    }
}
