<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Johmanx10\Transaction\Formatter;

use Johmanx10\Transaction\OperationFailureInterface;

class OperationFailureFormatter implements OperationFailureFormatterInterface
{
    /** @var OperationFormatterInterface */
    private $operationFormatter;

    /** @var ExceptionFormatterInterface */
    private $exceptionFormatter;

    /**
     * Constructor.
     *
     * @param OperationFormatterInterface|null $operationFormatter
     * @param ExceptionFormatterInterface|null $exceptionFormatter
     */
    public function __construct(
        OperationFormatterInterface $operationFormatter = null,
        ExceptionFormatterInterface $exceptionFormatter = null
    ) {
        $this->operationFormatter = (
            $operationFormatter ?? new OperationFormatter()
        );
        $this->exceptionFormatter = (
            $exceptionFormatter ?? new ExceptionFormatter()
        );
    }

    /**
     * Format the operation failure into a readable string.
     *
     * @param OperationFailureInterface $failure
     *
     * @return string
     */
    public function format(OperationFailureInterface $failure): string
    {
        $exception  = $failure->getException();
        $operation  = $failure->getOperation();
        $identifier = str_pad(
            sprintf('(%d)', spl_object_id($operation)),
            8,
            ' ',
            STR_PAD_RIGHT
        );

        return $exception !== null
            ? sprintf(
                '%s ∴ %s',
                $identifier,
                $this->exceptionFormatter->format($exception)
            )
            : sprintf(
                '%s ✔ %s',
                $identifier,
                $this->operationFormatter->format($operation)
            );
    }
}
