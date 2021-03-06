<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Johmanx10\Transaction\Exception;

use Throwable;

interface OperationExceptionFactoryInterface
{
    /**
     * Create an operation exception from the given throwable.
     *
     * @param Throwable $subject
     *
     * @return OperationExceptionInterface
     */
    public function createFromThrowable(
        Throwable $subject
    ): OperationExceptionInterface;
}
