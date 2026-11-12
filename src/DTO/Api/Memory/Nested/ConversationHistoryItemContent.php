<?php

namespace DataMat\GrinningCat\DTO\Api\Memory\Nested;

use DataMat\GrinningCat\DTO\MessageBase;
use DataMat\GrinningCat\DTO\Why;

class ConversationHistoryItemContent extends MessageBase
{
    /** @var Why|null */
    public ?Why $why = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        if ($this->why !== null) {
            $data['why'] = $this->why->toArray();
        }

        return $data;
    }
}