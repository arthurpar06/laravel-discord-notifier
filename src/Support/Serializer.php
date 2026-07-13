<?php

namespace Arthurpar06\DiscordNotifier\Support;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use BackedEnum;

/**
 * Turns a keyed set of builder values into a Discord API array body:
 * drops anything left unset (null), coerces enums to their scalar value, and
 * recursively serializes nested Arrayable objects.
 */
final class Serializer
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function build(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            $result[$key] = self::normalize($value);
        }

        return $result;
    }

    public static function normalize(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof Arrayable) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return array_map(static fn (mixed $item): mixed => self::normalize($item), $value);
        }

        return $value;
    }
}
