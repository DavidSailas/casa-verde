<?php

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function staffMember(string $role, array $extra = []): User
{
    return User::factory()->create(array_merge(['is_admin' => true, 'role' => $role], $extra));
}

test('an account from before roles existed keeps full access', function () {
    $legacy = User::factory()->create(['is_admin' => true]); // no role set

    $this->actingAs($legacy)->get(route('admin.bookings.index'))->assertOk();
    $this->actingAs($legacy)->get(route('admin.staff.index'))->assertOk();
    $this->actingAs($legacy)->get(route('admin.activity.index'))->assertOk();
});

test('an editor can work on content but not on bookings, availability, staff or history', function () {
    $editor = staffMember('editor');

    $this->actingAs($editor)->get(route('admin.dashboard'))->assertOk();
    $this->actingAs($editor)->get(route('admin.content.index'))->assertOk();
    $this->actingAs($editor)->get(route('admin.media.index'))->assertOk();

    $this->actingAs($editor)->get(route('admin.bookings.index'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.bookings.export'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.availability.index'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.reviews.index'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.staff.index'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.activity.index'))->assertForbidden();
});

test('a manager can run bookings and availability but not staff accounts', function () {
    $manager = staffMember('manager');

    $this->actingAs($manager)->get(route('admin.bookings.index'))->assertOk();
    $this->actingAs($manager)->get(route('admin.availability.index'))->assertOk();
    $this->actingAs($manager)->get(route('admin.reviews.index'))->assertOk();

    $this->actingAs($manager)->get(route('admin.staff.index'))->assertForbidden();
    $this->actingAs($manager)->post(route('admin.staff.store'), [
        'name' => 'Sneaky', 'email' => 'sneaky@example.com', 'role' => 'owner', 'password' => 'a-long-password',
    ])->assertForbidden();

    expect(User::where('email', 'sneaky@example.com')->exists())->toBeFalse();
});

test('the sidebar only shows what the role can open', function () {
    $editor = staffMember('editor');

    $this->actingAs($editor)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee(route('admin.bookings.index'), false)
        ->assertDontSee(route('admin.staff.index'), false)
        ->assertSee(route('admin.account.edit'), false);
});

test('an owner adds a person, who must choose their own password before anything else', function () {
    $owner = staffMember('owner');

    $this->actingAs($owner)->post(route('admin.staff.store'), [
        'name' => 'Ana Reyes', 'email' => 'ana@example.com', 'role' => 'editor', 'password' => 'first-password-1',
    ])->assertRedirect(route('admin.staff.index'));

    $ana = User::where('email', 'ana@example.com')->firstOrFail();

    expect($ana->is_admin)->toBeTrue()
        ->and($ana->roleKey())->toBe('editor')
        ->and($ana->must_change_password)->toBeTrue()
        ->and(Hash::check('first-password-1', $ana->password))->toBeTrue();

    // Until she picks her own password every page sends her to My account.
    $this->actingAs($ana)->get(route('admin.content.index'))->assertRedirect(route('admin.account.edit'));
    $this->actingAs($ana)->get(route('admin.account.edit'))->assertOk();

    $this->actingAs($ana)->put(route('admin.account.update'), [
        'name' => 'Ana Reyes', 'email' => 'ana@example.com',
        'current_password' => 'first-password-1', 'password' => 'my-own-password-2', 'password_confirmation' => 'my-own-password-2',
    ])->assertSessionHasNoErrors();

    expect($ana->fresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('my-own-password-2', $ana->fresh()->password))->toBeTrue();

    $this->actingAs($ana->fresh())->get(route('admin.content.index'))->assertOk();
});

test('a new password needs the current one and must be long enough', function () {
    $user = staffMember('manager', ['password' => 'old-password-123']);

    $this->actingAs($user)->put(route('admin.account.update'), [
        'name' => $user->name, 'email' => $user->email,
        'current_password' => 'wrong', 'password' => 'a-new-long-password', 'password_confirmation' => 'a-new-long-password',
    ])->assertSessionHasErrors('current_password');

    $this->actingAs($user)->put(route('admin.account.update'), [
        'name' => $user->name, 'email' => $user->email,
        'current_password' => 'old-password-123', 'password' => 'short', 'password_confirmation' => 'short',
    ])->assertSessionHasErrors('password');

    expect(Hash::check('old-password-123', $user->fresh()->password))->toBeTrue();
});

test('an owner can change a role, switch a person off, and set a new password', function () {
    $owner = staffMember('owner');
    $person = staffMember('editor');

    $this->actingAs($owner)->put(route('admin.staff.update', $person), [
        'name' => 'Renamed', 'email' => $person->email, 'role' => 'manager', 'active' => '0', 'password' => 'brand-new-password',
    ])->assertSessionHasNoErrors();

    $person->refresh();

    expect($person->name)->toBe('Renamed')
        ->and($person->roleKey())->toBe('manager')
        ->and($person->is_admin)->toBeFalse()
        ->and($person->must_change_password)->toBeTrue();
});

test('nobody can demote, switch off or delete their own account', function () {
    $owner = staffMember('owner');

    $this->actingAs($owner)->put(route('admin.staff.update', $owner), [
        'name' => $owner->name, 'email' => $owner->email, 'role' => 'editor', 'active' => '1',
    ])->assertSessionHasErrors('role');

    $this->actingAs($owner)->put(route('admin.staff.update', $owner), [
        'name' => $owner->name, 'email' => $owner->email, 'role' => 'owner', 'active' => '0',
    ])->assertSessionHasErrors('role');

    $this->actingAs($owner)->delete(route('admin.staff.destroy', $owner))->assertSessionHasErrors('staff');

    expect($owner->fresh()->is_admin)->toBeTrue()->and($owner->fresh()->roleKey())->toBe('owner');
});

test('an owner can delete another person', function () {
    $owner = staffMember('owner');
    $person = staffMember('editor');

    $this->actingAs($owner)->delete(route('admin.staff.destroy', $person))->assertRedirect(route('admin.staff.index'));

    expect(User::find($person->id))->toBeNull();
});

test('a switched-off account cannot sign in', function () {
    User::factory()->create(['email' => 'gone@example.com', 'password' => 'some-password-123', 'is_admin' => false, 'role' => 'editor']);

    $this->post(route('admin.login'), ['email' => 'gone@example.com', 'password' => 'some-password-123'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('signing in is paused after five wrong passwords', function () {
    $user = staffMember('owner', ['email' => 'boss@example.com', 'password' => 'right-password-123']);

    foreach (range(1, 5) as $_) {
        $this->post(route('admin.login'), ['email' => 'boss@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
    }

    // Even the right password is refused while the pause lasts.
    $this->post(route('admin.login'), ['email' => 'boss@example.com', 'password' => 'right-password-123'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('signing in records the time and a line in the history', function () {
    $user = staffMember('owner', ['email' => 'boss@example.com', 'password' => 'right-password-123']);

    $this->post(route('admin.login'), ['email' => 'boss@example.com', 'password' => 'right-password-123'])
        ->assertRedirect(route('admin.dashboard'));

    expect($user->fresh()->last_login_at)->not->toBeNull()
        ->and(ActivityLog::where('action', 'Signed in')->where('user_id', $user->id)->exists())->toBeTrue();
});

test('changes are written to the history without passwords', function () {
    $owner = staffMember('owner', ['name' => 'Olivia Owner']);

    $this->actingAs($owner)->post(route('admin.availability.blocks.store'), [
        'start_date' => now()->addDays(5)->toDateString(),
        'end_date' => now()->addDays(6)->toDateString(),
        'reason' => 'Repairs',
    ])->assertSessionHasNoErrors();

    $this->actingAs($owner)->post(route('admin.staff.store'), [
        'name' => 'New Person', 'email' => 'new@example.com', 'role' => 'editor', 'password' => 'secret-password-99',
    ]);

    $entry = ActivityLog::where('action', 'Added closed dates')->firstOrFail();

    expect($entry->user_name)->toBe('Olivia Owner')
        ->and(ActivityLog::where('action', 'Added a staff account')->exists())->toBeTrue()
        ->and(ActivityLog::all()->map(fn ($e) => json_encode($e->toArray()))->implode(' '))->not->toContain('secret-password-99');
});

test('a form that failed validation is not written to the history', function () {
    $owner = staffMember('owner');

    $this->actingAs($owner)->post(route('admin.availability.blocks.store'), [
        'start_date' => now()->addDays(9)->toDateString(),
        'end_date' => now()->addDays(8)->toDateString(),
    ])->assertSessionHasErrors('end_date');

    expect(ActivityLog::where('action', 'Added closed dates')->exists())->toBeFalse();
});

test('the history page lists changes and can be filtered by person', function () {
    $owner = staffMember('owner', ['name' => 'Olivia Owner']);
    $other = staffMember('manager', ['name' => 'Mike Manager']);

    ActivityLog::record('Changed a booking', 'Booking CV-1', null, $owner);
    ActivityLog::record('Added a review', null, null, $other);

    $this->actingAs($owner)->get(route('admin.activity.index'))
        ->assertOk()->assertSee('Changed a booking')->assertSee('Added a review');

    $this->actingAs($owner)->get(route('admin.activity.index', ['user' => $other->id]))
        ->assertOk()->assertSee('Added a review')->assertDontSee('Changed a booking');
});
