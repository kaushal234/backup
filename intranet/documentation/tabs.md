# Tabs by Dedicated Controllers

This documentation explains how to build tab navigation using one Symfony controller per tab and one dedicated view per tab.

---

## Description

Each tab is handled by its own controller.

This approach keeps responsibilities small and makes each tab easier to maintain, test, and evolve independently.

In this setup:

- one route = one tab
- one controller = one tab
- one Twig view = one tab

The tab navigation is shared, while the content is rendered by the dedicated controller of the active tab.

---

## Why Use This Approach

Using one controller per tab provides several benefits:

- each tab has isolated logic
- controllers stay small and focused
- each tab can evolve independently
- form handling stays local to the tab that owns it
- routes remain explicit and easy to debug

This is especially useful when tabs contain different behaviors such as:

- read-only content
- forms
- file uploads
- logs
- comments
- subscriptions

---

## Structure

A typical implementation looks like this:

### Base tab

```php
#[Route(path: '/{id}/show', name: 'vendor_warranty_claim_show', methods: ['GET'])]
final class ShowController extends AbstractController
{
    public function __invoke(ApiData $vendorWarrantyClaim): array
    {
        return [
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
        ];
    }
}
```

### Files tab

```php
#[Route(path: '/{id}/show/files', name: 'vendor_warranty_claim_show_files', methods: ['GET', 'POST'])]
final class ShowFilesController extends AbstractController
{
    public function __invoke(ApiData $vendorWarrantyClaim, Request $request): Response|array
    {
        return [
            'vendorWarrantyClaim' => $vendorWarrantyClaim,
            'form_files' => $form->createView(),
        ];
    }
}
```

Each controller renders its own template:

- show.html.twig
- show_files.html.twig
- show_logs.html.twig
- show_subscription.html.twig

---

## Shared Tab Navigation

The navigation is centralized in a shared partial.

```php
<twig:Tabs:NavItem
    url="{{ path('vendor_warranty_claim_show', {id: vendorWarrantyClaim.id}) }}"
>
    Details
</twig:Tabs:NavItem>

<twig:Tabs:NavItem
    url="{{ path('vendor_warranty_claim_show_files', {id: vendorWarrantyClaim.id}) }}"
>
    Files
</twig:Tabs:NavItem>
```

This allows each page to render the same navigation while activating the correct tab depending on the current route.

---

## Shared Layout

Each tab view reuses the same layout.

```html
<twig:tabs:Tabs>
    <twig:tabs:NavItemList>
        {{ include('purchasing/vendor_warranty_claim/partial/tabs/_nav.html.twig', {
            vendorWarrantyClaim: vendorWarrantyClaim,
        }) }}
    </twig:tabs:NavItemList>

    <twig:tabs:Content>
        {{ include('purchasing/vendor_warranty_claim/partial/tabs/_files_tab.html.twig', {
            item: vendorWarrantyClaim,
            form_files: form_files,
        }) }}
    </twig:tabs:Content>
</twig:tabs:Tabs>
```

With this pattern:

- the navigation is shared
- the content is specific to each tab

---

## Template Attribute

When the controller only needs to return template data, the Template attribute can be used:

```php
#[Template('purchasing/vendor_warranty_claim/show_files.html.twig')]
public function __invoke(ApiData $vendorWarrantyClaim): array
{
    return [
        'vendorWarrantyClaim' => $vendorWarrantyClaim,
    ];
}
```

If the tab needs form handling or redirects, the controller can return:

- array
- RedirectResponse

---

## Summary

This tab architecture is based on a simple rule:

- one controller per tab
- one route per tab
- one view per tab
- one shared navigation partial

It keeps the codebase modular and makes each tab easy to understand and maintain.

---

## Re-initialize JavaScript in Tabs (Turbo)

When using Turbo, tab content is dynamically replaced without a full page reload.

As a result, JavaScript executed on the initial page load (such as React mounting or DOM initialization) is **not automatically re-run** when switching tabs.

This behavior is global and applies to any dynamic content rendered inside a Turbo frame.

#### How It Works

To ensure JavaScript is correctly executed after each tab change:

1. Move your initialization logic into a reusable function (e.g. `mountLogsBlocks`)
2. Call this function on the initial page load
3. Re-trigger it after each Turbo frame reload

This mechanism only needs to be implemented once and can then be reused across all modules.

#### Implementation Pattern

The recommended approach is:

- expose a global mount function (scoped if needed)
- attach a Stimulus controller to the Turbo frame
- re-trigger the mount on `turbo:frame-load`

Example:

```js
function mountLogsBlocks(root = document) {
  // initialize your JS / React components
}
```

```ts
// Stimulus controller (simplified)
connect() {
  this.mount();
  document.addEventListener("turbo:frame-load", this.onFrameLoad);
}

onFrameLoad = (event) => {
  if (event.target === this.element) {
    this.mount();
  }
}

mount() {
  window.mountLogsBlocks?.(this.element);
}
```

```twig
<turbo-frame data-controller="logs">
```

#### How to Reuse in Other Modules

To enable this behavior on another module or page:

1. Extract your JavaScript initialization into a reusable function
2. Ensure it can accept a root element (for scoping)
3. Reuse the existing Stimulus controller or create a similar one
4. Attach the controller to the Turbo frame

No additional changes are required once the pattern is in place.

#### Summary

- Turbo replaces DOM without re-running JavaScript
- initialization must be manually re-triggered
- this is solved with a reusable mount function + Turbo event
- the pattern is implemented once and reused everywhere