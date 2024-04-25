<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-client/blob/master/LICENSE
 */
namespace Dtm\Grpc;

use brown\server\core\Application;
use Psr\Container\ContainerInterface;

class GrpcClientManagerFactory
{
    use Application;

    public  function factory(){
            $manager = new GrpcClientManager();
            $server = $this->getConfig('dtm.server', '127.0.0.1');
            $port = $this->getConfig('dtm.port.grpc', 36790);
            $hostname = $server . ':' . $port;
            $manager->addClient($hostname, new GrpcClient($hostname));
            return $manager;
        }

}
