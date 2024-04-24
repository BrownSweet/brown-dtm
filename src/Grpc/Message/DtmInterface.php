<?php

declare(strict_types=1);
/**
 * This file is part of DTM-PHP.
 *
 * @license  https://github.com/dtm-php/dtm-client/blob/master/LICENSE
 */
# source: dtm.proto

namespace Dtm\Grpc\Message;

/**
 * The dtm service definition.
 *
 * Protobuf type <code>dtm.Dtm</code>
 */
interface DtmInterface
{
    /**
     * Method <code>newGid</code>.
     *
     * @return \Dtm\Grpc\Message\DtmGidReply
     */
    public function newGid(\Google\Protobuf\GPBEmpty $request);

    /**
     * Method <code>submit</code>.
     *
     * @param \Dtm\Grpc\Message\DtmRequest $request
     * @return \Google\Protobuf\GPBEmpty
     */
    public function submit(DtmRequest $request);

    /**
     * Method <code>prepare</code>.
     *
     * @param \Dtm\Grpc\Message\DtmRequest $request
     * @return \Google\Protobuf\GPBEmpty
     */
    public function prepare(DtmRequest $request);

    /**
     * Method <code>abort</code>.
     *
     * @param \Dtm\Grpc\Message\DtmRequest $request
     * @return \Google\Protobuf\GPBEmpty
     */
    public function abort(DtmRequest $request);

    /**
     * Method <code>registerBranch</code>.
     *
     * @param \Dtm\Grpc\Message\DtmBranchRequest $request
     * @return \Google\Protobuf\GPBEmpty
     */
    public function registerBranch(DtmBranchRequest $request);
}
