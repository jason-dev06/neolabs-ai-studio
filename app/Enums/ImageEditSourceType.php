<?php

namespace App\Enums;

enum ImageEditSourceType: string
{
    case Upload = 'upload';
    case Generated = 'generated';
}
