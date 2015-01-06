<?php
/**
 * Silktide Nibbler. Copyright 2013-2014 Silktide Ltd. All Rights Reserved.
 */
namespace Silktide\QueueBall\Test\Message;
use Silktide\QueueBall\Exception\QueueException;
use Silktide\QueueBall\Message\SqsMessageFactory;

/**
 *
 */
class SqsMessageFactoryTest extends \PHPUnit_Framework_TestCase {

    public function testExceptions()
    {
        $factory = new SqsMessageFactory();

        // malformed data
        $data = [];
        $queueId = "queue";
        try {
            $factory->createMessage($data, $queueId);
            $this->fail("Should not be able to create a message with no data");
        } catch (QueueException $e) {
            $this->assertEquals("Queue message is not in valid SQS message format", $e->getMessage());
        }

        // missing data
        $messageId = "id";
        $body = "body";
        $receiptId = "receiptId";

        $data = [
            "Message" => [
                [
                    "MessageId" => $messageId,
                    "Body" => $body,
                    "ReceiptHandle" => $receiptId
                ]
            ]
        ];
        foreach ($data["Message"][0] as $field => $value) {
            $testData = $data;
            unset($testData["Message"][0][$field]);
            try {
                $factory->createMessage($testData, $queueId);
                $this->fail("Should not be able to create a message without data for '$field'");
            } catch (QueueException $e) {
                $this->assertEquals("SQS message has missing information", $e->getMessage());
            }
        }

    }

    public function testRequiredDataMapping()
    {
        $factory = new SqsMessageFactory();

        $expected = [
            "QueueId" => "queue",
            "Id" => "id",
            "Message" => "body",
            "ReceiptId" => "receiptId"
        ];

        $data = [
            "Message" => [
                [
                    "MessageId" => $expected["Id"],
                    "Body" => json_encode($expected["Message"]),
                    "ReceiptHandle" => $expected["ReceiptId"]
                ]
            ]
        ];

        $queueMessage = $factory->createMessage($data, $expected["QueueId"]);
        $this->assertInstanceOf("Silktide\\QueueBall\\Message\\QueueMessage", $queueMessage);

        foreach ($expected as $property => $value) {
            $this->assertEquals($value, $queueMessage->{"get" . $property}());
        }
    }

    /**
     * @dataProvider attributeProvider
     *
     * @param $messageData
     * @param $expected
     */
    public function testAttributeMapping($messageData, $expected)
    {
        $factory = new SqsMessageFactory();

        $data = [
            "Message" => [
                [
                    "MessageId" => "blah",
                    "Body" => "\"blah\"",
                    "ReceiptHandle" => "blah"
                ]
            ]
        ];

        $data["Message"][0] = array_merge($data["Message"][0], $messageData);

        $queueMessage = $factory->createMessage($data, "queue");
        $this->assertEquals($expected, $queueMessage->getAttributes());

    }

    public function attributeProvider()
    {
        return [
            [
                [],
                []
            ],
            [
                [
                    "Attributes" => [1, 2, 3]
                ],
                [1, 2, 3]
            ],
            [
                [
                    "MessageAttributes" => [4, 5, 6]
                ],
                [4, 5, 6]
            ],
            [
                [
                    "Attributes" => ["one" => 1, "two" => 2, "three" => 3],
                    "MessageAttributes" => ["two" => 22, "four" => 4]
                ],
                ["one" => 1, "two" => 22, "three" => 3, "four" => 4]
            ]
        ];
    }

}
 