<?php

namespace Arthurpar06\DiscordNotifier\Enums;

/**
 * Convenience palette for embed colors. An embed color may also be any raw
 * integer (0x000000–0xFFFFFF); this enum just spares callers the hex lookup.
 */
enum DiscordColor: int
{
    case Blurple = 0x5865F2;
    case Green = 0x57F287;
    case Yellow = 0xFEE75C;
    case Red = 0xED4245;
    case White = 0xFFFFFF;
    case Black = 0x000000;
    case Greyple = 0x99AAB5;
    case DarkButNotBlack = 0x2C2F33;
}
