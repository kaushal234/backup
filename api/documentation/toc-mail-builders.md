# TOC — Mail Builders

## Overview

The TOC (Technician On Call) mail system sends transactional emails on every significant lifecycle event of a `TechnicianOnCall` entity. It is built on the **Strategy pattern** combined with Symfony autoconfiguration: each email type is handled by a dedicated builder, discovered automatically via service tags, and triggered by HTTP/Doctrine event listeners.

---

## High-level architecture

```
HTTP write (POST/PUT/PATCH) or Doctrine event
    │
    ▼
TechnicianOnCallNotifierListener        ← detects the event, picks subject(s)
    │
    ▼
TechnicianOnCallNotifier::sendEmail()   ← iterates builders, finds the matching one
    │
    ├── TechnicianOnCallEmailBuilderInterface::supports()   ← first builder that matches wins
    │
    ├── AbstractTocEmailBuilder::build()
    │       ├── RecipientsFinder::findTos()     ← business logic for TO recipients
    │       ├── RecipientsFinder::findCcs()     ← business logic for CC recipients
    │       ├── Translator (domain: 'emails')   ← subject line
    │       ├── buildContext()                  ← hook: Twig template variables (injects CRT for external subjects)
    │       └── TemplatedEmail                  ← template: Emails/Service/TechnicianOnCall/{subject}.html.twig
    │
    └── MailerInterface::send()
```

---

## Mail subjects — `TechnicianOnCallMailSubject`

**File:** `src/Notifier/Service/TechnicianOnCall/TechnicianOnCallMailSubject.php`

A PHP backed string enum with one case per email type. The case value (`snake_case`) doubles as the Twig template filename.

| Case | Template | Trigger |
|------|----------|---------|
| `TOC_CREATED_BY_CUSTOMER` | `toc_created_by_customer.html.twig` | ExtranetUser creates a TOC |
| `TOC_CREATED_BY_PEOPLE_INTERNAL` | `toc_created_by_people_internal.html.twig` | People creates a TOC (internal audience) |
| `TOC_CREATED_BY_PEOPLE_EXTERNAL` | `toc_created_by_people_external.html.twig` | People creates a TOC (external audience) |
| `TOC_BLACK_CAT` | `toc_black_cat.html.twig` | TOC involves a Black Cat equipment |
| `TOC_UPDATED` | `toc_updated.html.twig` | TOC data is modified |
| `TOC_PENDING_TO_IN_PROGRESS_INTERNAL` | `toc_pending_to_in_progress_internal.html.twig` | Status: PENDING → IN_PROGRESS (internal) |
| `TOC_PENDING_TO_IN_PROGRESS_EXTERNAL` | `toc_pending_to_in_progress_external.html.twig` | Status: PENDING → IN_PROGRESS (external) |
| `TOC_NEW_COMMENT_INTERNAL` | `toc_new_comment_internal.html.twig` | People adds a comment (internal audience) |
| `TOC_NEW_COMMENT_EXTERNAL` | `toc_new_comment_external.html.twig` | People adds a comment (external audience) |
| `TOC_NEW_COMMENT_BY_CUST` | `toc_new_comment_by_cust.html.twig` | External customer adds a comment |
| `TOC_FACTORY_FLAG` | `toc_factory_flag.html.twig` | Factory flag toggled |
| `TOC_SOLVED_EXTERNAL` | `toc_solved_external.html.twig` | TOC status set to SOLVED |
| `TOC_CSR_CREATED` | `toc_csr_created.html.twig` | A Customer Service Record is created from the TOC |

### `isExternal(): bool`

The enum exposes a domain method that returns `true` for the three subjects whose emails are sent to external customers (`TOC_CREATED_BY_PEOPLE_EXTERNAL`, `TOC_NEW_COMMENT_EXTERNAL`, `TOC_SOLVED_EXTERNAL`).

This flag is the single source of truth for "is this email going to an external audience?" and drives two cross-cutting behaviours without scattering subject comparisons across the codebase:

1. **CRT injection** — `AbstractTocEmailBuilder::buildContext()` injects the Customer Relationship Team into the Twig context only when `$subject->isExternal()` is true.
2. **Comment visibility filter** — `TocNewCommentEmailBuilder` restricts the `previousComments` query to `public: true` when `$subject->isExternal()`, so internal comments are never leaked to external recipients.

When adding a new external subject, add it to `isExternal()` and both behaviours apply automatically.

### New-comment body — translated text first

The three `TOC_NEW_COMMENT_*` templates render the comment body through the shared
`_partials/_comment_message.html.twig` partial (the external template inlines the
same logic because its comment sits inside an intro sentence). The DeepL English
translation stored on the comment — `comment.metadata['translation']`, populated
by `TechnicianOnCallCommentListener::onCommentTranslateMessage()` at `PRE_WRITE`,
before the notifier listener runs — is shown first, with the original-language
text in a muted block below it (`toc.message.original_comment` label, `emails`
domain). When no translation exists (`metadata['translation']` is `null`), only
the original text is rendered, with no empty block.

---

## Service registration — zero YAML

**File:** `src/Notifier/Service/TechnicianOnCall/Builders/TechnicianOnCallEmailBuilderInterface.php`

```php
#[AutoconfigureTag('app.toc.email.builder')]
interface TechnicianOnCallEmailBuilderInterface
{
    public function supports(TechnicianOnCallMailSubject $subject): bool;

    public function build(
        TechnicianOnCallMailSubject $subject,
        TechnicianOnCall $technicianOnCall,
        array $context = [],
    ): TemplatedEmail;
}
```

Any class implementing this interface is automatically tagged `app.toc.email.builder` (Symfony `autoconfigure: true`). `TechnicianOnCallNotifier` collects them all via:

```php
#[AutowireIterator('app.toc.email.builder')]
iterable $builders
```

No `services.yaml` entry is needed when adding a new builder.

---

## Base class — `AbstractTocEmailBuilder`

**File:** `src/Notifier/Service/TechnicianOnCall/Builders/AbstractTocEmailBuilder.php`

Handles the assembly of every `TemplatedEmail`. Concrete builders only override what differs.

### Shared dependencies (setter injection)

```php
#[Required]
public function setDependencies(
    TranslatorInterface $translator,
    RecipientsFinder $recipientsFinder,
    Security $security,
    CustomerRelationshipTeamRepository $customerRelationshipTeamRepository,
    NormalizerInterface $normalizer,
): void
```

Using `#[Required]` setter injection avoids repeating constructor arguments in every subclass. All five dependencies are available to every builder without any constructor boilerplate.

### `build()` flow

```
build(subject, toc, context)
    │
    ├── RecipientsFinder::findTos(subject, toc, context)   → Address[]
    ├── RecipientsFinder::findCcs(subject, toc, context)   → Address[]
    │
    ├── getSubjectTranslationKey(subject, toc)   ← overridable (default: toc.subject.{subject.value})
    ├── getAdditionalTranslationParameters(subject, toc)   ← abstract: e.g. ['%serialNumber%' => ...]
    ├── Translator::trans(key, params, domain: 'emails')
    │
    ├── htmlTemplate: 'Emails/Service/TechnicianOnCall/{subject.value}.html.twig'
    │
    └── buildContext(subject, toc, context)   ← injects CRT if subject.isExternal(), then builder hook
```

### Extension hooks

| Method | Signature | Default | Override when |
|--------|-----------|---------|---------------|
| `getAdditionalTranslationParameters()` | `(subject, toc): array` | abstract | always — provide subject-line parameters like `%serialNumber%`, `%openDays%` |
| `buildContext()` | `(subject, toc, context): array` | injects `crt` when `subject->isExternal()` | the template needs extra data (e.g. `previousComments`); always call `parent::buildContext()` first |
| `getSubjectTranslationKey()` | `(subject, toc): string` | `toc.subject.{subject.value}` | the key must vary at runtime (e.g. `TocFactoryFlagEmailBuilder` appends `_open` or `_closed`) |

Note: `getAdditionalTranslationParameters()` does **not** receive `$context`. Context-dependent data belongs in `buildContext()` instead.

---

## Concrete builders

**Directory:** `src/Notifier/Service/TechnicianOnCall/Builders/`

```
Builders/
├── TechnicianOnCallEmailBuilderInterface.php
├── AbstractTocEmailBuilder.php
├── TocNewCommentEmailBuilder.php
├── TocPendingToInProgressEmailBuilder.php
├── TocUpdatedEmailBuilder.php
├── TocSolvedExternalEmailBuilder.php
├── TocFactoryFlagEmailBuilder.php
├── TocCustomerServiceRecordEmailBuilder.php
└── Creation/
    ├── TocCreatedByExtranetUserEmailBuilder.php
    ├── TocCreatedByPeopleExternalEmailBuilder.php
    ├── TocCreatedByPeopleInternalEmailBuilder.php
    └── TocCreatedOnBlackCatEmailBuilder.php
```

### Translation parameters by builder

| Builder | Subjects handled | Parameters |
|---------|-----------------|-----------|
| `TocCreatedByExtranetUserEmailBuilder` | `TOC_CREATED_BY_CUSTOMER` | `%customer%`, `%serialNumber%` |
| `TocCreatedByPeopleInternalEmailBuilder` | `TOC_CREATED_BY_PEOPLE_INTERNAL` | `%openDays%`, `%sso%`, `%customer%` |
| `TocCreatedByPeopleExternalEmailBuilder` | `TOC_CREATED_BY_PEOPLE_EXTERNAL` | `%openDays%`, `%sso%`, `%customer%` |
| `TocCreatedOnBlackCatEmailBuilder` | `TOC_BLACK_CAT` | `%openDays%`, `%sso%`, `%customer%` |
| `TocUpdatedEmailBuilder` | `TOC_UPDATED` | `%openDays%`, `%sso%`, `%customer%` |
| `TocPendingToInProgressEmailBuilder` | `TOC_PENDING_TO_IN_PROGRESS_INTERNAL` + `TOC_PENDING_TO_IN_PROGRESS_EXTERNAL` | `%openDays%`, `%serialNumber%` |
| `TocNewCommentEmailBuilder` | `TOC_NEW_COMMENT_INTERNAL` + `TOC_NEW_COMMENT_BY_CUST` | `%openDays%`, `%sso%`, `%customer%`, `%indiceFactor%` |
| `TocNewCommentEmailBuilder` | `TOC_NEW_COMMENT_EXTERNAL` | `%openDays%`, `%sso%` |
| `TocSolvedExternalEmailBuilder` | `TOC_SOLVED_EXTERNAL` | `%serialNumber%` |
| `TocFactoryFlagEmailBuilder` | `TOC_FACTORY_FLAG` | `%openDays%`, `%sso%`, `%customer%` |
| `TocCustomerServiceRecordEmailBuilder` | `TOC_CSR_CREATED` | `%openDays%`, `%sso%`, `%customer%`, `%indiceFactor%` |

### Pattern examples

**Minimal builder:**

```php
class TocSolvedExternalEmailBuilder extends AbstractTocEmailBuilder
{
    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return TechnicianOnCallMailSubject::TOC_SOLVED_EXTERNAL === $subject;
    }

    protected function getAdditionalTranslationParameters(
        TechnicianOnCallMailSubject $subject,
        TechnicianOnCall $toc,
    ): array {
        return ['%serialNumber%' => $toc->equipmentRecord?->getSerialNumber() ?? '---'];
    }
}
```

CRT is automatically injected into context by `parent::buildContext()` because `TOC_SOLVED_EXTERNAL` is external — no code needed in the builder.

**Builder enriching the Twig context** (one builder handling multiple subjects with `isExternal()`-driven behaviour):

```php
class TocNewCommentEmailBuilder extends AbstractTocEmailBuilder
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    public function supports(TechnicianOnCallMailSubject $subject): bool
    {
        return in_array($subject, [
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_INTERNAL,
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_BY_CUST,
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_EXTERNAL,
        ], true);
    }

    protected function buildContext(
        TechnicianOnCallMailSubject $subject,
        TechnicianOnCall $toc,
        array $context,
    ): array {
        $context = parent::buildContext($subject, $toc, $context); // injects CRT for EXTERNAL

        $criteria = ['resource' => $context['comment']->getResource()];
        if ($subject->isExternal()) {
            $criteria['public'] = true; // never leak internal comments to customers
        }

        $context['previousComments'] = $this->entityManager->getRepository(Comment::class)->findBy(
            $criteria, ['createdAt' => 'DESC'], 5, 1,
        );

        return $context;
    }

    protected function getAdditionalTranslationParameters(...): array
    {
        $params = ['%openDays%' => ..., '%sso%' => ...];
        if (!$subject->isExternal()) {
            $params['%customer%'] = ...;
            $params['%indiceFactor%'] = ...;
        }
        return $params;
    }
}
```

**Builder with a dynamic subject key:**

```php
class TocFactoryFlagEmailBuilder extends AbstractTocEmailBuilder
{
    protected function getSubjectTranslationKey(
        TechnicianOnCallMailSubject $subject,
        TechnicianOnCall $toc,
    ): string {
        return sprintf('toc.subject.%s_%s', $subject->value, $toc->factoryFlag ? 'open' : 'closed');
    }
}
```

---

## Recipient resolution — `RecipientsFinder`

**File:** `src/Notifier/Service/TechnicianOnCall/RecipientsFinder.php`

All recipient logic is centralized here. `findTos()` and `findCcs()` each contain a `match ($subject)` expression that maps every subject to its recipient rules.

All output passes through `SanitizedEmailListFactory::buildCleanEmailAddressList()`, which removes nulls, duplicates and malformed addresses.

### Recipient sources by subject (examples)

**`TOC_CREATED_BY_PEOPLE_INTERNAL`** → current user, assignee, technician + supervisor, creator, followers, IHS tag recipients, escalation list based on Indice Factor

**`TOC_FACTORY_FLAG`** → assignee + supervisor, sales representatives, EVP/CSM/DPM/PSM/COO resolved by location

**`TOC_BLACK_CAT`** → buyer and end-user sales reps, DPM/CSM/EVP/RCEO, CSTL hierarchy

### Indice Factor escalation

`getAdditionalRecipientsDependingOnIndiceFactor()` adds progressively broader audiences as the IF increases:

| Indice Factor | Additional recipients |
|---|---|
| `IF_10` | Supervisor of assignee |
| `IF_100` | + GCSD, manufacturer contacts |
| `IF_1000` | + sales org, ALVEST members |

---

## Orchestrator — `TechnicianOnCallNotifier`

**File:** `src/Notifier/Service/TechnicianOnCall/TechnicianOnCallNotifier.php`

```php
public function sendEmail(TechnicianOnCallMailSubject $subject, TechnicianOnCall $toc, array $context = []): void
```

Flow:
1. Injects the normalized TOC into `$context` (groups: `ITEM_NORMALIZATION_GROUPS`, `people_detail`)
2. Injects normalized previous comments (groups: `activity`, `people_public`)
3. Iterates `$builders` until `supports()` returns `true`
4. Calls `builder->build()` → `MailerInterface::send()`
5. Throws `\LogicException` if no builder matches — fail-fast, no silent drop

`sendEmails(array $subjects, ...)` is a convenience wrapper that calls `sendEmail()` for each subject in the array.

A separate `sendIntranetEmail()` method handles the intranet mail flow (custom template, creates a `Comment` entity as a side-effect).

---

## Event triggers — `TechnicianOnCallNotifierListener`

**File:** `src/EventListener/Service/TechnicianOnCall/TechnicianOnCallNotifierListener.php`

Extends `AbstractTechnicianOnCallListener` (which provides shared helpers: `isTechnicianOnCall()`, `isTechnicianOnCallCreation()`, `isTechnicianOnCallRoute()`). Uses the `ServiceSubscriberInterface` pattern — dependencies are resolved lazily via `ContainerInterface`.

| Listener method | Event | Mail subjects sent |
|---|---|---|
| `preFlush()` | `Doctrine\ORM\Events::preFlush` | *(captures changeset only, sends nothing)* |
| `onCreation()` | `KernelEvents::VIEW` POST_WRITE | `TOC_CREATED_BY_CUSTOMER` or `TOC_CREATED_BY_PEOPLE_INTERNAL` + `TOC_CREATED_BY_PEOPLE_EXTERNAL` + optionally `TOC_BLACK_CAT` |
| `onUpdate()` | `KernelEvents::VIEW` POST_WRITE | `TOC_UPDATED` (with changeset in context) |
| `onSolved()` | `KernelEvents::VIEW` POST_WRITE | `TOC_SOLVED_EXTERNAL` (only on transition to SOLVED) |
| `onStatusChangeFromPendingToInProgress()` | `KernelEvents::VIEW` POST_WRITE | `TOC_PENDING_TO_IN_PROGRESS_INTERNAL` + `TOC_PENDING_TO_IN_PROGRESS_EXTERNAL` |
| `onNewComment()` | `KernelEvents::VIEW` POST_WRITE | `TOC_NEW_COMMENT_INTERNAL` / `TOC_NEW_COMMENT_EXTERNAL` / `TOC_NEW_COMMENT_BY_CUST` / `TOC_FACTORY_FLAG` depending on user type and comment visibility. Skipped if `notifications: false` is present in request metadata. |
| `onCustomerServiceRecordCreation()` | `KernelEvents::VIEW` POST_WRITE | `TOC_CSR_CREATED` |

`preFlush` fires before the entity is flushed, capturing the Doctrine changeset so it is available in `$context` when `onUpdate()` runs after the write.

---

## Templates

**Directory:** `api/templates/Emails/Service/TechnicianOnCall/`

One template per subject (filename = `{subject.value}.html.twig`) plus shared partials:

```
TechnicianOnCall/
├── toc_created_by_customer.html.twig
├── toc_created_by_people_internal.html.twig
├── toc_created_by_people_external.html.twig
├── toc_black_cat.html.twig
├── toc_updated.html.twig
├── toc_pending_to_in_progress_internal.html.twig
├── toc_pending_to_in_progress_external.html.twig
├── toc_new_comment_internal.html.twig
├── toc_new_comment_external.html.twig
├── toc_new_comment_by_cust.html.twig
├── toc_factory_flag.html.twig
├── toc_solved_external.html.twig
├── toc_csr_created.html.twig
├── toc_intranet_mail.html.twig
├── survey.html.twig
├── bad_survey.html.twig
├── task_survey_error.html.twig
└── _partials/
    ├── _toc.html.twig
    ├── _toc_details.html.twig
    ├── _equipment_record.html.twig
    ├── _equipment_record_details.html.twig
    ├── _previous_comments.html.twig
    ├── _crt.html.twig
    └── cell/
        ├── _assignee.html.twig
        ├── _created_by.html.twig
        ├── _phones.html.twig
        └── _technician.html.twig
```

### Default Twig context (always available)

Injected by `TechnicianOnCallNotifier` before the builder runs:

| Variable | Type | Source |
|---|---|---|
| `technicianOnCall` | normalized array | normalization groups: `ITEM_NORMALIZATION_GROUPS`, `people_detail` |
| `previousComments` | normalized array | normalization groups: `activity`, `people_public` |

### Additional context by subject

Provided by the event listener (passed in `$context`) and enriched by `buildContext()`:

| Subject(s) | Extra variable | Type | Set by |
|---|---|---|---|
| all `isExternal()` subjects | `crt` | normalized array | `AbstractTocEmailBuilder::buildContext()` |
| `TOC_NEW_COMMENT_*` | `comment` | `Comment` entity | Listener |
| `TOC_NEW_COMMENT_*` | `user` | `People` entity | Listener |
| `TOC_UPDATED` | `changeSet` | `EmailChangeSet` | Listener (`preFlush` changeset) |
| `TOC_CSR_CREATED` | `customerServiceRecord` | `CustomerServiceRecord` | Listener |
| `TOC_CSR_CREATED` | `intervention`, `leader` | `Intervention`, string | `TocCustomerServiceRecordEmailBuilder::buildContext()` |

The `crt` variable may be `null` if no Customer Relationship Team is configured for the TOC's customer and location — all external templates guard with `{% if crt is not null %}`.

---

## Adding a new mail type

1. **Add a case** to `TechnicianOnCallMailSubject` with a `snake_case` value. If the email is addressed to an external customer, also add the case to `isExternal()`.

2. **Create a builder** in `src/Notifier/Service/TechnicianOnCall/Builders/`:
   - Extend `AbstractTocEmailBuilder`
   - Implement `supports()` returning `true` for the new case
   - Implement `getAdditionalTranslationParameters(subject, toc)` (required — return `[]` if the subject line has no parameters)
   - Override `buildContext()` if the template needs extra data; always call `parent::buildContext()` first
   - Override `getSubjectTranslationKey()` if the translation key must vary at runtime

3. **Add recipient rules** in `RecipientsFinder::findTos()` and `findCcs()` — add a case to each `match` expression.

4. **Create the template** at `api/templates/Emails/Service/TechnicianOnCall/{subject.value}.html.twig`. Reuse `_partials/` where possible; for external templates include `_partials/_crt.html.twig` guarded by `{% if crt is not null %}`.

5. **Add a translation key** in the `emails` domain: `toc.subject.{subject.value}`.

6. **Trigger the email** — either add a branch in an existing listener method in `TechnicianOnCallNotifierListener`, or add a new `#[AsEventListener]` method.

No service registration is needed: `autoconfigure: true` picks up the new builder automatically via the `AutoconfigureTag` on the interface.
