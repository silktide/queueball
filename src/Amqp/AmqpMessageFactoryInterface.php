<?php
/**
 * @package queueball
 */
namespace Silktide\QueueBall\Queue;

interface AmqpMessageFactoryInterface 
{

    /**
     * @param mixed $data
     *
     * @return AmqpMessageInterface
     */
    public function create($data);

} 