<?php
/**
 * Silktide Nibbler. Copyright 2013-2014 Silktide Ltd. All Rights Reserved.
 */
namespace Silktide\QueueBall\Message;

/**
 *
 */
interface QueueMessageFactoryInterface
{

    /**
     * @param array $message
     * @param $queueId
     * @return mixed
     */
    public function createMessage(array $message, $queueId);

} 