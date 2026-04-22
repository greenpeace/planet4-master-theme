# AGENTS.md

Project-specific guidance for contributors using AI coding assistants (Codex,
Cursor, Aider, Copilot, Claude Code, Jules, Amp, etc.). All information below is
derived from existing config files in this repository and is intentionally
terse. This file does not enable any automated AI tooling; `.gemini/config.yaml`
still governs Gemini Code Assist for this repo.

See also: [CONTRIBUTING.md](CONTRIBUTING.md), [README.md](README.md), and the
meta repo https://github.com/greenpeace/planet4 for the authoritative
contributor guide.

## Project

- WordPress master theme for the Planet 4 platform.
- PHP 8.3, React, Sass, Playwright.
- Namespace: `P4\MasterTheme\` → `src/` (see `composer.json`).
- License: GPL-3.0-or-later (see `LICENSE`).

## Setup

- Node and npm versions are pinned in `package.json`: Node `>=20.0.0 <22.0.0`,
  npm `>10.0.0 <11.0.0`. `.npmrc` sets `engine-strict=true`.
- `npm install`: installs JS deps.
- `composer install`: installs PHP deps (phpcs, PHPUnit, Timber, etc.).
- For a working local WordPress environment, use
  [`greenpeace/planet4-docker-compose`](https://github.com/greenpeace/planet4-docker-compose).

## Build and lint

- `npm start`: webpack dev build (wp-scripts).
- `npm run build`: webpack production build. Output goes to `assets/build/`
  (gitignored).
- `npm run lint`: runs `lint:js` then `lint:css`.
- `npm run lint:js`: ESLint over `assets/src/**`, `tests/e2e/**`, `admin/js/**`.
- `npm run lint:css`: stylelint over `assets/src/scss/**`.
- `composer sniffs`: phpcs using `phpcs.xml.dist` (PSR-12 + Slevomat +
  WordPress Coding Standards).
- `composer fixes`: phpcbf auto-fix for the same ruleset.

## Tests

- **PHPUnit** lives under `tests/` and is configured by `phpunit.xml.dist`.
  Bootstrap is `tests/bootstrap.php`, which expects `WP_TESTS_DIR`: see
  `bin/install-wp-tests.sh`. The WordPress test suite is easier to run inside
  the docker-compose environment than on host PHP.
- **Playwright** E2E tests live under `tests/e2e/`. Run with
  `npx playwright test`.
- **Accessibility** (pa11y-ci): `.pa11yci` drives the URLs. In CI the
  `pa11y.json` file is regenerated from `.pa11yci` via `jq`
  (see `.circleci/config.yml`).

## Commits

Defined in `commitlint.config.js`. The local `.husky/pre-commit` hook only runs
lint-staged (`lint:js` / `lint:css --fix`); commit-message compliance itself is
enforced by the `Lint commit messages` job in CircleCI, so commits that fail
`commitlint` will only error after push:

- `header-case: sentence-case`: first word capitalised, no other title case.
- `header-min-length: 10`, `header-max-length: 100`.
- `header-full-stop: never`: no trailing period on the subject.
- `body-leading-blank: always`: blank line between subject and body.
- `body-empty: never`: a body is required.
- `body-case: sentence-case`.
- Reference internal tickets with `PLANET-XXXX` in the subject or body when
  applicable.

## CI

- This project uses **CircleCI**, not GitHub Actions. The pipeline is defined
  in `.circleci/config.yml`.
- CI jobs (names as they appear in config): `phpcs`, `phpunit`, `Lint CSS`,
  `Lint JS`, `Lint commit messages`, `Accessibility tests`, `End-to-end tests`,
  plus deploy/test-instance steps.
- PHPCS and PHPUnit run in pinned Docker images. Running phpcs against the
  current `wp-coding-standards/wpcs ^2.3.0` on host PHP 8.4 may surface
  deprecation notices that CI does not see.
- Gemini Code Assist is intentionally disabled for this repo
  (`.gemini/config.yaml`).

## Conventions

- `.editorconfig`: UTF-8, LF, final newline, trim trailing whitespace,
  2-space indent (4 for `*.php` and `*.twig`).
- PHP: PSR-12 + Slevomat + WordPress Coding Standards (see `phpcs.xml.dist`).
  `minimum_supported_wp_version` is set to `6.1` today.
- JS: `@wordpress/eslint-plugin` (see `.eslintrc.json`).
- SCSS: stylelint (see `.stylelintrc`).

## Project structure

- `src/`: PHP classes, PSR-4 autoloaded under `P4\MasterTheme\`.
- `tests/`: PHPUnit unit tests, fixtures in `tests/data/`, and Playwright
  specs in `tests/e2e/`.
- `assets/src/`: React / JS / Sass sources. `assets/build/` is
  build output and is gitignored.
- `admin/`: admin-only PHP assets (`admin/js/`, `admin/css/`).
- `.circleci/`: pipeline config.
- `bin/install-wp-tests.sh`: sets up the PHPUnit WordPress test library.

## Gotchas

- Bug reports and feature work are tracked in internal Jira as `PLANET-XXXX`,
  not GitHub Issues. The top-level `CONTRIBUTING.md` and `README.md` both
  redirect to the meta repo for the real contributor guide.
- `pa11y.json` is committed but is also rewritten by CI from `.pa11yci` before
  every accessibility run: edit `.pa11yci` for URL or default changes, not
  `pa11y.json` directly.
- `phpcs.xml.dist` still carries `@todo: Temporary exclusions for 1st PR on
  6967` exclusions and targets `minimum_supported_wp_version` 6.1; both are
  known-stale and should be handled in a dedicated PR with Docker verification.

## Communication

- Slack: `#planet4` on `greenpeace.slack.com` (external contributors: request
  access via the meta repo).
- Code of Conduct: [`CODE_OF_CONDUCT.md`](CODE_OF_CONDUCT.md).
