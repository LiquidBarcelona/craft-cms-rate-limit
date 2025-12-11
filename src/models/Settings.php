<?php

namespace liquidbcn\ratelimit\models;

use craft\base\Model;

class Settings extends Model
{
    public int $maxRequestsPerIpPerMinute = 4000;
}
