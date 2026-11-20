<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AiBrainController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AvailabilityController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryPhotosController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaTrashController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\WebpController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'create'])->name('login');
        Route::post('login', [AuthController::class, 'store']);
    });

    Route::middleware(['auth', 'admin', 'admin.log'])->group(function () {
        Route::post('logout', [AuthController::class, 'destroy'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // My account: every signed-in person can change their own name, e-mail and password.
        Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
        Route::put('account', [AccountController::class, 'update'])->name('account.update');

        // Staff accounts and the history of changes: owners only.
        Route::middleware('admin.area:staff')->group(function () {
            Route::resource('staff', StaffController::class)
                ->except(['show'])
                ->parameters(['staff' => 'user']);
            Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
        });

        // Drag-and-drop ordering and the draft preview must be listed before the content resource.
        Route::middleware('admin.area:content')->group(function () {
            Route::post('content/reorder', [ContentController::class, 'reorder'])->name('content.reorder');
            Route::get('content/{content}/preview', [ContentController::class, 'preview'])->name('content.preview');
        });

        Route::resource('content', ContentController::class)
            ->except(['show'])
            ->parameters(['content' => 'content'])
            ->middleware('admin.area:content');

        // Media library: every photo, icon, symbol and logo, and which ones the site uses now.
        Route::get('media', [MediaController::class, 'index'])->name('media.index')->middleware('admin.area:media');

        // Convert the pictures that were uploaded before WebP became automatic (a few at a time, from the Media library page).
        Route::get('media/webp', [WebpController::class, 'status'])->name('media.webp.status')->middleware('admin.area:media');
        Route::post('media/webp', [WebpController::class, 'run'])->name('media.webp.run')->middleware('admin.area:media');

        // Clean up: pictures go to a recoverable trash first (see App\Support\MediaTrash).
        Route::get('media/cleanup', [MediaTrashController::class, 'status'])->name('media.cleanup.status')->middleware('admin.area:media');
        Route::post('media/cleanup', [MediaTrashController::class, 'move'])->name('media.cleanup.move')->middleware('admin.area:media');
        Route::post('media/cleanup/restore', [MediaTrashController::class, 'restore'])->name('media.cleanup.restore')->middleware('admin.area:media');
        Route::post('media/cleanup/empty', [MediaTrashController::class, 'empty'])->name('media.cleanup.empty')->middleware('admin.area:media');

        // Gallery photos: see and change every photo on the Gallery page in one place.
        Route::prefix('gallery')->name('gallery.')->middleware('admin.area:gallery')->controller(GalleryPhotosController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::put('{content}', 'update')->name('update');
            Route::post('{content}/move', 'move')->name('move');
            Route::delete('{content}', 'destroy')->name('destroy');
        });

        // AI Brain: teach the website assistant, manage what it knows, see what guests ask, test it.
        Route::prefix('ai-brain')->name('ai-brain.')->middleware('admin.area:ai-brain')->controller(AiBrainController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('entries', 'storeEntry')->name('entries.store');
            Route::put('entries/{id}', 'updateEntry')->name('entries.update')->where('id', '[a-z0-9]+');
            Route::post('entries/{id}/toggle', 'toggleEntry')->name('entries.toggle')->where('id', '[a-z0-9]+');
            Route::delete('entries/{id}', 'destroyEntry')->name('entries.destroy')->where('id', '[a-z0-9]+');
            Route::post('questions/{id}/done', 'questionDone')->name('questions.done')->where('id', '[a-z0-9]+');
            Route::post('teach', 'teach')->name('teach')->middleware('throttle:20,1');
            Route::post('drafts', 'saveDrafts')->name('drafts');
            Route::post('test', 'test')->name('test')->middleware('throttle:30,1');
            Route::post('connection', 'saveConnection')->name('connection.save');
            Route::post('connection/check', 'checkConnection')->name('connection.check')->middleware('throttle:10,1');
            Route::delete('connection', 'removeConnection')->name('connection.remove');
        });

        // Booking requests sent from the website's Book page.
        // Website languages: which are offered and how a first-time visitor's is picked.
        Route::middleware('admin.area:languages')->group(function () {
            Route::get('languages', [LanguageController::class, 'edit'])->name('languages.edit');
            Route::put('languages', [LanguageController::class, 'update'])->name('languages.update');
        });

        Route::middleware('admin.area:bookings')->group(function () {
            Route::get('bookings/export', [AdminBookingController::class, 'export'])->name('bookings.export');
            Route::resource('bookings', AdminBookingController::class)
                ->only(['index', 'update', 'destroy']);
        });

        // Availability: month calendar, closed dates, seasonal rates and the number of villas per type.
        Route::get('availability', [AvailabilityController::class, 'index'])->name('availability.index')->middleware('admin.area:availability');
        Route::post('availability/blocks', [AvailabilityController::class, 'storeBlock'])->name('availability.blocks.store')->middleware('admin.area:availability');
        Route::put('availability/blocks/{blockedDate}', [AvailabilityController::class, 'updateBlock'])->name('availability.blocks.update')->middleware('admin.area:availability');
        Route::delete('availability/blocks/{blockedDate}', [AvailabilityController::class, 'destroyBlock'])->name('availability.blocks.destroy')->middleware('admin.area:availability');
        Route::post('availability/seasons', [AvailabilityController::class, 'storeSeason'])->name('availability.seasons.store')->middleware('admin.area:availability');
        Route::put('availability/seasons/{seasonalRate}', [AvailabilityController::class, 'updateSeason'])->name('availability.seasons.update')->middleware('admin.area:availability');
        Route::delete('availability/seasons/{seasonalRate}', [AvailabilityController::class, 'destroySeason'])->name('availability.seasons.destroy')->middleware('admin.area:availability');
        Route::put('availability/units', [AvailabilityController::class, 'updateUnits'])->name('availability.units.update')->middleware('admin.area:availability');

        // Reviews written by guests on the website: show, hide or delete them.
        Route::post('reviews/sync-google', [AdminReviewController::class, 'syncGoogle'])
            ->name('reviews.sync-google')->middleware(['throttle:6,1', 'admin.area:reviews']);
        Route::resource('reviews', AdminReviewController::class)
            ->only(['index', 'update', 'destroy'])
            ->middleware('admin.area:reviews');
    });

});
