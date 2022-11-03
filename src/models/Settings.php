<?php

namespace liquidbcn\ratelimit\models;


use craft\base\Model;

class Settings extends Model
{
    public $maxRequestsPerIpPerMinute = 4000;
}
