# Automated Releases

## Goal

- **Pre-releases** when code is merged into `develop` (after tests and secret scan pass).
- **Full releases** when code is merged into `main` (after tests and secret scan pass).
- Version base: **v1.0.8**. All new versions are derived from existing tags after that.

## Flow

1. **Branch protection** (Settings → Branches) ensures PRs into `develop` and `main` only merge when **Tests** and **Secret Scan** pass.
2. On **merge into `develop`** → workflow **Pre-release** runs → creates a **pre-release** (e.g. `v1.0.9-dev.1`, `v1.0.9-dev.2`).
3. On **merge into `main`** → workflow **Release** runs → creates a **full release** (e.g. `v1.0.9`, `v1.1.0`).

No release is created unless the merge has already passed CI; the release workflows only run on push to the branch.

## Version rules

| Branch   | Trigger       | Version pattern        | Example              |
|----------|---------------|------------------------|----------------------|
| `develop`| Push (merge)  | `v{next}-dev.{N}`      | v1.0.9-dev.1         |
| `main`   | Push (merge)  | `v{major}.{minor}.{patch}` (patch bump) | v1.0.9 |

- **main:** Latest tag is read (e.g. `v1.0.8`). Next version = patch bump → `v1.0.9`. Tag and GitHub Release are created.
- **develop:** Latest *stable* tag (e.g. `v1.0.8`) gives next base `v1.0.9`. Count existing `v1.0.9-dev.*` tags; next pre-release = `v1.0.9-dev.(count+1)`.

If no tag exists yet, the workflow falls back to base `v1.0.8` (so first release from main is `v1.0.9`).

## Workflows

| File               | Trigger        | Job summary |
|--------------------|----------------|-------------|
| `release.yml`      | Push to `main` | Create tag + GitHub Release (full release). |
| `pre-release.yml`  | Push to `develop` | Create tag + GitHub Pre-release. |

Both need `contents: write` to create tags and releases.

## Skip release

To merge without creating a release, include `[skip release]` in the merge commit message (e.g. "Merge PR #123 [skip release]").

## Optional later improvements

- **Conventional commits:** Bump minor/major from commit messages (`feat:`, `BREAKING CHANGE:`).
- **Changelog:** Generate release notes from commits or a CHANGELOG file.
- **Manual trigger:** Add `workflow_dispatch` to run a release from the Actions tab.
- **Version in composer.json:** Read version from package and tag that (instead of auto patch bump).
