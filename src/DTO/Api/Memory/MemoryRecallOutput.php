<?php

namespace DataMat\GrinningCat\DTO\Api\Memory;

use DataMat\GrinningCat\DTO\Api\Memory\Nested\MemoryRecallQuery;
use DataMat\GrinningCat\DTO\Api\Memory\Nested\MemoryRecallVectors;

class MemoryRecallOutput
{
    /** @var MemoryRecallQuery */
    public MemoryRecallQuery $query;

    /** @var MemoryRecallVectors */
    public MemoryRecallVectors $vectors;
}