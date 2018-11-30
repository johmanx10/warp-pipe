<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Johmanx10\WarpPipe;

use SplDoublyLinkedList;
use Throwable;

class Pipe implements PipeInterface
{
    /**
     * Invoke the operations in order.
     * Roll back operations in reverse order, from the point where a throwable
     * was caught.
     *
     * @param OperationInterface ...$operations
     *
     * @return void
     *
     * @throws OperationRolledBackException When operations are rolled back.
     */
    public function __invoke(OperationInterface ...$operations): void
    {
        $queue = $this->createQueue(...$operations);

        try {
            $this->process($queue);
        } catch (Throwable $exception) {
            throw new OperationRolledBackException(
                ...$this->rollback($exception, $queue)
            );
        }
    }

    /**
     * Rollback the given operations.
     *
     * @param Throwable                                $exception
     * @param SplDoublyLinkedList|OperationInterface[] $queue
     *
     * @return array|OperationFailureInterface[]
     *
     * @throws FailedRollbackException When an operation could not be rolled back.
     */
    private function rollback(
        Throwable $exception,
        SplDoublyLinkedList $queue
    ): array {
        // Reverse the iterator by telling it last in goes first out.
        $queue->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);

        // Do not rewind the iterator. It has to continue from where it left off.
        // @codingStandardsIgnoreLine
        for ($failures = []; $queue->valid(); $queue->next()) {
            /** @var OperationInterface $operation */
            $operation = $queue->current();
            $exception = null;

            try {
                $operation->rollback();
            } catch (Throwable $rollbackException) {
                throw new FailedRollbackException(
                    $operation,
                    0,
                    $rollbackException,
                    ...$failures
                );
            }

            $failures[] = new OperationFailure($operation, $exception);
        }

        return $failures;
    }

    /**
     * Process the queued operations.
     *
     * @param SplDoublyLinkedList|OperationInterface[] $queue
     *
     * @return void
     */
    private function process(SplDoublyLinkedList $queue): void
    {
        // Set the iterator to process operations in order.
        // First in goes first out and the queue is kept in-tact while traversing.
        $queue->setIteratorMode(
            SplDoublyLinkedList::IT_MODE_FIFO
            | SplDoublyLinkedList::IT_MODE_KEEP
        );

        // @codingStandardsIgnoreLine
        for ($queue->rewind(); $queue->valid(); $queue->next()) {
            /** @var OperationInterface $operation */
            $operation = $queue->current();
            $operation->__invoke();
        }
    }

    /**
     * Create a queue for the given operations.
     *
     * @param OperationInterface ...$operations
     *
     * @return SplDoublyLinkedList|OperationInterface[]
     */
    private function createQueue(
        OperationInterface ...$operations
    ): SplDoublyLinkedList {
        /** @var SplDoublyLinkedList|OperationInterface[] $queue */
        $queue = new SplDoublyLinkedList();

        foreach ($operations as $operation) {
            $queue->push($operation);
        }

        return $queue;
    }
}
