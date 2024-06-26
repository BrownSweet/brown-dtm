<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-client/blob/master/LICENSE
 */
# source: dtm.proto

namespace Dtm\Grpc\GPBMetadata;

class Dtm
{
    public static $is_initialized = false;

    public static function initOnce()
    {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
            return;
        }
        \GPBMetadata\Google\Protobuf\GPBEmpty::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
	dtm.protodtm"�
DtmTransOptions

WaitResult (

TimeoutToFail (

RetryInterval (
PassthroughHeaders (	>

BranchHeaders (2\'.dtm.DtmTransOptions.BranchHeadersEntry4
BranchHeadersEntry
key (	
value (	:8"�

DtmRequest
Gid (	
	TransType (	*
TransOptions (2.dtm.DtmTransOptions
CustomedData (	
BinPayloads (

QueryPrepared (	
Steps (	"
DtmGidReply
Gid (	"�
DtmBranchRequest
Gid (	
	TransType (	
BranchID (	

Op (	-
Data (2.dtm.DtmBranchRequest.DataEntry
BusiPayload (+
	DataEntry
key (	
value (	:82�
Dtm4
NewGid.google.protobuf.Empty.dtm.DtmGidReply" 3
Submit.dtm.DtmRequest.google.protobuf.Empty" 4
Prepare.dtm.DtmRequest.google.protobuf.Empty" 2
Abort.dtm.DtmRequest.google.protobuf.Empty" A
RegisterBranch.dtm.DtmBranchRequest.google.protobuf.Empty" B9�DtmClient\\Grpc\\Message��DtmClient\\Grpc\\GPBMetadatabproto3',
            true
        );

        static::$is_initialized = true;
    }
}
