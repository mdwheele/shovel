<?php

namespace Shovel;

interface Bystander
{
    public function tell(Message $message);
}