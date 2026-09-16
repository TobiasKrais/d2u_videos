<?php

namespace TobiasKrais\D2UVideos\Api;

use rex_addon;
use rex_clang;
use TobiasKrais\D2UVideos\Playlist;
use TobiasKrais\D2UVideos\Video;

/**
 * Single source of truth for the d2u_videos REST API.
 *
 * Describes every writable/readable resource field and whether it is language
 * specific. The same definition drives the discovery endpoint, the write
 * validation and the payload-to-model mapping.
 */
final class Schema
{
    /**
     * Resource meta: model class, id field and whether save() returns the error
     * flag (true on error) instead of a success flag (true on success).
     *
     * @var array<string,array{class:class-string,id:string,save_error_flag:bool}>
     */
    private const RESOURCES = [
        'videos' => ['class' => Video::class, 'id' => 'video_id', 'save_error_flag' => true],
        'playlists' => ['class' => Playlist::class, 'id' => 'playlist_id', 'save_error_flag' => false],
    ];

    /**
     * Field definitions per resource.
     *
     * Each field: type, required (on create), language (per clang), relation
     * (target resource for id references).
     *
     * @var array<string,array<string,array{type:string,required?:bool,language?:bool,relation?:string,seo?:string}>>
     */
    private const FIELDS = [
        'videos' => [
            'priority' => ['type' => 'int'],
            'video_type' => ['type' => 'enum:redaxo,youtube'],
            'youtube_video_id' => ['type' => 'string'],
            'redaxo_file' => ['type' => 'media'],
            'picture' => ['type' => 'media'],
            'name' => ['type' => 'string', 'language' => true, 'required' => true],
            'teaser' => ['type' => 'string', 'language' => true],
            'video_type_lang' => ['type' => 'enum:redaxo,youtube', 'language' => true],
            'youtube_video_id_lang' => ['type' => 'string', 'language' => true],
            'redaxo_file_lang' => ['type' => 'media', 'language' => true],
            'picture_lang' => ['type' => 'media', 'language' => true],
        ],
        'playlists' => [
            'name' => ['type' => 'string', 'required' => true],
            'video_ids' => ['type' => 'int[]', 'relation' => 'videos'],
        ],
    ];

    /**
     * @return array<int,string> Resource keys
     */
    public static function getAvailableResources(): array
    {
        return array_keys(self::RESOURCES);
    }

    public static function hasResource(string $resource): bool
    {
        return isset(self::RESOURCES[$resource]);
    }

    /**
     * @return array{class:class-string,id:string,save_error_flag:bool}
     */
    public static function getResourceMeta(string $resource): array
    {
        return self::RESOURCES[$resource];
    }

    /**
     * @return array<string,array{type:string,required?:bool,language?:bool,relation?:string,seo?:string}>
     */
    public static function getFields(string $resource): array
    {
        return self::FIELDS[$resource] ?? [];
    }

    public static function isLanguageField(string $resource, string $field): bool
    {
        return (bool) (self::FIELDS[$resource][$field]['language'] ?? false);
    }

    /**
     * Full machine-readable capability document for the discovery endpoint.
     *
     * @return array<string,mixed>
     */
    public static function describe(): array
    {
        $languages = [];
        foreach (rex_clang::getAll() as $clang) {
            $languages[] = [
                'id' => $clang->getId(),
                'code' => $clang->getCode(),
                'name' => $clang->getName(),
            ];
        }

        $resources = [];
        foreach (self::getAvailableResources() as $resource) {
            $fields = [];
            foreach (self::getFields($resource) as $name => $definition) {
                $fields[] = [
                    'name' => $name,
                    'type' => $definition['type'],
                    'required' => (bool) ($definition['required'] ?? false),
                    'language' => (bool) ($definition['language'] ?? false),
                    'relation' => $definition['relation'] ?? null,
                    'seo' => $definition['seo'] ?? null,
                ];
            }

            $resources[$resource] = [
                'id_field' => self::RESOURCES[$resource]['id'],
                'endpoints' => [
                    'list' => 'GET /api/d2u_videos/' . $resource,
                    'get' => 'GET /api/d2u_videos/' . $resource . '/{id}',
                    'create' => 'POST /api/d2u_videos/' . $resource,
                    'update' => 'PATCH /api/d2u_videos/' . $resource . '/{id}',
                    'delete' => 'DELETE /api/d2u_videos/' . $resource . '/{id}',
                ],
                'fields' => $fields,
            ];
        }

        return [
            'addon' => 'd2u_videos',
            'version' => (string) rex_addon::get('d2u_videos')->getVersion(),
            'languages' => $languages,
            'notes' => [
                'Language specific fields are provided per clang inside "translations".',
                'Non-language fields are provided under "fields".',
                'Upload media via the api addon endpoint POST /api/media first, then reference the returned file name.',
                'Only fields listed here may be written; unknown fields are rejected with HTTP 400.',
            ],
            'resources' => $resources,
        ];
    }
}
