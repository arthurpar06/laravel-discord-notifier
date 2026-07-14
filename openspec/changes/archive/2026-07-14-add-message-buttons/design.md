## Context

`DiscordMessage` today carries `content`, `embeds`, `tts`, `flags`, and `allowedMentions`, all built through typed `Arrayable` objects with `make()` factories, `Serializer::build()` for null-omitting serialization, and `validate()` invoked from `toArray()`. `MessageFlag::IsComponentsV2` and an `InvalidDiscordMessageException::componentsV2Conflict()` guard already exist, but there is no `components` field and no component objects. Discord buttons live inside action rows inside a message's `components` array; a message may carry up to five action rows, each with up to five buttons. The package sends via webhook or bot token and has no interaction-handling layer.

## Goals / Non-Goals

**Goals:**
- Model all six button styles with an API that makes invalid buttons unrepresentable.
- Let buttons ride alongside `content`/`embeds` using Components V1.
- Enforce Discord's component limits and per-style field rules at serialization time.
- Be honest, in code and docs, about what interactive buttons require.
- Hold the existing quality gates (100% line / 100% type / ≥97% mutation).

**Non-Goals:**
- Select menus, text inputs, modals, and Components V2 layout components.
- Any interaction-receiving capability (gateway, HTTP interactions endpoint, deferred responses).
- Changing the existing `IS_COMPONENTS_V2` flag or its conflict guard.

## Decisions

### D1: Named constructors over a free-form `make()`
`Button::primary/secondary/success/danger($customId, $label)`, `Button::link($url, $label = null)`, `Button::premium($skuId)`, then fluent `->disabled()`/`->emoji()`. Each style has a different required/forbidden field set; named constructors encode the valid shapes so a Link button with a `custom_id` cannot be constructed. This mirrors the existing `DiscordRoute::webhook()/channel()` pattern.
- *Alternatives:* A single `Button::make()->style()->url()->customId()` — rejected: lets callers assemble invalid combinations that only fail at `toArray()`.

### D2: `ButtonStyle` as a backed int enum
`Primary=1 … Link=5, Premium=6`, matching Discord's wire values, coerced to `value` on serialize — consistent with `DiscordColor`/`MessageFlag`.

### D3: `ActionRow` is an explicit object; `->button()` is sugar
`ActionRow` (type 1) holds the buttons and validates the 1–5 count. `DiscordMessage->actionRow(Button ...)` adds a row; `->button(Button)` wraps a single button in its own row for the common one-button case.
- *Alternatives:* Auto-flow loose buttons into rows of five — rejected as implicit/surprising; callers control grouping.

### D4: Validation lives in `validate()`, keyed by style
`Button::validate()` switches on style for required/forbidden fields, label ≤80, custom_id ≤100. `ActionRow::validate()` checks 1–5 buttons. `DiscordMessage::validate()` checks ≤5 rows and cascades into each row/button. New `InvalidDiscordMessageException` factories give descriptive, field-naming messages, consistent with embed validation.

### D5: Components V1, coexisting with content/embeds; V2 flag left alone
V1 action rows do not set `IS_COMPONENTS_V2`, so the existing conflict guard is unaffected and buttons ship with embeds — the notifier's real use case. As a cheap safety net, `DiscordMessage::validate()` MAY reject setting the `IS_COMPONENTS_V2` flag together with V1 components (the two component systems interpret the `components` field differently); this is minor and can be a single guard.

### D6: Emoji as an optional partial-emoji value
`->emoji()` accepts a unicode string (serialized as `{ name: '🔥' }`) or a custom emoji `{ id, name, animated }`. Optional on non-premium buttons; forbidden on premium. Kept small; not its own capability.

## Risks / Trade-offs

- **Interactive buttons are a footgun** (clicks dead-end without a handler) → mitigate by documenting prominently and making link/premium the frictionless path; do not hide the caveat.
- **Per-style validation branching is the bug-prone part** → cover every style's required/forbidden combination and both length limits with explicit tests; mutation testing guards the boundary/`>=` logic.
- **Discord may evolve component rules** (e.g. premium button fields) → keep validation in one `validate()` per object so changes are localized.
- **Emoji shape ambiguity** (unicode vs custom) → accept both explicitly; document which maps to `name` vs `id`.

## Migration Plan

Purely additive; no migration. Existing messages serialize identically (no `components` key unless set). Ship the enum + `Button` + `ActionRow`, wire `components` into `DiscordMessage`, add validation and exceptions, then tests and docs. Rollback is removal of the new classes and the `components` field.

## Open Questions

- Include the `IS_COMPONENTS_V2` + V1-`components` guard now (D5), or leave it undefined? Leaning include — it is one cheap check.
- Emoji in this change (D6) or a fast follow? Leaning include, since all styles/completeness were requested.
