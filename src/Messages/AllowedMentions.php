<?php

namespace Arthurpar06\DiscordNotifier\Messages;

use Arthurpar06\DiscordNotifier\Contracts\Arrayable;
use Arthurpar06\DiscordNotifier\Enums\AllowedMentionType;
use Arthurpar06\DiscordNotifier\Support\Serializer;

/**
 * @phpstan-consistent-constructor
 */
class AllowedMentions implements Arrayable
{
    /** @var array<int, AllowedMentionType>|null */
    protected ?array $parse = null;

    /** @var array<int, string>|null */
    protected ?array $roles = null;

    /** @var array<int, string>|null */
    protected ?array $users = null;

    protected ?bool $repliedUser = null;

    public static function make(): static
    {
        return new static;
    }

    /**
     * Suppress every mention in the message.
     */
    public static function none(): static
    {
        return (new static)->parse([]);
    }

    /**
     * @param  array<int, AllowedMentionType>  $parse
     */
    public function parse(array $parse): static
    {
        $this->parse = array_values($parse);

        return $this;
    }

    /**
     * @param  array<int, string>  $roles
     */
    public function roles(array $roles): static
    {
        $this->roles = array_values($roles);

        return $this;
    }

    /**
     * @param  array<int, string>  $users
     */
    public function users(array $users): static
    {
        $this->users = array_values($users);

        return $this;
    }

    public function repliedUser(bool $repliedUser = true): static
    {
        $this->repliedUser = $repliedUser;

        return $this;
    }

    public function toArray(): array
    {
        return Serializer::build([
            'parse' => $this->parse,
            'roles' => $this->roles,
            'users' => $this->users,
            'replied_user' => $this->repliedUser,
        ]);
    }
}
