# discord-message Specification

## Purpose

Provide a strongly-typed, fluent representation of the Discord Create Message body so callers assemble messages (content, embeds, allowed mentions, flags) through typed builders and enums instead of hand-written arrays, with serialization and limit validation handled for them.

## Requirements

### Requirement: Fluent message construction

The system SHALL provide a `DiscordMessage` class constructed via a static `make()` factory and mutated through fluent setter methods, so a complete Create Message body can be assembled without writing arrays by hand.

#### Scenario: Build a message fluently

- **WHEN** a caller writes `DiscordMessage::make()->content('Hello')->tts(false)`
- **THEN** a `DiscordMessage` instance is returned carrying the set values

#### Scenario: Attach embeds

- **WHEN** a caller passes an array of `DiscordEmbed` instances to `->embeds([...])`
- **THEN** those embeds are stored on the message and appear under the `embeds` key when serialized

### Requirement: Typed embed objects

The system SHALL provide a `DiscordEmbed` class and typed sub-objects for footer, author, image, thumbnail, and fields, each constructed via `make()` and fluent setters covering the documented embed fields (title, description, url, color, timestamp, footer, author, image, thumbnail, fields).

#### Scenario: Build an embed with sub-objects

- **WHEN** a caller sets a title, description, a footer with text, an author with name, and one or more fields
- **THEN** the serialized embed contains `title`, `description`, `footer.text`, `author.name`, and a `fields` array with `name`/`value`/`inline` entries

#### Scenario: Color accepts an integer or a color helper

- **WHEN** a caller sets an embed color from an integer or a provided color enum/helper
- **THEN** the serialized `color` is the integer value Discord expects

### Requirement: Enums for fixed-value fields

The system SHALL model Discord's fixed-value fields as PHP enums, including message flags (`MessageFlag` with at least `SUPPRESS_EMBEDS`, `SUPPRESS_NOTIFICATIONS`, `IS_COMPONENTS_V2`), so callers select values by name rather than remembering bit values.

#### Scenario: Combine message flags

- **WHEN** a caller sets one or more `MessageFlag` cases on a message
- **THEN** the serialized `flags` value is the bitwise OR of the selected cases' integer values

### Requirement: Serialization to the Create Message body

The system SHALL serialize a `DiscordMessage` (and all nested objects) to an array matching the Discord Create Message JSON body via a `toArray()` method, omitting any field that was not set (no explicit nulls) and coercing enums to their underlying scalar values.

#### Scenario: Only set fields are emitted

- **WHEN** a message sets only `content`
- **THEN** `toArray()` returns `['content' => '...']` with no `embeds`, `flags`, `tts`, or other keys present

#### Scenario: Allowed mentions serialize as an object

- **WHEN** a caller configures allowed mentions with parse types and/or explicit user/role ids
- **THEN** the serialized body contains an `allowed_mentions` object with the configured `parse`, `users`, `roles`, and `replied_user` fields that were set

### Requirement: Send-time limit validation

The system SHALL validate Discord's documented count and length limits at serialization time (when `toArray()` runs), raising a descriptive exception that identifies the offending field and limit, rather than relying on the type system for these constraints.

#### Scenario: Too many embeds

- **WHEN** a message with more than 10 embeds is serialized
- **THEN** an exception is raised naming the embed count and the maximum of 10

#### Scenario: Content too long

- **WHEN** a message whose `content` exceeds 2000 characters is serialized
- **THEN** an exception is raised naming the content length limit

#### Scenario: Components V2 flag conflicts with content or embeds

- **WHEN** a message sets the `IS_COMPONENTS_V2` flag together with `content` or `embeds`
- **THEN** an exception is raised explaining that these fields are mutually exclusive

#### Scenario: Valid message passes

- **WHEN** a message within all documented limits is serialized
- **THEN** no exception is raised and the body array is returned
