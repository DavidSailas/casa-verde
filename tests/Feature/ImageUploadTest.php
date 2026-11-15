<?php

use App\Models\HomeContent;
use App\Models\User;
use App\Support\ImageUploader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    if (! ImageUploader::canConvert()) {
        $this->markTestSkipped('The GD extension with WebP support is needed.');
    }

    $this->stored = [];
});

afterEach(function () {
    foreach ($this->stored as $path) {
        @unlink(public_path($path));
    }
});

test('a large photo is shrunk and saved as WebP', function () {
    $path = ImageUploader::store(UploadedFile::fake()->image('Big Beach.jpg', 3600, 2400));
    $this->stored[] = $path;

    expect($path)->toEndWith('.webp')->toContain('big-beach');

    [$width, $height] = getimagesize(public_path($path));

    expect($width)->toBe(ImageUploader::MAX_SIDE)
        ->and($height)->toBe(1333)
        ->and(mime_content_type(public_path($path)))->toBe('image/webp');
});

test('a small photo keeps its size and is still converted', function () {
    $path = ImageUploader::store(UploadedFile::fake()->image('small.png', 400, 300));
    $this->stored[] = $path;

    expect($path)->toEndWith('.webp')
        ->and(getimagesize(public_path($path))[0])->toBe(400);
});

test('an SVG icon is stored untouched', function () {
    $file = UploadedFile::fake()->createWithContent('leaf.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><path d="M0 0h10v10z"/></svg>');

    $path = ImageUploader::store($file);
    $this->stored[] = $path;

    expect($path)->toEndWith('.svg')
        ->and(file_get_contents(public_path($path)))->toContain('<path d="M0 0h10v10z"/>');
});

test('the names follow the site convention so the media library can still read them', function () {
    $path = ImageUploader::store(UploadedFile::fake()->image('villa view.jpg', 800, 600));
    $this->stored[] = $path;

    expect(basename($path))->toMatch('/^\d{8}_\d{6}_[0-9a-f-]{36}_villa-view\.webp$/');
});

test('a photo uploaded in the admin is saved as WebP', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)->post(route('admin.content.store'), [
        'section' => 'zz_upload',
        'title' => 'With photo',
        'image' => UploadedFile::fake()->image('upload.jpg', 2800, 1800),
    ])->assertRedirect();

    $entry = HomeContent::where('section', 'zz_upload')->firstOrFail();
    $this->stored[] = $entry->image;

    expect($entry->image)->toEndWith('.webp')
        ->and(getimagesize(public_path($entry->image))[0])->toBe(ImageUploader::MAX_SIDE);
});
