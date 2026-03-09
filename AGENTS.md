# D2U Videos - Redaxo Addon

A Redaxo 5 CMS addon for managing videos (YouTube and self-hosted) and playlists. Supports multiple video players (Ultimate Video Player, Plyr, Vidstack), schema.org LD+JSON markup, and video sitemap entries for YRewrite.

## Tech Stack

- **Language:** PHP >= 8.0
- **CMS:** Redaxo >= 5.10.0
- **Frontend Framework:** Bootstrap 4/5 (via d2u_helper templates)
- **Namespace:** `TobiasKrais\D2UVideos`

## Project Structure

```text
d2u_videos/
├── boot.php               # Addon bootstrap (extension points, permissions, video sitemap)
├── install.php             # Installation (database tables, media manager types)
├── update.php              # Update (calls install.php)
├── uninstall.php           # Cleanup (database tables, media manager types)
├── package.yml             # Addon configuration, version, dependencies
├── README.md
├── assets/
│   └── minimal_skin_dark/  # Ultimate Video Player skin assets
├── lang/                   # Backend translations (de_de, en_gb)
├── lib/                    # PHP classes
│   ├── Video.php           # Video model (multilingual, LD+JSON, sitemap)
│   ├── Playlist.php        # Playlist model
│   ├── Videomanager.php    # Video player rendering (Ultimate/Plyr/Vidstack)
│   ├── Module.php          # Module definitions and revisions
│   └── deprecated_classes.php  # Backward compatibility (since 1.2.0)
├── modules/                # 1 module in group 30
│   └── 30/
│       └── 1/              # Video/Playlist output
│           ├── input.php
│           └── output.php
└── pages/                  # Backend pages
    ├── index.php           # Page router
    ├── videos.php          # Video management (CRUD)
    ├── playlist.php        # Playlist management
    ├── settings.php        # Player settings (type, JS file, dimensions)
    ├── setup.php           # Module manager + FAQ
    └── help.changelog.php  # Changelog
```

## Coding Conventions

- **Namespace:** `TobiasKrais\D2UVideos` for all classes
- **Deprecated:** Global `Playlist`, `Video`, `Videomanager` aliases (since 1.2.0)
- **Naming:** camelCase for variables, PascalCase for classes
- **Indentation:** 4 spaces in PHP classes, tabs in module files
- **Comments:** English comments only
- **Backend labels:** Use `rex_i18n::msg()` with keys from `lang/` files

## Key Classes

| Class | Description |
| ----- | ----------- |
| `Video` | Video model: name, teaser, YouTube/Redaxo video, preview picture, priority, LD+JSON output, sitemap XML. Multilingual with language fallback. Implements `ITranslationHelper` |
| `Playlist` | Playlist model: name, ordered video IDs (pipe-separated) |
| `Videomanager` | Video player renderer: supports Ultimate Video Player, Plyr and Vidstack. Single video, multi-video, and playlist output |
| `Module` | Module definitions: 1 module (30-1) |

## Database Tables

| Table | Description |
| ----- | ----------- |
| `rex_d2u_videos_videos` | Videos (language-independent): priority, picture, video type, YouTube ID, Redaxo file |
| `rex_d2u_videos_videos_lang` | Videos (language-specific): name, teaser, picture, video type, YouTube ID, Redaxo file, translation status |
| `rex_d2u_videos_playlists` | Playlists: name, video IDs (pipe-separated) |

## Architecture

### Extension Points

| Extension Point | Location | Purpose |
| --------------- | -------- | ------- |
| `CLANG_DELETED` | boot.php (backend) | Cleans up language-specific video data |
| `D2U_HELPER_TRANSLATION_LIST` | boot.php (backend) | Registers addon in D2U Helper translation manager |
| `MEDIA_IS_IN_USE` | boot.php (backend) | Prevents deletion of media files used by videos |
| `YREWRITE_SITEMAP` | boot.php (frontend) | Adds `<video:video>` entries to YRewrite sitemap |

### Video Types

- `youtube` — YouTube video (ID or full URL)
- `redaxo` — Self-hosted MP4 file from Redaxo media pool

### Video Player Support

| Player | Type | Description |
| ------ | ---- | ----------- |
| Ultimate Video Player | Commercial | FWDUVPlayer.js with custom skin |
| Plyr | Open Source | Via Plyr Redaxo addon |
| Vidstack | Open Source | Via Vidstack Redaxo addon |

### Schema.org / LD+JSON

`Video::getLDJSONScript()` generates VideoObject markup (only for Redaxo-hosted files with preview picture).

### Video Sitemap

`Video::getSitemapEntry()` generates `<video:video>` XML entries for the YRewrite sitemap.

### Modules

1 module in group 30:

| Module | Name | Description |
| ------ | ---- | ----------- |
| 30-1 | D2U Videomanager - Video / Playlist | Single video or playlist output with LD+JSON |

#### Module Versioning

Each module has a revision number defined in `lib/Module.php` inside the `getModules()` method. When a module is changed:

1. Add a changelog entry in `pages/help.changelog.php` describing the change.
2. Increment the module's revision number in `Module::getModules()` by one.

**Important:** The revision only needs to be incremented **once per release**, not per commit. Check the changelog: if the version number is followed by `-DEV`, the release is still in development and no additional revision bump is needed.

### Media Manager Types

| Type | Purpose |
| ---- | ------- |
| `d2u_videos_preview` | Large preview image in player (1200×510) |
| `d2u_videos_thumb` | Small thumbnail in list (780×404) |

## Settings

Managed via `pages/settings.php` and stored in `rex_config`:

- `player` — Player type: `ultimate`, `plyr` or `vidstack`
- `player_js` — Media file for player JS
- `max_height` — Maximum player height (default: 440)
- `max_width` — Maximum player width (default: 1180)

## Dependencies

| Package | Version | Purpose |
| ------- | ------- | ------- |
| `d2u_helper` | >= 1.14.0 | Backend/frontend helpers, module manager, translation interface |

## Multi-language Support

- **Backend:** de_de, en_gb
- **Data:** Multilingual via `rex_d2u_videos_videos_lang` table with language fallback

## Versioning

This addon follows [Semantic Versioning](https://semver.org/):

- **Major** (1st digit): Breaking changes (e.g. removed classes, renamed methods, incompatible DB changes)
- **Minor** (2nd digit): New features, new modules, new database fields (backward compatible)
- **Patch** (3rd digit): Bug fixes, small improvements (backward compatible)

The version number is maintained in `package.yml`. During development, the changelog uses a `-DEV` suffix.

## Changelog

The changelog is located in `pages/help.changelog.php`.
