# 介绍

[dtm/dtm-client](https://packagist.org/packages/dtm/dtm-client) 是分布式事务管理器 [DTM](https://github.com/dtm-labs/dtm) 的 PHP 客户端，已支持 TCC模式、Saga、XA、二阶段消息模式的分布式事务模式，并分别实现了与 DTM Server 以 HTTP 协议或 gRPC 协议通讯，该客户端可安全运行于 PHP-FPM 和 Swoole 协程环境中。





# 关于 DTM

DTM 是一款基于 Go 语言实现的开源分布式事务管理器，提供跨语言，跨存储引擎组合事务的强大功能。DTM 优雅的解决了幂等、空补偿、悬挂等分布式事务难题，也提供了简单易用、高性能、易水平扩展的分布式事务解决方案。



## 亮点

- 极易上手
  - 零配置启动服务，提供非常简单的 HTTP 接口，极大降低上手分布式事务的难度
- 跨语言
  - 可适合多语言栈的公司使用。方便 Go、Python、PHP、NodeJs、Ruby、C# 等各类语言使用。
- 使用简单
  - 开发者不再担心悬挂、空补偿、幂等各类问题，首创子事务屏障技术代为处理
- 易部署、易扩展
  - 仅依赖 MySQL/Redis，部署简单，易集群化，易水平扩展
- 多种分布式事务协议支持
  - TCC、SAGA、XA、二阶段消息，一站式解决多种分布式事务问题

## 对比

在非 Java 语言下，暂未看到除 DTM 之外的成熟的分布式事务管理器，因此这里将 DTM 和 Java 中最成熟的开源项目 Seata 做对比：

| 特性                                                         | DTM                                                          | SEATA                                                        | 备注                                     |
| ------------------------------------------------------------ | ------------------------------------------------------------ | ------------------------------------------------------------ | ---------------------------------------- |
| [支持语言](https://dtm.pub/other/opensource.html#lang)       | Go、C#、Java、Python、PHP...                                 | Java                                                         | DTM 可轻松接入一门新语言                 |
| [存储引擎](https://dtm.pub/other/opensource.html#store)      | 支持数据库、Redis、Mongo等                                   | 数据库                                                       |                                          |
| [异常处理](https://dtm.pub/other/opensource.html#exception)  | 子事务屏障自动处理                                           | 手动处理                                                     | DTM 解决了幂等、悬挂、空补偿             |
| [SAGA事务](https://dtm.pub/other/opensource.html#saga)       | 极简易用                                                     | 复杂状态机                                                   |                                          |
| [二阶段消息](https://dtm.pub/other/opensource.html#msg)      | ✓                                                            | ✗                                                            | 最简消息最终一致性架构                   |
| [TCC事务](https://dtm.pub/other/opensource.html#tcc)         | ✓                                                            | ✓                                                            |                                          |
| [XA事务](https://dtm.pub/other/opensource.html#xa)           | ✓                                                            | ✓                                                            |                                          |
| [AT事务](https://dtm.pub/other/opensource.html#at)           | 建议使用XA                                                   | ✓                                                            | AT 与 XA类似，但有脏回滚                 |
| [单服务多数据源](https://dtm.pub/other/opensource.html#multidb) | ✓                                                            | ✗                                                            |                                          |
| [通信协议](https://dtm.pub/other/opensource.html#protocol)   | HTTP、gRPC                                                   | Dubbo等协议                                                  | DTM对云原生更加友好                      |
| [star数量](https://dtm.pub/other/opensource.html#star)       | ![github stars](https://img.shields.io/github/stars/dtm-labs/dtm.svg?style=social) | ![github stars](https://img.shields.io/github/stars/seata/seata.svg?style=social) | DTM 从 2021-06-04 发布 0.1版本，发展飞快 |

从上面对比的特性来看，DTM 在许多方面都具备很大的优势。如果考虑多语言支持、多存储引擎支持，那么 DTM 毫无疑问是您的首选.



# 安装

通过 Composer 可以非常方便的安装 dtm-client

```bash
composer require brown/brown-dtm
```

- 使用时别忘了启动 DTM Server 哦





## 配置文件



```php
<?php

use Dtm\Constants\DbType;
use Dtm\Constants\Protocol;

return [
    'dtm'=>[
        'protocol' => Protocol::HTTP,  //选择接入的协议，目前仅支持http协议接入，grpc后续开发中
        'server' => '127.0.0.1', //dtm服务器地址
        'port' => [
            'http' => 36789,
            'grpc' => 36790,
        ],
        'barrier' => [
            'db' => [
                'type' => DbType::MySQL, //dtm引擎的类型 redis引擎正在开发中
            ],
            'apply' => [],
        ],
        'guzzle' => [
            'options' => [],
        ],
    ]
];
```



可复制 `./vendor/dtm/src/Config/dtm.php` 文件到对应的配置目录中。





# 使用

## TCC 模式

TCC 模式是一种非常流行的柔性事务解决方案，由 Try-Confirm-Cancel 三个单词的首字母缩写分别组成 TCC 的概念，最早是由 Pat Helland 于 2007 年发表的一篇名为《Life beyond Distributed Transactions:an Apostate’s Opinion》的论文中提出。

### TCC 的 3 个阶段

Try 阶段：尝试执行，完成所有业务检查（一致性）, 预留必须业务资源（准隔离性）
Confirm 阶段：如果所有分支的 Try 都成功了，则走到 Confirm 阶段。Confirm 真正执行业务，不作任何业务检查，只使用 Try 阶段预留的业务资源
Cancel 阶段：如果所有分支的 Try 有一个失败了，则走到 Cancel 阶段。Cancel 释放 Try 阶段预留的业务资源。

如果我们要进行一个类似于银行跨行转账的业务，转出（TransOut）和转入（TransIn）分别在不同的微服务里，一个成功完成的 TCC 事务典型的时序图如下：

![img](https://dtm.pub/assets/tcc_normal.dea14fb3.jpg)

### 代码示例



```php
<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

namespace app\controller;

use Dtm\Api\HttpApi;

use Dtm\Api\HttpApiFactory;
use Dtm\Saga;
use Dtm\TCC;
use Dtm\BranchIdGenerator;
use Dtm\Context\TransContext;
use GuzzleHttp\Client;
use think\Request;

class Index
{
    protected TCC $tcc;
    protected $url='https://gift.yourcharon.com/base-images-project';
    public function index(){
        $api=(new HttpApiFactory())->factory();  //获取httpapi实例
		$BranchIdGenerator=(new BranchIdGenerator()); //获取分支事务Id编号
        $this->tcc=new TCC($api,$BranchIdGenerator); 
        try {
            $this->tcc->globalTransaction(function (TCC $tcc){
                $tcc->callBranch(
                    ['account'=>30],     //事务数据
                    $this->url.'/trya',  //try方法
                    $this->url.'/confirma', //confirm方法
                    $this->url.'/cancela', //回滚方法
                );
                $tcc->callBranch(
                    ['account'=>30],
                    $this->url.'/tryb',
                    $this->url.'/confirmb',
                    $this->url.'/cancelb',
                );
            });
        }catch (\Throwable $e){
            print_r($e->getMessage());
            print_r($e->getFile());
            print_r($e->getLine());
        }
        echo '-----------';
        // 通过 TransContext::getGid() 获得 全局事务ID 并返回
//        echo TransContext::getGid();
        return TransContext::getGid();

    }
}
```



## Saga 模式

Saga 模式是分布式事务领域最有名气的解决方案之一，也非常流行于各大系统中，最初出现在 1987 年 由 Hector Garcaa-Molrna & Kenneth Salem 发表的论文 [SAGAS](https://www.cs.cornell.edu/andru/cs711/2002fa/reading/sagas.pdf) 里。

Saga 是一种最终一致性事务，也是一种柔性事务，又被叫做 长时间运行的事务（Long-running-transaction），Saga 是由一系列的本地事务构成。每一个本地事务在更新完数据库之后，会发布一条消息或者一个事件来触发 Saga 全局事务中的下一个本地事务的执行。如果一个本地事务因为某些业务规则无法满足而失败，Saga 会执行在这个失败的事务之前成功提交的所有事务的补偿操作。所以 Saga 模式在对比 TCC 模式时，因缺少了资源预留的步骤，往往在实现回滚逻辑时会变得更麻烦。

### Saga 子事务拆分

比如我们要进行一个类似于银行跨行转账的业务，将 A 账户中的 30 元转到 B 账户，根据 Saga 事务的原理，我们将整个全局事务，拆分为以下服务：

- 转出（TransOut）服务，这里将会进行操作 A 账户扣减 30 元
- 转出补偿（TransOutCompensate）服务，回滚上面的转出操作，即 A 账户增加 30 元
- 转入（TransIn）服务，这里将会进行 B 账户增加 30 元
- 转入补偿（TransInCompensate）服务，回滚上面的转入操作，即 B 账户减少 30 元

整个事务的逻辑是：

执行转出成功 => 执行转入成功 => 全局事务完成

如果在中间发生错误，例如转入 B 账户发生错误，则会调用已执行分支的补偿操作，即：

执行转出成功 => 执行转入失败 => 执行转入补偿成功 => 执行转出补偿成功 => 全局事务回滚完成

下面是一个成功完成的 SAGA 事务典型的时序图：

![img](https://dtm.pub/assets/saga_normal.a2849672.jpg)

### 代码示例

```php
<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time:
 */

namespace app\controller;

use Dtm\Api\HttpApi;

use Dtm\Api\HttpApiFactory;
use Dtm\Saga;
use Dtm\TCC;
use Dtm\BranchIdGenerator;
use Dtm\Context\TransContext;
use GuzzleHttp\Client;
use think\Request;

class Index
{
    protected TCC $tcc;
    protected $url='https://gift.yourcharon.com/base-images-project';
    public function index(){
        $api=(new HttpApiFactory())->factory();  //获取httpapi实例
		try {
            $data = ['amount' => 50];
            $saga=new Saga($api);
            $saga->init();

            $saga->add(
                $this->url.'/sagaout',
                $this->url.'/sagaoutCompensate',
                $data
            );

            $saga->add(
                $this->url.'/sagain',
                $this->url.'/sagainCompensate',
                $data
            );

            $saga->submit();

        }catch (\Throwable $exception){
            print_r($exception->getMessage());
            print_r($exception->getFile());
            print_r($exception->getLine());
        }
        echo TransContext::getGid();
        return TransContext::getGid();

    }
}
```

