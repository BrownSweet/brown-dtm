<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2023-05-15 10:01
 */

namespace Dtm\Api;

use Dtm\Constants\Operation;
use Dtm\Constants\Protocol;
use Dtm\Constants\Result;
use Dtm\Constants\TransType;
use Dtm\Exception\FailureException;
use Dtm\Exception\GenerateException;
use Dtm\Exception\OngingException;
use Dtm\Exception\RequestException;
use Dtm\Exception\RuntimeException;
use Dtm\Context\TransContext;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

class HttpApi implements ApiInterface
{
    protected Client $client;
    protected  $config;
    public function __construct(Client $client,$config)
    {
        $this->client=$client;
        $this->config=$config;
    }

//    public static function create($server,$port,$options){
//
//        $client = new Client(array_merge(
//            [
//                'base_uri' => empty($port) ? $server : $server . ':' . $port,
//            ],
//            $options
//        ));
//
//        return new static($client,array_merge(['port'=>$port,'server'=>$server],$options));
//    }
    public function getProtocol(): string
    {
        return Protocol::HTTP;
    }

    public function getProtocolHead(): string
    {
        return $this->getProtocol() . '://';
    }

    public function generateGid(): string
    {
        $url = sprintf('/api/dtmsvr/newGid');

        $response = $this->client->get($url)->getBody()->getContents();
        $responseContent = json_decode($response, true);
        if ($responseContent['dtm_result'] !== 'SUCCESS' || empty($responseContent['gid'])) {
            throw new GenerateException($responseContent['message'] ?? '');
        }
        return $responseContent['gid'];
    }

    public function prepare(array $body)
    {

        return $this->transCallDtm('POST', $body, Operation::PREPARE);
    }

    public function submit(array $body)
    {
        return $this->transCallDtm('POST', $body, Operation::SUBMIT);
    }

    public function abort(array $body)
    {
        return $this->transCallDtm('POST', $body, Operation::ABORT);
    }

    public function registerBranch(array $body)
    {
        return $this->transCallDtm('POST', $body, Operation::REGISTER_BRANCH);
    }

    public function query(array $body)
    {
        return $this->transQuery($body, Operation::QUERY);
    }

    public function queryAll(array $body)
    {
        return $this->transQuery($body, Operation::QUERY_ALL);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }

    public function transRequestBranch(RequestBranch $requestBranch)
    {
        $dtm = $this->getProtocolHead() . $this->config['server']. ':' .$this->config['port']['http'] . '/api/dtmsvr';
        $options = [
            RequestOptions::QUERY => [
                'dtm' => $dtm,
                'gid' => TransContext::getGid(),
                'branch_id' => $requestBranch->branchId,
                'trans_type' => TransContext::getTransType(),
                'op' => $requestBranch->op,
            ],
            RequestOptions::JSON => $requestBranch->body,
            RequestOptions::HEADERS => $requestBranch->branchHeaders,
        ];

        if (TransContext::getTransType() == TransType::XA) {
            $options[RequestOptions::QUERY]['phase2_url'] = $requestBranch->phase2Url;
        }

        $response = $this->client->request($requestBranch->method, $requestBranch->url, $options);

        if (Result::isOngoing($response)) {
            throw new OngingException();
        }
        if (Result::isFailure($response)) {
            throw new FailureException();
        }
        if (! Result::isSuccess($response)) {
            throw new RuntimeException($response->getReasonPhrase(), $response->getStatusCode());
        }

        return $response;
    }

    /**
     * @throws RequestException
     */
    protected function transCallDtm(string $method, array $body, string $operation, array $query = [])
    {
        try {
            $url = sprintf('/api/dtmsvr/%s', $operation);

            $response = $this->getClient()->request($method, $url, [
                'json' => $body,
                'query' => $query,
            ]);

            if (! Result::isSuccess($response)) {
                throw new RequestException($response->getReasonPhrase(), $response->getStatusCode());
            }
        } catch (GuzzleException $exception) {
            throw new RequestException($exception->getMessage(), $exception->getCode(), $exception);
        }
        return $response;
    }

    protected function transQuery(array $query, string $operation)
    {
        try {
            $url = sprintf('/api/dtmsvr/%s', $operation);
            $response = $this->getClient()->get($url, [
                'query' => $query,
            ]);
            if (! Result::isSuccess($response)) {
                throw new RequestException($response->getReasonPhrase(), $response->getStatusCode());
            }
        } catch (GuzzleException $exception) {
            throw new RequestException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $response;
    }



}
