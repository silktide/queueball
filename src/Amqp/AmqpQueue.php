<?php
/**
 * @package queueball
 */
namespace Silktide\QueueBall\Queue;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Silktide\QueueBall\Message\QueueMessage;
use Silktide\QueueBall\Message\QueueMessageFactoryInterface;

class AmqpQueue extends AbstractQueue
{

    /**
     * @var AMQPStreamConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $channel;

    /**
     * @var QueueMessageFactoryInterface
     */
    protected $queueMessageFactory;

    /**
     * @var AmqpMessageFactoryInterface
     */
    protected $amqpMessageFactory;

    public function __construct(
        AMQPStreamConnection $connection,
        QueueMessageFactoryInterface $queueMessageFactory,
        AmqpMessageFactoryInterface $amqpMessageFactory,
        $channelId = null,
        $queueId = null
    ) {
        parent::__construct($queueId);
        $this->connection = $connection;
        $this->channel = $connection->channel($channelId);
        $this->queueMessageFactory = $queueMessageFactory;
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }

    /**
     * @param string $queueId
     * @param int $messageLockTimeout
     * @param array $options
     */
    function createQueue($queueId, $messageLockTimeout = 0, $options = [])
    {
        $this->channel->queue_declare($queueId);
    }

    /**
     * @param string|null $queueId
     */
    function deleteQueue($queueId = null)
    {
        if (empty($queueId)) {
            $queueId = $this->queueId;
        }
        $this->channel->queue_delete($queueId);
    }

    /**
     * @param mixed $messageBody
     * @param string|null $queueId
     */
    function sendMessage($messageBody, $queueId = null)
    {
        if (empty($queueId)) {
            $queueId = $this->queueId;
        }
        /** @var AmqpMessage $msg */
        $msg = $this->amqpMessageFactory->create($messageBody);
        $this->channel->basic_publish($msg, '', $queueId);
    }

    /**
     * @param string|null $queueId
     *
     * @return QueueMessage
     */
    function receiveMessage($queueId = null)
    {
        if (empty($queueId)) {
            $queueId = $this->queueId;
        }
        $queueMessage = $this->queueMessageFactory->createMessage([], $queueId);
        $this->channel->basic_consume($queueId, '', false, false, false, false, function (AMQPMessage $message) use ($queueMessage){
            $queueMessage->setMessage($message->body);
            $queueMessage->setReceiptId($message->delivery_info['delivery_tag']);
            $queueMessage->setArguments($message->get_properties());
        });

        while(!empty($this->channel->callbacks)) {
            $this->channel->wait();
        }

        return $queueMessage;
    }

    /**
     * @param QueueMessage $message
     */
    function completeMessage(QueueMessage $message)
    {
        $this->channel->basic_ack($message->getReceiptId());
    }

    /**
     * @param QueueMessage $message
     */
    function returnMessage(QueueMessage $message)
    {
        $this->channel->basic_nack($message->getReceiptId(), false, true);
    }

} 