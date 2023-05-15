<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2023-05-13 09:00
 */

namespace Dtm;

use Dtm\Api\ApiInterface;
use Dtm\Api\RequestBranch;
use Dtm\Constants\Operation;
use Dtm\Constants\Protocol;
use Dtm\Constants\TransType;
use Dtm\Exception\InvalidArgumentException;
use Dtm\Exception\UnsupportedException;
use Dtm\BranchIdGeneratorInterface;
use Dtm\Context\TransContext;

class TCC extends AbstractTransaction
{
    protected BranchIdGeneratorInterface $branchIdGenerator;

    public function __construct(ApiInterface $api,BranchIdGeneratorInterface $branchIdGenerator)
    {
        $this->api=$api;
        $this->branchIdGenerator=$branchIdGenerator;
    }

    public function init(?string $gid = null)
    {
        if ($gid === null) {
            $gid = $this->generateGid();
        }
        TransContext::init($gid, TransType::TCC, '');
    }

    public function globalTransaction(callable $callback, ?string $gid = null)
    {
        $this->init($gid);
        $requestBody = TransContext::toArray();

        try {
            $a=$this->api->prepare($requestBody);

            $callback($this);
        } catch (\Throwable $throwable) {

            $this->api->abort($requestBody);
            throw $throwable;
        }
        $this->api->submit($requestBody);
    }

    /**
     * @param array|Message $body
     */
    public function callBranch($body, string $tryUrl, string $confirmUrl, string $cancelUrl)
    {
        $branchId = $this->branchIdGenerator->generateSubBranchId();

        switch ($this->api->getProtocol()) {
            case Protocol::JSONRPC_HTTP:
            case Protocol::HTTP:
                $this->api->registerBranch([
                    'data' => json_encode($body),
                    'branch_id' => $branchId,
                    'confirm' => $confirmUrl,
                    'cancel' => $cancelUrl,
                    'gid' => TransContext::getGid(),
                    'trans_type' => TransType::TCC,
                ]);

                $branchRequest = new RequestBranch();
                $branchRequest->method = 'POST';
                $branchRequest->url = $tryUrl;
                $branchRequest->branchId = $branchId;
                $branchRequest->op = Operation::TRY;
                $branchRequest->body = $body;
                return $this->api->transRequestBranch($branchRequest);
            case Protocol::GRPC:
                if (! $body instanceof Message) {
                    throw new InvalidArgumentException('$body must be instance of Message');
                }
                $formatBody = [
                    'Gid' => TransContext::getGid(),
                    'TransType' => TransType::TCC,
                    'BranchID' => $branchId,
                    'BusiPayload' => $body->serializeToString(),
                    'Data' => ['confirm' => $confirmUrl, 'cancel' => $cancelUrl],
                ];
                $this->api->registerBranch($formatBody);
                $branchRequest = new RequestBranch();
                $branchRequest->grpcArgument = $body;
                $branchRequest->url = $tryUrl;
                $branchRequest->grpcMetadata = [
                    'dtm-gid' => $formatBody['Gid'],
                    'dtm-trans_type' => $formatBody['TransType'],
                    'dtm-branch_id' => $formatBody['BranchID'],
                    'dtm-op' => Operation::TRY,
                    'dtm-dtm' => TransContext::getDtm(),
                ];
                return $this->api->transRequestBranch($branchRequest);
            default:
                throw new UnsupportedException('Unsupported protocol');
        }
    }
}