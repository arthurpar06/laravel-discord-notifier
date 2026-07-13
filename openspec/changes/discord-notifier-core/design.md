## Context

The repository is the unmodified Spatie `package-skeleton-laravel` template — every identifier is still a placeholder (`VendorName\Skeleton`, `config/skeleton.php`, etc.). Before real code exists the skeleton must be personalized.

The goal of this change is the foundational slice of a Discord notifier: a typed message builder plus the plumbing to deliver it through Laravel's notification system over either a webhook or a bot. The design was settled during exploration; this document records the decisions and their rationale. Key external facts driving it (confirmed against Discord's docs): the Create Message endpoint is a single body with one field set; `IS_COMPONENTS_V2` is merely a bit in `flags`, not a separate payload type; and DM-to-user and message-to-guild-channel are the same `POST /channels/{id}/messages` call.

Targets PHP `^8.4`, Laravel 12–13, tested via Pest 4 on Orchestra Testbench.

## Goals / Non-Goals

**Goals:**
- One fluent, IDE-discoverable `DiscordMessage` that models the Create Message body; no hand-written arrays.
- Deliver the same message over webhook or bot, chosen by the route, with bot credentials from config/`.env`.
- Ergonomic routing: a bare channel-id string works, `DiscordRoute` is available for explicit/webhook cases.
- Maximize the type system for "kind" correctness; validate limits at send time with clear exceptions.
- Ship embeds + core message fields; leave a clean seam for components and Components V2 later.

**Non-Goals:**
- Interactive components (buttons, select menus, action rows) and Components V2 container trees — deferred to a fast-follow change.
- Handling interactions/responses, editing/deleting sent messages, reactions, threads, polls, stickers, file/attachment uploads.
- A default/global route; message reference (replies) and webhook username/avatar overrides (transport-specific fields deferred).
- Opening a user's DM channel (`POST /users/@me/channels`) — consumers store a pre-resolved `discord_private_channel_id`.

## Decisions

### One `DiscordMessage`, not V1/V2 siblings
Model the Create Message body as a single class; `IS_COMPONENTS_V2` is a `MessageFlag` case. Mutual-exclusivity (flag ⇒ no `content`/`embeds`) is a send-time validation rule, not a type split.
*Alternative considered:* separate `DiscordMessage` (V1) and `DiscordComponentMessage` (V2) siblings behind a `DiscordPayload` contract. Rejected as premature — the API presents one payload, and the sibling split doubled surface area for no current benefit. A `DiscordPayload`/serializable contract is still introduced so the transport depends on an interface, keeping the seam open.

### Serialization via a `toArray()` contract with null-stripping
Every typed object implements `toArray()`; parents recurse into children and drop any key that was never set (Discord rejects some explicit nulls). Enums coerce to their scalar backing value. Validation of limits runs as part of this pass.
*Alternative considered:* Laravel `Arrayable` + a separate `validate()` call. Folding validation into `toArray()` guarantees no unvalidated body can be sent, at the cost of `toArray()` being able to throw — an acceptable, documented trade.

### Validation split: type system for kinds, runtime for limits
Enums and typed parameters make wrong *kinds* unrepresentable (free, IDE-time). Counts/lengths/ranges (≤10 embeds, `content` ≤2000, field caps, color range, V2 exclusivity) are not expressible in PHP's type system and are checked at send time, each raising a descriptive exception naming field + limit.
*Alternative considered:* eager validation inside every setter. Rejected — it scatters rules across dozens of setters, breaks fluent building where you assemble-then-trim, and can't produce one aggregated error. Trade-off accepted: errors surface at `send()`, not the offending setter call.

### Routing: `DiscordRoute` plus safe string sniffing
The channel resolves the routed value: a `DiscordRoute` is used as-is; a string starting with `http` → webhook; an all-digits snowflake → bot channel; anything else throws. Webhook URLs and snowflakes cannot collide, so sniffing is unambiguous. This preserves the ergonomic `Notification::route('discord', $channelId)` and `routeNotificationForDiscord()` returning a bare id, while `DiscordRoute::webhook(...)` stays explicit.
*Alternative considered:* require `DiscordRoute` everywhere. Rejected as needless ceremony for the common channel-id case the user actually writes.

### Transport selection behind the notification channel
A single `DiscordChannel` (registered as `discord`) resolves the route, picks `WebhookTransport` or `BotTransport`, calls `message->toArray()`, and dispatches via the framework HTTP client. Transports are thin and independently testable with `Http::fake()`.

### Bot config from `.env`, no default route
`config/discord-notifier.php` exposes `bot.token` (`env('DISCORD_BOT_TOKEN')`) and `bot.api_base`. Routing is always explicit; a missing token on a bot send throws a clear configuration exception.

### Personalize the skeleton first
Run/emulate `configure.php` to replace placeholders (namespace, config filename, provider, facade) so subsequent code lands in the real namespace. This is a prerequisite task, gated before any `src/` additions.

## Risks / Trade-offs

- **Send-time errors feel "late"** (thrown at `send()`, not the offending call) → mitigated by exceptions that name the exact field, current value, and limit.
- **`toArray()` can throw** because validation is folded in → documented behavior; guarantees no invalid body reaches Discord.
- **String sniffing could misclassify an exotic route value** → constrained to two well-separated forms (http-prefixed URL vs all-digit snowflake) with an explicit throw on anything else; `DiscordRoute` is the escape hatch.
- **Skeleton personalization is destructive/renaming-heavy** → do it as the first isolated task and verify the suite still boots before writing features.
- **Discord may evolve limits/fields** → limits live in one validation layer, easy to update; the `DiscordPayload` seam absorbs the eventual components/V2 additions without reworking the transport.

## Open Questions

- Exact class/namespace names after personalization (vendor slug, root namespace) — resolved during the personalization task.
- Whether `allowed_mentions` gets its own small typed object or inline setters on `DiscordMessage` — leaning typed object for consistency; finalize in implementation.
