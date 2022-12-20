<?php

declare(strict_types=1);

namespace betterkoths\utils;

use pocketmine\math\Vector3;
use RuntimeException;

final class KothPosition {

    static public function vectorToString(Vector3 $position): string {
        return $position->getFloorX() . ':' . $position->getFloorY() . ':' . $position->getFloorZ();
    }

    static public function stringToVector(string $position): Vector3 {
        $coords = explode(':', $position);

        if (count($coords) !== 3) {
            throw new RuntimeException('String vector invalid.');
        }
        return new Vector3((int) $coords[0], (int) $coords[1], (int) $coords[2]);
    }
}