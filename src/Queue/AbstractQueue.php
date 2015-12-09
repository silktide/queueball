<?php

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

    /**
     * @var string
     */
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

    /**
     * @return string
     * @throws QueueException
     */
    public function getQueueId()
    {
        if (empty($this->queueId)) {
            throw new QueueException("No queue ID has been set");
        }
        return $this->queueId;
    }

    /**
     * @param string $queueId
     * @param int $messageLockTimeout
     * @param array $options
     */
    abstract public function createQueue($queueId, $messageLockTimeout = 0, $options = []);

    /**
     * @param string|null $queueId
     */
    abstract public function deleteQueue($queueId = null);

    /**
     * @param mixed $messageBody
     * @param string|null $queueId
     */
    abstract public function sendMessage($messageBody, $queueId = null);

    /**
     * @param string|null $queueId
     * @return QueueMessage
     */
    abstract public function receiveMessage($queueId = null);

    /**
     * @param QueueMessage $message
     */
    abstract public function completeMessage(QueueMessage $message);

    /**
     * @param QueueMessage $message
     */
    abstract public function returnMessage(QueueMessage $message);

} 