<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-client/blob/master/LICENSE
 */
namespace Dtm\Api;

use Dtm\Grpc\Message\DtmBranchRequest;
use Dtm\Grpc\Message\DtmRequest;
use Dtm\Grpc\Message\DtmTransOptions;
use Dtm\Constants\Operation;
use Dtm\Constants\Protocol;
use Dtm\Constants\Result;
use Dtm\Exception\FailureException;
use Dtm\Exception\OngingException;
use Dtm\Exception\RequestException;
use Dtm\Exception\UnsupportedException;
use Dtm\Grpc\GrpcClient;
use Dtm\Grpc\GrpcClientManager;
use Hyperf\Contract\ConfigInterface;

class GrpcApi implements ApiInterface
{
    protected ConfigInterface $config;

    protected GrpcClientManager $grpcClientManager;

    public function __construct(ConfigInterface $config, GrpcClientManager $grpcClientManager)
    {
        $this->config = $config;
        $this->grpcClientManager = $grpcClientManager;
    }

    public function getProtocol(): string
    {
        return Protocol::GRPC;
    }

    public function generateGid(): string
    {
        $gidReply = $this->getDtmClient()->newGid();
        return $gidReply->getGid();
    }

    public function prepare(array $body)
    {
        $dtmRequest = $this->transferToRequest($body);
        $this->getDtmClient()->transCallDtm($dtmRequest, Operation::PREPARE);
    }

    public function submit(array $body)
    {
        $dtmRequest = $this->transferToRequest($body);
        $this->getDtmClient()->transCallDtm($dtmRequest, Operation::SUBMIT);
    }

    public function abort(array $body)
    {
        $dtmRequest = $this->transferToRequest($body);
        $this->getDtmClient()->transCallDtm($dtmRequest, Operation::ABORT);
    }

    public function registerBranch(array $body)
    {
        $dtmRequest = new DtmBranchRequest($body);
        $this->getDtmClient()->transCallDtm($dtmRequest, Operation::REGISTER_BRANCH);
    }

    public function query(array $body)
    {
        throw new UnsupportedException('Unsupported Query operation');
    }

    public function queryAll(array $body)
    {
        throw new UnsupportedException('Unsupported QueryAll operation');
    }

    public function transRequestBranch(RequestBranch $requestBranch)
    {
        [$hostname, $method] = $this->parseHostnameAndMethod($requestBranch->url);
        $client = $this->grpcClientManager->getClient($hostname);

        [$reply, $status, $response] = $client->invoke($method, $requestBranch->grpcArgument, $requestBranch->grpcDeserialize, $requestBranch->grpcMetadata, $requestBranch->grpcOptions);

        if (Result::ONGOING_STATUS == $status) {
            throw new OngingException();
        }
        if (Result::FAILURE_STATUS == $status) {
            throw new FailureException();
        }
        if ($status !== Result::OK_STATUS) {
            throw new RequestException($reply->serializeToString(), $status);
        }

        return [$reply, $status, $response];
    }

    protected function transferToRequest(array $body): DtmRequest
    {
        $request = new DtmRequest();
        isset($body['gid']) && $request->setGid($body['gid']);
        isset($body['trans_type']) && $request->setTransType($body['trans_type']);
        isset($body['custom_data']) && $request->setCustomedData($body['custom_data']);
        isset($body['bin_payloads']) && $request->setBinPayloads($body['bin_payloads']);
        isset($body['query_prepared']) && $request->setQueryPrepared($body['query_prepared']);
        isset($body['steps']) && $request->setSteps(json_encode($body['steps']));
        $dtmTransOptions = $this->transferToTransOptions($body);
        $dtmTransOptions && $request->setTransOptions($dtmTransOptions);
        return $request;
    }

    protected function transferToTransOptions(array $body): ?DtmTransOptions
    {
        if (! isset($body['wait_result'], $body['timeout_to_fail'], $body['retry_interval'], $body['passthrough_headers'], $body['branch_headers'])) {
            return null;
        }
        $request = new DtmTransOptions();
        isset($body['wait_result']) && $request->setWaitResult($body['wait_result']);
        isset($body['timeout_to_fail']) && $request->setTimeoutToFail($body['timeout_to_fail']);
        isset($body['retry_interval']) && $request->setRetryInterval($body['retry_interval']);
        isset($body['passthrough_headers']) && $request->setPassthroughHeaders($body['passthrough_headers']);
        isset($body['branch_headers']) && $request->setBranchHeaders($body['branch_headers']);
        return $request;
    }

    protected function parseHostnameAndMethod(string $url): array
    {
        $path = explode('/', $url);
        $hostname = $path[0];
        array_shift($path);
        $method = implode('/', $path);
        return [$hostname, $method];
    }

    protected function getDtmClient(): GrpcClient
    {
        $server = $this->config->get('dtm.server', '127.0.0.1');
        $port = $this->config->get('dtm.port.grpc', 36790);
        $hostname = $server . ':' . $port;
        return $this->grpcClientManager->getClient($hostname);
    }
}
