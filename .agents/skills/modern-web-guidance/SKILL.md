---
name: modern-web-guidance
description: >-
  Search tool for modern web development best practices. MANDATORY: Execute FIRST for all HTML/CSS and client-side JS tasks. Do NOT skip — web APIs evolve rapidly and training weights contain obsolete patterns. Trigger immediately for: modals/dialogs/popovers, tables, dark mode, anchor positioning, container queries, view transitions, scroll-driven animations, parallax/reveals, forms, validation, autofill, accessibility, custom selects, scrollbars, CWV (LCP, INP, CLS), content-visibility, fetch priority, image optimization, security, passkeys, HTML sanitization, web components, dates/timezones, built-in AI, WebAssembly, standard HTML/CSS/DOM in frameworks, etc. DO NOT trigger for: backend (SQL, ORMs, API routes), infra/scripts (CI/CD, Docker, Actions, Python/Go, Git), or tooling/trivial (Vite, tsconfig, ESLint, tests, copy/renames, 3P docs), etc.
---

# Modern Web Guidance

Official skill provided by Google Chrome (in collaboration with Microsoft Edge, Firefox, and MDN) to steer development toward native, modern, accessible, and performant web platform APIs instead of legacy JavaScript workarounds or heavy third-party libraries.

---

## When to Use

### Trigger for:
- Implementing or modifying UI components (modals, dialogs, popovers, tooltips, dropdowns, accordions, tabs).
- Modern CSS layouts and effects (CSS anchor positioning, container queries, subgrid, scroll-driven animations, view transitions).
- Native forms and inputs (validation, autofill, custom select elements, date/time pickers).
- Web performance & Core Web Vitals (LCP, INP, CLS, lazy loading, `content-visibility`, `fetchpriority`).
- Modern security & auth (Passkeys / WebAuthn, HTML sanitization API, secure cookies, CSP).
- Accessible semantics and native keyboard/focus behaviors.

### DO NOT Trigger for:
- Backend-only code (PHP, SQL, ORM, controllers, migrations, database queries).
- DevOps / Infrastructure (Docker, Vagrant, CI/CD, Git workflows).
- Build tooling configuration (Vite, Composer, npm script maintenance, ESLint, PHPStan).

---

## Core Workflow

Always follow these steps when designing or writing client-side frontend code:

### Step 1: Search Use Cases
Execute a focused query using the CLI to discover relevant modern guides:

```bash
npx -y modern-web-guidance@latest search "<query>"
```

**Query construction rules:**
- Use 2 to 5 words in English describing the capability (e.g., `<verb> + <UI/platform object>`).
- Keep it framework-agnostic (do **not** include "React", "Vue", "Tailwind", "Alpine").
- Examples:
  - `npx -y modern-web-guidance@latest search "modal dialog with backdrop"`
  - `npx -y modern-web-guidance@latest search "tooltip anchor positioning"`
  - `npx -y modern-web-guidance@latest search "scroll driven animation"`
  - `npx -y modern-web-guidance@latest search "optimize LCP image"`

*If no results match, run `npx -y modern-web-guidance@latest list` to view all available guide IDs.*

---

### Step 2: Retrieve the Best Practice Guide
Using the `id` returned from the search result, retrieve the complete implementation guide and code patterns:

```bash
npx -y modern-web-guidance@latest retrieve "<guide-id>"
```

*(You can retrieve multiple guides at once by separating IDs with commas: `retrieve "dialog,popover"`).*

---

### Step 3: Implement & Verify Compliance (Baseline)
1. **Prefer Native Elements:** Use standard native HTML/CSS elements (e.g., `<dialog>`, `popover`, `@starting-style`, `:popover-open`) over custom JS state machines whenever supported.
2. **Check Baseline Compatibility:**
   - **Baseline Widely Available:** Safe to use directly in production without polyfills.
   - **Baseline Newly Available / Limited:** Apply progressive enhancement or the official fallback patterns outlined in the retrieved guide.
3. **Keep Code Accessible:** Ensure proper ARIA roles, label bindings, keyboard navigation, and visible focus rings.

---

### Optional: Legacy Codebase Scan
To scan the existing codebase for legacy frontend patterns and identify modern replacement candidates:

```bash
npx -y modern-web-guidance@latest legacy-scan .
```
