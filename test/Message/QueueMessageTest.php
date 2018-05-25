<?php
/**
 * Silktide Nibbler. Copyright 2013-2014 Silktide Ltd. All Rights Reserved.
 */
namespace Silktide\QueueBall\Test\Message;
use PHPUnit\Framework\TestCase;
use Silktide\QueueBall\Message\QueueMessage;

/**
 *
 */
class QueueMessageTest extends TestCase
{

    /**
     * @dataProvider properties
     *
     * @param string $property
     * @param string|null $type
     */
    public function testProperty($property, $type = null)
    {
        $message = new QueueMessage();
        switch ($type) {
            case "int":
                $value = 3;
                break;
            case "float":
                $value = 1.24;
                break;
            case "string":
                $value = "test";
                break;
            case "bool":
                $value = true;
                break;
            case "array":
                $value = [1, 2, 3];
                break;
            case null;
            case "null":
                $value = null;
                break;
            default:
                $value = \Mockery::mock($type);
                break;
        }
        $message->{"set" . $property}($value);
        $this->assertSame($value, $message->{"get" . $property}(), "The property '" . lcfirst($property). "' did not return the expected value");
    }

    /**
     * @return array
     */
    public function properties()
    {
        return [
            ["Id"],
            ["Message"],
            ["ReceiptId"],
            ["Attributes", "array"],
            ["QueueId"]
        ];
    }

}
 