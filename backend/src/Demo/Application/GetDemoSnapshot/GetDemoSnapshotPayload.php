<?php

declare(strict_types=1);

namespace App\Demo\Application\GetDemoSnapshot;

/**
 * No fields: this use case takes no input, it always returns the same fixed
 * demo snapshot. Kept as a Payload class for consistency with every other
 * use case (Ui -> Payload -> Handler), not because there's anything to carry.
 */
final class GetDemoSnapshotPayload
{
}
