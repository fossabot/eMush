<?php

declare(strict_types=1);

namespace Mush\Daedalus\Enum;

final class DaedalusOrientationEnum
{
    public const NORTH = 'north';
    public const SOUTH = 'south';
    public const EAST = 'east';
    public const WEST = 'west';

    public static function getAll(): array
    {
        return [
            self::NORTH,
            self::SOUTH,
            self::EAST,
            self::WEST,
        ];
    }

    public static function getOppositeOrientation(string $orientation): string
    {
        switch ($orientation) {
            case self::NORTH:
                return self::SOUTH;
            case self::SOUTH:
                return self::NORTH;
            case self::EAST:
                return self::WEST;
            case self::WEST:
                return self::EAST;
            default:
                throw new \Exception('Unknown orientation');
        }
    }

    public static function getClockwiseOrientation(string $orientation): string
    {
        switch ($orientation) {
            case self::NORTH:
                return self::EAST;
            case self::SOUTH:
                return self::WEST;
            case self::EAST:
                return self::SOUTH;
            case self::WEST:
                return self::NORTH;
            default:
                throw new \Exception('Unknown orientation');
        }
    }

    public static function getCounterClockwiseOrientation(string $orientation): string
    {
        switch ($orientation) {
            case self::NORTH:
                return self::WEST;
            case self::SOUTH:
                return self::EAST;
            case self::EAST:
                return self::NORTH;
            case self::WEST:
                return self::SOUTH;
            default:
                throw new \Exception('Unknown orientation');
        }
    }
}
