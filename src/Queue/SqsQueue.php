<?php
/**
 * Silktide Nibbler. Copyright 2013-2014 Silktide Ltd. All Rights Reserved.
 */
namespace Silktide\QueueBall\Queue;

use Aws\Sqs\SqsClient;
use Silktide\QueueBall\Message\QueueMessage;
use Silktide\QueueBall\Queue\AbstractQueue;
use Silktide\QueueBall\Exception\QueueException;
use Silktide\QueueBall\Message\QueueMessageFactoryInterface;

/**
 *
 */
class SqsQueue extends AbstractQueue
{

    /**
     * @var string
     */
    protected $queueUrl;

    /**
     * @var SqsClient
     */
    protected $queueClient;

    protected $messageFactory;

    public function __construct(SqsClient $sqsClient, QueueMessageFactoryInterface $messageFactory, $queueId = null)
    {
        parent::__construct($queueId);
        $this->queueClient = $sqsClient;
        $this->messageFactory = $messageFactory;
    }

    public function setQueueId($queueId)
    {
        parent::setQueueId($queueId);
        $this->queueUrl = null;
    }

    protected function getQueueUrl($queueId)
    {
        if (empty($this->queueUrl)) {
            if (empty($queueId)) {
                $queueId = $this->getQueueId();
            }
            $response = $this->queueClient->getQueueUrl(["QueueName" => $queueId]);
            $this->queueUrl = $response->get("QueueUrl");
        }
        return $this->queueUrl;
    }

    public function createQueue($queueId, $messageLockTimeout = 0, $options = [])
    {
        $timeout = (int) $messageLockTimeout;
        $attributes = [
            "VisibilityTimeout" => empty($timeout)? self::DEFAULT_MESSAGE_LOCK_TIMEOUT: $timeout
        ];
        $this->queueClient->createQueue([
            "QueueName" => $queueId,
            "Attributes" => $attributes
        ]);
        $this->setQueueId($queueId);
    }

    public function deleteQueue($queueId = null)
    {
        $queueUrl = $this->getQueueUrl($queueId);
        $this->queueClient->deleteQueue(["QueueUrl" => $queueUrl]);
    }

    public function sendMessage($messageBody, $queueId = null)
    {
        $queueUrl = $this->getQueueUrl($queueId);
        $this->queueClient->sendMessage([
            "QueueUrl" => $queueUrl,
            "MessageBody" => json_encode($messageBody)
        ]);
    }

    public function receiveMessage($queueId = null)
    {
        if (empty($queueId)) {
            // have to do this here as we need the ID later in this method
            $queueId = $this->getQueueId();
        }
        $queueUrl = $this->getQueueUrl($queueId);
        $message = $this->queueClient->receiveMessage(["QueueUrl" => $queueUrl]);
        return $this->messageFactory->createMessage($message->toArray(), $queueId);

    }

    public function completeMessage(QueueMessage $message)
    {
        $queueUrl = $this->getQueueUrl($message->getQueueId());
        $this->queueClient->deleteMessage([
            "QueueUrl" => $queueUrl,
            "ReceiptHandle" => $message->getReceiptId()
        ]);
    }

    public function returnMessage(QueueMessage $message)
    {
        $queueUrl = $this->getQueueUrl($message->getQueueId());
        $this->queueClient->changeMessageVisibility([
            "QueueUrl" => $queueUrl,
            "ReceiptHandle" => $message->getReceiptId(),
            "VisibilityTimeout" => 0
        ]);
    }

} 