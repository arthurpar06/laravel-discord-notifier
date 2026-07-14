## ADDED Requirements

### Requirement: Typed button components

The system SHALL provide a `Button` component and a `ButtonStyle` enum covering all six Discord button styles (`Primary`, `Secondary`, `Success`, `Danger`, `Link`, `Premium`). Buttons SHALL be constructed through named constructors that encode each style's valid field set, so a caller cannot build a button with a field combination Discord rejects: `primary`/`secondary`/`success`/`danger` take a `customId` and `label`, `link` takes a `url` and optional `label`, and `premium` takes an `skuId`.

#### Scenario: Build a link button

- **WHEN** a caller writes `Button::link('https://example.com', 'View')`
- **THEN** the serialized button is `{ type: 2, style: 5, url: 'https://example.com', label: 'View' }`

#### Scenario: Build an interactive button

- **WHEN** a caller writes `Button::primary('confirm', 'Confirm')`
- **THEN** the serialized button is `{ type: 2, style: 1, custom_id: 'confirm', label: 'Confirm' }`

#### Scenario: Build a premium button

- **WHEN** a caller writes `Button::premium('123456789012345678')`
- **THEN** the serialized button is `{ type: 2, style: 6, sku_id: '123456789012345678' }` with no label, url, or custom_id

#### Scenario: Disabled and emoji are optional

- **WHEN** a caller adds `->disabled()` and/or `->emoji('🔥')` to a non-premium button
- **THEN** the serialized button includes `disabled: true` and/or an `emoji` object accordingly, and omits them when not set

### Requirement: Action rows group buttons

The system SHALL provide an `ActionRow` component (Discord component type 1) that holds between one and five buttons, constructed via `make()`/fluent setters and implementing the same `Arrayable` serialization as other components.

#### Scenario: Serialize an action row

- **WHEN** a caller builds an action row with two buttons
- **THEN** the serialized row is `{ type: 1, components: [ <button>, <button> ] }`

#### Scenario: Reject an empty or oversized row

- **WHEN** an action row is serialized with zero buttons or more than five buttons
- **THEN** an exception is raised naming the action-row button-count limit

### Requirement: Messages carry components

The system SHALL let a `DiscordMessage` carry up to five action rows via fluent `->actionRow(Button ...)` and a single-button `->button(Button)` convenience that wraps the button in its own row, serialized under the `components` key alongside `content` and `embeds`. Components SHALL use Discord's classic (V1) action rows and SHALL NOT set the `IS_COMPONENTS_V2` flag.

#### Scenario: Attach a button to a message

- **WHEN** a caller writes `DiscordMessage::make()->content('Deploy done')->button(Button::link('https://ci.example.com', 'View build'))`
- **THEN** the serialized body contains `content` and a `components` array with one action row containing the link button

#### Scenario: Message with no components omits the key

- **WHEN** a message sets no buttons or action rows
- **THEN** `toArray()` contains no `components` key

#### Scenario: Reject more than five action rows

- **WHEN** a message with more than five action rows is serialized
- **THEN** an exception is raised naming the action-row count limit

### Requirement: Button field validation by style

The system SHALL validate each button's fields against its style at serialization time, raising a descriptive exception identifying the offending rule: interactive styles require a `custom_id` (≤100 characters) and forbid `url`/`sku_id`; `Link` requires a `url` and forbids `custom_id`/`sku_id`; `Premium` requires an `sku_id` and forbids `url`, `custom_id`, `label`, and `emoji`; and any non-premium `label` SHALL be at most 80 characters.

#### Scenario: Label too long

- **WHEN** a button whose `label` exceeds 80 characters is serialized
- **THEN** an exception is raised naming the label length limit

#### Scenario: custom_id too long

- **WHEN** an interactive button whose `custom_id` exceeds 100 characters is serialized
- **THEN** an exception is raised naming the custom_id length limit

### Requirement: Interactive-button handling caveat

The system SHALL document that interactive button styles (`Primary`, `Secondary`, `Success`, `Danger`) are serialized and sent but their clicks produce Discord Interactions that this send-only package does not receive; delivering a working interactive button requires the consumer to run their own interaction handling (gateway or HTTP interactions endpoint). `Link` and `Premium` buttons require no such handling.

#### Scenario: Documentation states the caveat

- **WHEN** a reader consults the package documentation for buttons
- **THEN** it explains that interactive buttons need the consumer's own interaction handling, while link and premium buttons work with no handling
