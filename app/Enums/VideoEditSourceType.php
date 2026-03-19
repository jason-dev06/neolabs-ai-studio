<?php

namespace App\Enums;

enum VideoEditSourceType: string
{
    case Upload = 'upload';
    case Generated = 'generated';
}
