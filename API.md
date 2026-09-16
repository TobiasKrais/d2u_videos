# REST API

Das Addon d2u_videos stellt eine REST API bereit, mit der externe Werkzeuge Videos und Wiedergabelisten auslesen und einspielen können – inklusive Texten, Vorschaubildern, Videodateien und YouTube-IDs.

Die API baut auf dem Addon [`api`](https://github.com/FriendsOfREDAXO/api) auf. Alle Endpunkte erscheinen automatisch in dessen OpenAPI-/Swagger-Ansicht und unter `/api/me`.

## Voraussetzungen

- Addon `api` (FriendsOfREDAXO) installiert und aktiviert.
- Ein API-Token mit den benötigten Scopes (siehe unten).

### Authorization-Header

Manche Apache-Konfigurationen entfernen den `Authorization`-Header. Falls Aufrufe trotz gültigem Token mit `401` beantwortet werden, muss der Header durchgereicht werden. Dazu in der `.htaccess` im Projektstamm direkt nach `RewriteEngine On` ergänzen:

```apache
RewriteCond %{HTTP:Authorization} .
RewriteRule ^ - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
```

## Authentifizierung

Jeder Aufruf benötigt ein Bearer-Token aus dem `api`-Addon. Das Token wird im Backend unter **API › Token** angelegt; dort werden ihm die benötigten Scopes zugewiesen.

```
Authorization: Bearer DEIN_TOKEN
```

## Basis-URL

```
https://deine-domain.tld/api/d2u_videos/...
```

## Scopes

Die Scopes folgen dem Schema `d2u_videos/<ressource>/<operation>`. Der Schema-Endpunkt benötigt nur ein gültiges Token (keinen eigenen Scope).

| Operation | Scope |
| --- | --- |
| Schema/Discovery | `d2u_videos/schema` (kein Scope nötig) |
| Liste | `d2u_videos/<ressource>/list` |
| Einzeln lesen | `d2u_videos/<ressource>/get` |
| Anlegen | `d2u_videos/<ressource>/create` |
| Ändern | `d2u_videos/<ressource>/update` |
| Löschen | `d2u_videos/<ressource>/delete` |

## Discovery / Schema-Abfrage

Der Schema-Endpunkt liefert eine maschinenlesbare Beschreibung: Addon-Version, Sprachen sowie pro Ressource die verfügbaren Felder mit Typ, Pflichtangabe, Sprachabhängigkeit und Relation.

```bash
curl -H "Authorization: Bearer DEIN_TOKEN" \
  https://deine-domain.tld/api/d2u_videos/schema
```

## Ressourcen

| Ressource | ID-Feld | Beschreibung |
| --- | --- | --- |
| `videos` | `video_id` | Einzelvideos (REDAXO-Datei oder YouTube), sprachabhängige Felder |
| `playlists` | `playlist_id` | Wiedergabelisten (Name + Liste von Video-IDs), nicht sprachabhängig |

## Endpunkte

Pro Ressource stehen die folgenden Endpunkte bereit:

| Methode | Pfad | Beschreibung |
| --- | --- | --- |
| `GET` | `/api/d2u_videos/<ressource>` | Liste (Query: `clang_id`, `page`, `per_page`) |
| `GET` | `/api/d2u_videos/<ressource>/{id}` | Einzelnen Datensatz inkl. Übersetzungen lesen |
| `POST` | `/api/d2u_videos/<ressource>` | Datensatz anlegen |
| `PUT`/`PATCH` | `/api/d2u_videos/<ressource>/{id}` | Datensatz ändern |
| `DELETE` | `/api/d2u_videos/<ressource>/{id}` | Datensatz löschen |

> Hinweis: `PUT`, `PATCH` und `DELETE` müssen serverseitig erlaubt sein. Manche Apache-Konfigurationen blockieren diese Methoden (Antwort: HTTP 403 als HTML).

## Payload-Aufbau

Nicht sprachabhängige Felder liegen unter `fields`, sprachabhängige je Sprach-ID (clang) unter `translations`. Unbekannte Felder werden mit `HTTP 400` abgelehnt.

Ein Video anlegen (nicht sprachabhängige Grunddaten + je Sprache Name/Teaser):

```json
{
  "fields": {
    "priority": 1,
    "video_type": "redaxo",
    "redaxo_file": "imagefilm.mp4",
    "picture": "imagefilm_poster.jpg"
  },
  "translations": {
    "1": { "name": "Imagefilm", "teaser": "Unser Unternehmen" },
    "2": { "name": "Company movie", "teaser": "About us" }
  }
}
```

Eine Wiedergabeliste anlegen (nur `fields`, keine Übersetzungen):

```json
{
  "fields": {
    "name": "Startseite",
    "video_ids": [3, 7, 12]
  }
}
```

### Video-Felder

Videos besitzen sowohl sprachübergreifende als auch sprachspezifische Varianten. Ist ein sprachspezifisches Feld (z. B. `redaxo_file_lang`) gesetzt, hat es im Frontend Vorrang vor der sprachübergreifenden Variante (`redaxo_file`).

| Feld | Typ | Sprachabhängig | Beschreibung |
| --- | --- | --- | --- |
| `priority` | `int` | nein | Sortierung |
| `video_type` | `enum:redaxo,youtube` | nein | Videotyp |
| `youtube_video_id` | `string` | nein | YouTube-ID |
| `redaxo_file` | `media` | nein | Videodatei (z. B. MP4) |
| `picture` | `media` | nein | Vorschaubild |
| `name` | `string` | **ja** | Titel |
| `teaser` | `string` | **ja** | Kurztext |
| `video_type_lang` | `enum:redaxo,youtube` | **ja** | Videotyp (sprachspezifisch) |
| `youtube_video_id_lang` | `string` | **ja** | YouTube-ID (sprachspezifisch) |
| `redaxo_file_lang` | `media` | **ja** | Videodatei (sprachspezifisch) |
| `picture_lang` | `media` | **ja** | Vorschaubild (sprachspezifisch) |

### Feldtypen

| Typ | Bedeutung |
| --- | --- |
| `string` | Zeichenkette |
| `int` | Ganzzahl |
| `int[]` | Liste von IDs (z. B. Video-IDs einer Playlist) |
| `media` | Dateiname aus dem Medienpool |
| `enum:a,b` | Fester Wertebereich |

## Medien hochladen

Vorschaubilder und Videodateien werden zuerst über den Medien-Endpunkt des `api`-Addons hochgeladen und anschließend per Dateiname referenziert:

1. `POST /api/media` (multipart) im `api`-Addon → liefert den Dateinamen.
2. Den Dateinamen in `picture`/`redaxo_file` (bzw. den `*_lang`-Varianten) eintragen.

## Beispiele

Videos auflisten:

```bash
curl -H "Authorization: Bearer DEIN_TOKEN" \
  "https://deine-domain.tld/api/d2u_videos/videos?per_page=20"
```

Playlist anlegen:

```bash
curl -X POST \
  -H "Authorization: Bearer DEIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"fields":{"name":"Startseite","video_ids":[3,7,12]}}' \
  https://deine-domain.tld/api/d2u_videos/playlists
```

## Fehlercodes

| Code | Bedeutung |
| --- | --- |
| `400` | Ungültiger Payload, unbekanntes Feld oder fehlendes Pflichtfeld |
| `401` | Kein oder ungültiges Token bzw. fehlender Scope |
| `404` | Ressource nicht verfügbar oder Datensatz nicht gefunden |
| `500` | Interner Fehler (Details im REDAXO-Systemlog) |
