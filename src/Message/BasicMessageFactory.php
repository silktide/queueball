<?php
/**
 * @package queueball
 */

namespace Silktide\QueueBall\Message;

class BasicMessageFactory implements QueueMessageFactoryInterface
{
    /**
     * @param $message
     * @param $queueId
     *
     * @return mixed
     */
    public function createMessage($message, $queueId)
    {
        $queueMessage = new QueueMessage();
        $queueMessage->setMessage($message);
        $queueMessage->setQueueId($queueId);
        return $queueMessage;
    }

} 