<?php

namespace ProcessMaker\Util;

use WsResponse;

class WsMessageResponse extends WsResponse
{
    private $appMessUid = null;

    /**
     * Get the appMessUid
     *
     * @return array
     */
    public function getAppMessUid()
    {
        return $this->appMessUid;
    }

    /**
     * Set the appMessUid
     *
     * @param string $v
     * @return void
     */
    public function setAppMessUid($v)
    {
        $this->appMessUid = $v;
    }
}

