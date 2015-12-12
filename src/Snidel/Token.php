<?php
class Snidel_Token
{
    /** @var int */
    private $ownerPid;

    /** @var int */
    private $concurrency;

    /** @var string */
    private $keyPrefix;

    /** @var resource */
    private $id;

    /**
     * @param   int     $ownerPid
     * @param   int     $concurrency
     */
    public function __construct($ownerPid, $concurrency)
    {
        $this->keyPrefix = uniqid((string) mt_rand(1, 100), true);
        $this->ownerPid = $ownerPid;
        $this->concurrency = $concurrency;
        $this->id = msg_get_queue($this->genId());
        $this->initializeQueue();
    }

    /**
     * wait for the token
     *
     * @return bool
     */
    public function accept()
    {
        $msgtype = $message = null;
        $success = msg_receive($this->id, 1, $msgtype, 100, $message, true, MSG_NOERROR);
        return $success;
    }

    /**
     * returns the token
     */
    public function back()
    {
        // argument #3 is owner(parent) pid or child pid
        return msg_send($this->id, 1, getmypid());
    }

    /**
     * generate IPC key
     *
     * @return  int
     */
    private function genId()
    {
        $pathname = '/tmp/' . sha1($this->getKey());
        if (!file_exists($pathname)) {
            touch($pathname);
        }

        return ftok($pathname, 'S');
    }

    private function getKey()
    {
        return $this->keyPrefix . $this->ownerPid;
    }

    /**
     * initialize the queue of token
     *
     * @return void
     */
    private function initializeQueue()
    {
        for ($i = 0; $i < $this->concurrency; $i++) {
            $this->back();
        }
    }

    public function __destruct()
    {
        if ($this->keyPrefix . getmypid() === $this->getKey()) {
            unlink('/tmp/' . sha1($this->getKey()));
            return msg_remove_queue($this->id);
        }
    }// @codeCoverageIgnore
}
