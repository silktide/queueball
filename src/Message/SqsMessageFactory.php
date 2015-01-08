<?php
/**
 * Silktide Nibbler. Copyright 2013-2014 Silktide Ltd. All Rights Reserved.
 */
namespace Silktide\QueueBall\Message;
use Silktide\QueueBall\Exception\QueueException;

/**
 *
 */
class SqsMessageFactory implements QueueMessageFactoryInterface
{

    /**
     * {@inheritDoc}
     * @return QueueMessage
     * @throws \Silktide\QueueBall\Exception\QueueException
     */
    public function createMessage(array $message, $queueId)
    {
        if (empty($message["Message"][0])) {
            throw new QueueException("Queue message is not in valid SQS message format");
        }
        $message = $message["Message"][0];

        if (empty($message["MessageId"]) || empty($message["Body"]) || empty($message["ReceiptHandle"])) {
            throw new QueueException("SQS message has missing information");
        }

        $queueMessage = new QueueMessage();
        $queueMessage->setId($message["MessageId"]);
        $queueMessage->setMessage(json_decode($message["Body"], true));
        $queueMessage->setReceiptId($message["ReceiptHandle"]);
        $queueMessage->setQueueId($queueId);

        if (!empty($message["Attributes"]) || !empty($message["MessageAttributes"])) {
            $attributes = empty($message["Attributes"])? []: $message["Attributes"];
            $attributes = array_merge($attributes, empty($message["MessageAttributes"])? []: $message["MessageAttributes"]);
            $queueMessage->setAttributes($attributes);
        }

        return $queueMessage;
    }

} 