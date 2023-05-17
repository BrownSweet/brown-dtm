<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-client/blob/master/LICENSE
 */
namespace Dtm\Api;

use brown\server\core\Application;
use Dtm\Api\HttpApi;

use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;

class HttpApiFactory
{
    use Application;

    public  function factory(){

        $dtm=$this->getConfig('dtm');
        $server=$dtm['server'];
        $port=$dtm['port']['http'];
        $options = $this->getConfig('dtm.guzzle.options',[]);

        $client = new Client(array_merge(
            [
                'base_uri' => empty($port) ? $server : $server . ':' . $port,
            ],
            $options
        ));
        return new HttpApi($client, $dtm);
    }

}
