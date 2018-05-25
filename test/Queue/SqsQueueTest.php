<?php
/**
 * Silktide Nibbler. Copyright 2013-2014 Silktide Ltd. All Rights Reserved.
 */
namespace Silktide\QueueBall\Test\Queue;
use PHPUnit\Framework\TestCase;
use Silktide\QueueBall\Exception\QueueException;
use Silktide\QueueBall\Queue\AbstractQueue;
use Silktide\QueueBall\Queue\SqsQueue;
use Aws\Sqs\SqsClient;
use Silktide\QueueBall\Message\QueueMessageFactoryInterface;
use Silktide\QueueBall\Message\QueueMessage;

/**
 *
 */
class SqsQueueTest extends TestCase
{
    /**
     * @var SqsClient
     */
    protected $sqsClient;

    /**
     * @var QueueMessageFactoryInterface
     */
    protected $messageFactory;

    /**
     * @var QueueMessage
     */
    protected $queueMessage;

    protected $queueUrl;

    protected $queueId = "queue";

    protected $receiptId = "receipt";

    public function setUp()
    {

        $this->queueUrl = "http://queue.com";
        $urlReturn = \Mockery::mock("Guzzle\\Service\\Resource\\Model")->shouldReceive("get")->with("QueueUrl")->andReturn($this->queueUrl)->getMock();

        $this->sqsClient = \Mockery::mock("Aws\\Sqs\\SqsClient");
        $this->sqsClient->shouldReceive("getQueueUrl")->andReturn($urlReturn);

        $this->queueMessage = \Mockery::mock("Silktide\\QueueBall\\Message\\QueueMessage");
        $this->queueMessage->shouldReceive(
            [
                "getQueueId" => $this->queueId,
                "getReceiptId" => $this->receiptId
            ]
        );

        $this->messageFactory = \Mockery::mock("Silktide\\QueueBall\\Message\\QueueMessageFactoryInterface");
    }

    public function tearDown()
    {
        \Mockery::close();
    }

    public function testQueueId()
    {
        // no queueId
        $queue = new SqsQueue($this->sqsClient, $this->messageFactory);

        try {
            $queue->getQueueId();
            $this->fail("Shouldn't be able to get a queue Id that hasn't been set");
        } catch (QueueException $e) {
            $this->assertEquals("No queue ID has been set", $e->getMessage());
        }

        $queue = new SqsQueue($this->sqsClient, $this->messageFactory, $this->queueId);

        $this->assertEquals($this->queueId, $queue->getQueueId());
    }

    /**
     * @dataProvider timeoutProvider
     *
     * @param $timeout
     * @param $expectedTimeout
     */
    public function testCreateQueue($timeout, $expectedTimeout)
    {
        $queue = new SqsQueue($this->sqsClient, $this->messageFactory);

        $expectedArg = [
            "QueueName" => $this->queueId,
            "Attributes" => [
                "VisibilityTimeout" => $expectedTimeout
            ]
        ];

        $this->sqsClient->shouldReceive("createQueue")->atLeast()->times(1)->with($expectedArg);

        $queue->createQueue($this->queueId, $timeout);
        $this->assertEquals($this->queueId, $queue->getQueueId());
    }

    public function timeoutProvider()
    {
        return [
            [ // explicit timeout
                30,
                30
            ],
            [ // default timeout
                null,
                AbstractQueue::DEFAULT_MESSAGE_LOCK_TIMEOUT
            ],
            [ // default when timeout is invalid
                "NAN",
                AbstractQueue::DEFAULT_MESSAGE_LOCK_TIMEOUT
            ]
        ];
    }

    public function testDeleteQueue()
    {
        $expectedArg = [
            "QueueUrl" => $this->queueUrl
        ];

        $this->sqsClient->shouldReceive("deleteQueue")->twice()->with($expectedArg);


        $queue = new SqsQueue($this->sqsClient, $this->messageFactory);
        try {
            $queue->deleteQueue();
            $this->fail("should not be able to delete a queue without setting or passing a queue ID");
        } catch (QueueException $e) {
            $this->assertEquals("No queue ID has been set", $e->getMessage());
        }

        // test deleting, passing a queueId
        $queue->deleteQueue($this->queueId);
        $this->assertAttributeEquals($this->queueUrl, "queueUrl", $queue);

        // set the QueueId on the queue (check the URL is reset)
        $queue->setQueueId($this->queueId);
        $this->assertAttributeEquals(null, "queueUrl", $queue);

        // delete the queue implicitly
        $queue->deleteQueue();
        $this->assertAttributeEquals($this->queueUrl, "queueUrl", $queue);

    }

    public function testSendMessage()
    {
        $queue = new SqsQueue($this->sqsClient, $this->messageFactory, $this->queueId);

        $message = "message";
        $expectedArg = [
            "QueueUrl" => $this->queueUrl,
            "MessageBody" => json_encode($message)
        ];
        $this->sqsClient->shouldReceive("sendMessage")->with($expectedArg)->once();
        $queue->sendMessage($message, $this->queueId);
        $this->assertTrue(true);
    }

    public function testReceiveMessage()
    {
        $messageArray = [1, 2, 3];

        $message = \Mockery::mock("Guzzle\\Common\\Collection")->shouldReceive("toArray")->andReturn($messageArray)->getMock();
        $this->sqsClient->shouldReceive("receiveMessage")->with(["QueueUrl" => $this->queueUrl, "WaitTimeSeconds" => 0])->andReturn($message);
        $this->messageFactory->shouldReceive("createMessage")->with($messageArray, $this->queueId)->andReturn(true);

        $queue = new SqsQueue($this->sqsClient, $this->messageFactory, $this->queueId);

        $this->assertTrue($queue->receiveMessage());
    }

    public function testCompleteMessage()
    {
        $expectedArg = [
            "QueueUrl" => $this->queueUrl,
            "ReceiptHandle" => $this->receiptId
        ];
        $this->sqsClient->shouldReceive("deleteMessage")->with($expectedArg)->once();

        $queue = new SqsQueue($this->sqsClient, $this->messageFactory);
        $queue->completeMessage($this->queueMessage);
        $this->assertTrue(true);
    }

    public function testReturnMessage()
    {
        $expectedArg = [
            "QueueUrl" => $this->queueUrl,
            "ReceiptHandle" => $this->receiptId,
            "VisibilityTimeout" => 0
        ];
        $this->sqsClient->shouldReceive("changeMessageVisibility")->with($expectedArg)->once();

        $queue = new SqsQueue($this->sqsClient, $this->messageFactory);
        $queue->returnMessage($this->queueMessage);
        $this->assertTrue(true);
    }

}
 