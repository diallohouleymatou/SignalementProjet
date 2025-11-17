<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Disk
    |--------------------------------------------------------------------------
    |
    | The default disk to use for media uploads.
    | Options: 'public', 'local', 's3'
    |
    */
    'disk' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    |
    | Define which file types are allowed for upload.
    |
    */
    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'application/pdf',
        'video/mp4',
        'video/quicktime',
    ],

    /*
    |--------------------------------------------------------------------------
    | Max File Size
    |--------------------------------------------------------------------------
    |
    | Maximum file size in kilobytes.
    |
    */
    'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 10240), // 10MB par défaut

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Settings
    |--------------------------------------------------------------------------
    |
    | Settings for automatic thumbnail generation.
    |
    */
    'thumbnail' => [
        'width' => 300,
        'height' => 300,
        'quality' => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for image optimization.
    |
    */
    'optimization' => [
        'enabled' => env('MEDIA_OPTIMIZATION_ENABLED', true),
        'quality' => 85,
        'max_width' => 2000,
        'max_height' => 2000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Collections
    |--------------------------------------------------------------------------
    |
    | Define available media collections.
    |
    */
    'collections' => [
        'profile_photos',
        'signalement_photos',
        'documents',
        'videos',
    ],
];
