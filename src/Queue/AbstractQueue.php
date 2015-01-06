<?php
/**
 * Silktide Nibbler. Copyright 2013-2014 Silktide Ltd. All Rights Reserved.
 */
namespace Silktide\QueueBall\Queue;
use Silktide\QueueBall\Exception\QueueException;
use Silktide\QueueBall\Message\QueueMessage;

/**
 * Abstract class for encapsulating queue functionality
 *
 * stores queue ID to prevent having to pass the value through every time
 *
 */
abstract class AbstractQueue
{

    const DEFAULT_MESSAGE_LOCK_TIMEOUT = 120;

    protected $queueId;

    /**
     * @param string $queueId
     */
    public function __construct($queueId = null)
    {
        $this->setQueueId($queueId);
    }

    /**
     * @param string $queueId
     */
    public function setQueueId($queueId)
    {
        $this->queueId = $queueId;
    }

    public function getQueueId()
    {
        if (empty($this->queueId)) {
            throw new QueueException("No queue ID has been set");
        }
        return $this->queueId;
    }

    /**
     * @param string $queueId
     * @param array $options
     */
    abstract function createQueue($queueId, $messageLockTimeout = 0, $options = []);

    /**
     * @param string|null $queueId
     */
    abstract function deleteQueue($queueId = null);

    /**
     * @param QueueMessage $message
     * @param string|null $queueId
     */
    abstract function sendMessage($messageBody, $queueId = null);

    /**
     * @param string|null $queueId
     * @return QueueMessage
     */
    abstract function receiveMessage($queueId = null);

    /**
     * @param QueueMessage $message
     */
    abstract function completeMessage(QueueMessage $message);

    /**
     * @param QueueMessage $message
     */
    abstract function returnMessage(QueueMessage $message);

} 