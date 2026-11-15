<?php

use App\Models\HomeContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function reorderEntries(string $section, int $count): array
{
    return collect(range(1, $count))
        ->map(fn (int $position) => HomeContent::create([
            'section' => $section, 'title' => "Entry {$position}", 'sort_order' => $position, 'active' => true,
        ])->id)
        ->all();
}

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('guests cannot change the order', function () {
    $ids = reorderEntries('zz_reorder', 2);

    $this->postJson(route('admin.content.reorder'), ['ids' => array_reverse($ids)])->assertUnauthorized();
});

test('dragging an entry saves the new order as positions 1, 2, 3', function () {
    [$a, $b, $c] = reorderEntries('zz_reorder', 3);

    $this->actingAs($this->admin)
        ->postJson(route('admin.content.reorder'), ['ids' => [$c, $a, $b]])
        ->assertOk();

    expect(HomeContent::find($c)->sort_order)->toBe(1)
        ->and(HomeContent::find($a)->sort_order)->toBe(2)
        ->and(HomeContent::find($b)->sort_order)->toBe(3);
});

test('a partial list is refused so the other entries keep their place', function () {
    [$a, $b, $c] = reorderEntries('zz_reorder', 3);

    $this->actingAs($this->admin)
        ->postJson(route('admin.content.reorder'), ['ids' => [$b, $a]])
        ->assertStatus(422);

    expect(HomeContent::find($a)->sort_order)->toBe(1)
        ->and(HomeContent::find($b)->sort_order)->toBe(2)
        ->and(HomeContent::find($c)->sort_order)->toBe(3);
});

test('entries of different sections cannot be mixed in one order', function () {
    $first = reorderEntries('zz_reorder_one', 1)[0];
    $second = reorderEntries('zz_reorder_two', 1)[0];

    $this->actingAs($this->admin)
        ->postJson(route('admin.content.reorder'), ['ids' => [$second, $first]])
        ->assertStatus(422);
});

test('unknown or repeated ids are rejected', function () {
    [$a] = reorderEntries('zz_reorder', 1);

    $this->actingAs($this->admin)->postJson(route('admin.content.reorder'), ['ids' => [$a, $a]])->assertStatus(422);
    $this->actingAs($this->admin)->postJson(route('admin.content.reorder'), ['ids' => [999999]])->assertStatus(422);
});

test('the content list shows a drag handle for sections with more than one entry', function () {
    reorderEntries('zz_reorder', 2);

    $this->actingAs($this->admin)
        ->get(route('admin.content.index', ['section' => 'zz_reorder']))
        ->assertOk()
        ->assertSee('data-drag-handle', false)
        ->assertSee(route('admin.content.reorder'), false);
});
