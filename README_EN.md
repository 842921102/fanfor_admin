# Fanfou (Current Version)

中文: [README.md](./README.md)

## Versioning

- **Current version**: `1.0.0` (see [`VERSION`](./VERSION) at repo root)
- **History**: [`CHANGELOG.md`](./CHANGELOG.md)

When cutting a release, bump `VERSION`, update `CHANGELOG.md`, and keep `mini-fan-package/package.json` and `mini-fan-package/src/manifest.json` (`versionName` / `versionCode`) in sync (see notes at the top of the changelog).

---

This repository now keeps only these parts:

- `admin-backend`: Laravel admin backend and APIs (including mini-program generative AI)
- `mini-fan-package`: WeChat mini program frontend

Legacy Android project, old web frontend, the former Node BFF, and unrelated deployment files were removed.

## Feature Scope

- Today Eat / Table Menu / Fortune Cooking / Sauce Design / Gallery
- Favorites, histories, unified result detail, replay, and secondary actions
- Admin data management menus per feature
- AI model center (providers, models, scene configs, connection test logs)

## Local Run

From repository root:

```bash
# Mini program dev
npm run dev:mp-weixin

# Laravel (default http://127.0.0.1:8000)
npm run admin:serve
```

Or run each project directly:

```bash
cd mini-fan-package && npm run dev:mp-weixin
cd admin-backend && php artisan serve
```

## Structure

```text
admin-backend/      Laravel admin and APIs
mini-fan-package/   WeChat mini program
```
