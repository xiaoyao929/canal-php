<?php
namespace client;

interface CanalConnector
{
    public function connect();
    public function disConnect();

    /**
     * @param $username
     * @param $password
     * @return mixed
     */
    public function checkValid($username, $password);

    /**
     * @param string $filter
     */
    public function subscribe($filter);
    public function unSubscribe();

    /**
     * @param int $size
     * @return mixed
     */
    public function get($size);

    /**
     * 允许指定batchSize，一次可以获取多条，每次返回的对象为Message
     *
     * @param $batchSize
     * @param $timeout
     * @param $unit
     * @return mixed
     */
    public function getWithoutAck($batchSize, $timeout, $unit);

    /**
     * @param int $messageId
     */
    public function ack($messageId);
    public function rollback();
}