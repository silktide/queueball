<?php
/**
 * Silktide Nibbler. Copyright 2013-2014 Silktide Ltd. All Rights Reserved.
 */
namespace Silktide\QueueBall\Message;

/**
 *
 */
interface QueueMessageFactoryInterface
{

    public function createMessage(array $message, $queueId);

} 