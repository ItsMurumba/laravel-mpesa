# Automated Releases

## Goal

- **Pre-releases** when code is merged into `develop` (after tests and secret scan pass).
- **Full releases** when code is merged into `main` (after tests and secret scan pass).
- Version base: **v1.0.8**. All new versions are derived from existing tags after that.

## Flow

1. **Branch protection** (Settings → Branches) ensures PRs into `develop` and `main` only merge when **Tests** and **Secret Scan** pass.
2. On **merge into `develop`** → workflow **Pre-release** runs → creates a **pre-release** (e.g. `v1.0.9-beta.1`, `v1.0.9-beta.2`).
3. On **merge into `main`** → workflow **Release** runs → creates a **full release** (e.g. `v1.0.9`, `v1.1.0`).

No release is created unless the merge has already passed CI; the release workflows only run on push to the branch.

## Version rules

| Branch   | Trigger       | Version pattern        | Example              |
|----------|---------------|------------------------|----------------------|
| `develop`| Push (merge)  | `v{next}-beta.{N}`     | v1.0.9-beta.1        |
| `main`   | Push (merge)  | `v{major}.{minor}.{patch}` (patch bump unless the merge message says otherwise) | v1.0.9 |

- **main:** Latest *stable* `vX.Y.Z` tag is read (e.g. `v1.0.8`; pre-release tags such as `v1.0.9-beta.1` or `v2.0.0-rc.1` are skipped). Next version = patch bump → `v1.0.9`, or a minor/major bump when the merge commit message contains `[minor]` or `[major]` (see below). Tag and GitHub Release are created on the merged commit.
- **develop:** Latest *stable* tag (e.g. `v1.0.8`) gives next base `v1.0.9`, unless a release candidate tag is higher (e.g. `v2.0.0-rc.1`), in which case its version is the base (`v2.0.0`). The next pre-release is one past the highest existing `-beta.N` for that base, tagged on the merged commit. Install one with `composer require itsmurumba/laravel-mpesa:2.0.0-beta.1`.

Pre-releases tagged before 2.0 use `-dev.N` (e.g. `v1.0.9-dev.10`). Composer doesn't accept that format, so Packagist doesn't list them and they can't be installed.

If no tag exists yet, the workflow falls back to base `v1.0.8` (so first release from main is `v1.0.9`).

## Workflows

| File               | Trigger        | Job summary |
|--------------------|----------------|-------------|
| `release.yml`      | Push to `main` | Create tag + GitHub Release (full release). |
| `pre-release.yml`  | Push to `develop` | Create tag + GitHub Pre-release. |

Both need `contents: write` to create tags and releases.

## Minor and major releases

Put `[minor]` or `[major]` in the merge commit message, for example in the title of the `develop` → `main` pull request (GitHub adds the PR title to the merge commit):

| Marker | From `v1.0.8` |
|---|---|
| none | `v1.0.9` |
| `[minor]` | `v1.1.0` |
| `[major]` | `v2.0.0` |

Use `[minor]` for new features and `[major]` when existing code has to change to keep working.

## Release candidates

To try a release before it goes to `main`, tag the `develop` commit as a GitHub pre-release by hand, for example `v2.0.0-rc.1`, and install it with `composer require itsmurumba/laravel-mpesa:2.0.0-rc.1`. The release workflow on `main` ignores these tags when it works out the next version; set the version with `[minor]` or `[major]` as usual. Pre-releases from `develop` use the candidate's version, so after `v2.0.0-rc.1` they are `v2.0.0-beta.N`.

## Skip release

To merge without creating a release, include `[skip release]` in the merge commit message (e.g. "Merge PR #123 [skip release]").

## Optional later improvements

- **Changelog:** Generate release notes from commits or a CHANGELOG file.
- **Manual trigger:** Add `workflow_dispatch` to run a release from the Actions tab.
- **Version in composer.json:** Read version from package and tag that (instead of auto patch bump).
