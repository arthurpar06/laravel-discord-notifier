## 1. Button style enum

- [x] 1.1 Add `src/Enums/ButtonStyle.php` as a backed int enum: `Primary=1`, `Secondary=2`, `Success=3`, `Danger=4`, `Link=5`, `Premium=6`
- [x] 1.2 Add a helper to distinguish interactive styles (1–4) from link/premium, for use in validation

## 2. Button component

- [x] 2.1 Add `src/Components/Button.php` implementing `Arrayable`, with named constructors `primary`/`secondary`/`success`/`danger($customId, $label)`, `link($url, $label = null)`, `premium($skuId)`
- [x] 2.2 Add fluent `->disabled(bool = true)` and `->emoji(string|array)` (unicode string → `{name}`, custom → `{id, name, animated}`); emoji forbidden on premium
- [x] 2.3 Implement `toArray()` emitting `type: 2`, `style`, and only the style-appropriate fields (`custom_id`/`url`/`sku_id`/`label`/`emoji`/`disabled`) via `Serializer::build`
- [x] 2.4 Implement `validate()` keyed by style: interactive ⇒ `custom_id` set (≤100) and `url`/`sku_id` unset; link ⇒ `url` set and `custom_id`/`sku_id` unset; premium ⇒ `sku_id` set and `url`/`custom_id`/`label`/`emoji` unset; non-premium `label` ≤80

## 3. Action row component

- [x] 3.1 Add `src/Components/ActionRow.php` implementing `Arrayable` (type 1) via `make(Button ...)` / fluent add, holding its buttons
- [x] 3.2 Implement `toArray()` emitting `{ type: 1, components: [...] }` and cascading `validate()` into each button
- [x] 3.3 Implement `validate()` enforcing 1–5 buttons per row

## 4. Wire components into DiscordMessage

- [x] 4.1 Add a `protected array $components` field plus `->actionRow(Button ...)` and single-button `->button(Button)` (wrap in its own row) fluent setters
- [x] 4.2 Serialize under the `components` key in `toArray()`, omitting it when empty
- [x] 4.3 Extend `DiscordMessage::validate()`: ≤5 action rows and cascade into each row; optionally guard against `IS_COMPONENTS_V2` flag set together with V1 components (per design D5)

## 5. Exceptions

- [x] 5.1 Add descriptive `InvalidDiscordMessageException` factories for the new failures (action-row count, buttons-per-row count, label length, custom_id length, style field-rule violations), consistent with existing `limitExceeded` messaging

## 6. Tests (hold existing gates)

- [x] 6.1 Button construction + serialization per style (link, each interactive style, premium) including exact serialized shape
- [x] 6.2 Disabled and emoji (unicode and custom) serialization; emoji omitted when unset
- [x] 6.3 Action row serialization; empty row and >5 buttons rejected
- [x] 6.4 Message with components: `->button()` convenience, `->actionRow()`, coexistence with content/embeds, `components` key omitted when unset, >5 rows rejected
- [x] 6.5 Every button validation branch: interactive/link/premium required+forbidden fields, label >80, custom_id >100 (assert exact boundary at the limit)
- [x] 6.6 Run the full gate suite (`composer test`, `analyse`, `test-type-coverage`, `test-coverage`, `test-mutate`) and reach 100% line / 100% type / ≥97% mutation; add boundary/exact-value tests to kill mutation survivors

## 7. Documentation

- [x] 7.1 Add a README buttons example (embed + link button) using the fluent API
- [x] 7.2 Document the interactive-button caveat: interactive styles need the consumer's own interaction handling; link/premium need none
