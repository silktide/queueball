<?php
/**
 * Silktide Nibbler. Copyright 2013-2014 Silktide Ltd. All Rights Reserved.
 */
namespace Silktide\QueueBall\Message;

/**
 *
 */
class QueueMessage
{

    /**
     * @var string
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $message;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var string
     */
    protected $receiptId;

    /**
     * @var string
     */
    protected $queueId;

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string $receiptId
     */
    public function setReceiptId($receiptId)
    {
        $this->receiptId = $receiptId;
    }

    /**
     * @return string
     */
    public function getReceiptId()
    {
        return $this->receiptId;
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
     */
    public function getQueueId()
    {
        return $this->queueId;
    }

}