<?php
/**
 * @package queueball
 */
namespace Silktide\QueueBall\Queue;

class AmqpMessageFactory implements AmqpMessageFactoryInterface
{
    /**
     * @param mixed $data
     *
     * @return AmqpMessageInterface
     */
    public function create($data)
    {
        return new AmqpMessage($data);
    }

} 