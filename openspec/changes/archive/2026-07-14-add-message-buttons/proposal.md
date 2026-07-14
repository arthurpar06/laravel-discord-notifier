## Why

The package models messages, embeds, and allowed mentions but has no way to attach interactive elements. Buttons are the most-requested message component — a notification is far more useful when it carries a "View in dashboard" link or an action button. Discord's classic (V1) message components support this alongside content and embeds, and the existing object model (typed `Arrayable` builders with `make()` factories and `validate()`-at-serialization) extends to them naturally.

## What Changes

- Add a `ButtonStyle` enum covering all six Discord button styles: `Primary` (1), `Secondary` (2), `Success` (3), `Danger` (4), `Link` (5), `Premium` (6).
- Add a `Button` component (type 2) built through **named constructors** that make invalid buttons unrepresentable: `Button::primary/secondary/success/danger(string $customId, string $label)`, `Button::link(string $url, ?string $label = null)`, `Button::premium(string $skuId)`, plus fluent `->disabled()` and `->emoji()`.
- Add an `ActionRow` component (type 1) holding 1–5 buttons.
- Add a `components` field to `DiscordMessage` with fluent `->actionRow(Button ...)` and `->button(Button)` (single-button convenience row), serialized under the `components` key.
- Enforce Discord's component limits and per-style field rules at `toArray()` (≤5 rows, 1–5 buttons per row, label ≤80, custom_id ≤100, style-specific required/forbidden fields).
- Document that interactive styles (Primary/Secondary/Success/Danger) send correctly but their clicks raise Interactions this send-only package does **not** receive; the consumer must run their own interaction handling. Link and Premium buttons need no handling.

Uses **Components V1** (classic action rows) so buttons coexist with `content` and `embeds`. The existing `IS_COMPONENTS_V2` flag and its mutual-exclusivity guard are untouched. Select menus, text inputs/modals, and Components V2 layout components are out of scope.

## Capabilities

### New Capabilities
<!-- None. Buttons extend the existing message model. -->

### Modified Capabilities
- `discord-message`: adds requirements for typed button/action-row components, a `components` field on the message, per-style and per-limit validation, and the interactive-button handling caveat.

## Impact

- **Source (new)**: `src/Enums/ButtonStyle.php`, `src/Components/Button.php`, `src/Components/ActionRow.php`.
- **Source (modified)**: `src/Messages/DiscordMessage.php` (new `components` field, setters, serialization, validation), `src/Exceptions/InvalidDiscordMessageException.php` (new descriptive failures for button/row rules).
- **Tests**: new coverage for button construction, action rows, message serialization with components, and every validation branch — held to the existing 100% line / 100% type / ≥97% mutation gates.
- **Docs**: README usage example plus a clear interaction-handling caveat for interactive styles.
- **Backward compatibility**: purely additive; existing messages serialize unchanged (no `components` key unless set).
