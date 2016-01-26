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

    /**
     * @var string
     */
    protected $queueId;

    protected $waitTime = 0;

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
     * @param int $seconds
     * @throws \Exception
     */
    public function setWaitTime($seconds)
    {
        $seconds = (int) $seconds;
        if ($seconds < 0 || 20 < $seconds) {
            throw new \Exception("WaitTime must be a period between 0-20 seconds");
        }
        $this->waitTime = $seconds;
    }

    /**
     * @param string $queueId
     * @param array $options
     */
    abstract public function createQueue($queueId, $options = []);

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
     * @param int|null $waitTime
     * @return QueueMessage
     */
    abstract public function receiveMessage($queueId = null, $waitTime = null);

    /**
     * @param QueueMessage $message
     */
    abstract public function completeMessage(QueueMessage $message);

    /**
     * @param QueueMessage $message
     */
    abstract public function returnMessage(QueueMessage $message);

} 