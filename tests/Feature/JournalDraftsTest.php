<?php

use App\Models\HomeContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function journalArticle(string $title, bool $published, array $extra = []): HomeContent
{
    return HomeContent::create($extra + [
        'section' => 'journal_articles', 'title' => $title, 'subtitle' => 'November 15, 2026',
        'description' => "## A heading\n\nSome **bold** words.", 'sort_order' => 1, 'active' => $published,
    ]);
}

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true]);
});

test('a draft is not on the journal page and has no public page', function () {
    journalArticle('Secret Draft Story', published: false);

    $this->get(route('journal.index'))->assertOk()->assertDontSee('Secret Draft Story');
    $this->get(route('journal.show', 'secret-draft-story'))->assertNotFound();
});

test('a published article is public', function () {
    journalArticle('Open Story', published: true);

    $this->get(route('journal.index'))->assertOk()->assertSee('Open Story');
    $this->get(route('journal.show', 'open-story'))->assertOk()->assertSee('Open Story');
});

test('an admin can preview a draft and is told it is a draft', function () {
    $draft = journalArticle('Secret Draft Story', published: false);

    $this->actingAs($this->admin)
        ->get(route('admin.content.preview', $draft))
        ->assertOk()
        ->assertSee('Secret Draft Story')
        ->assertSee('this article is a draft')
        ->assertSee('A heading');
});

test('previewing a published article says it is published', function () {
    $article = journalArticle('Open Story', published: true);

    $this->actingAs($this->admin)
        ->get(route('admin.content.preview', $article))
        ->assertOk()
        ->assertSee('this article is published');
});

test('guests cannot open the preview', function () {
    $draft = journalArticle('Secret Draft Story', published: false);

    $this->get(route('admin.content.preview', $draft))->assertRedirect();
});

test('only journal articles can be previewed', function () {
    $other = HomeContent::create(['section' => 'villas', 'title' => 'A villa', 'sort_order' => 1, 'active' => true]);

    $this->actingAs($this->admin)->get(route('admin.content.preview', $other))->assertNotFound();
});

test('an article without a title cannot be previewed yet', function () {
    $untitled = journalArticle('', published: false);

    $this->actingAs($this->admin)
        ->get(route('admin.content.preview', $untitled))
        ->assertRedirect(route('admin.content.edit', $untitled))
        ->assertSessionHasErrors('title');
});

test('the preview does not publish anything', function () {
    $draft = journalArticle('Secret Draft Story', published: false);

    $this->actingAs($this->admin)->get(route('admin.content.preview', $draft))->assertOk();

    expect($draft->fresh()->active)->toBeFalse();
});

test('a new journal article starts as a draft, other entries start visible', function () {
    $switch = function (string $section): string {
        $html = $this->actingAs($this->admin)->get(route('admin.content.create', ['section' => $section]))->assertOk()->getContent();
        preg_match('/<input type="checkbox" id="active"[^>]*>/s', $html, $match);

        return $match[0] ?? '';
    };

    expect($switch('journal_articles'))->not->toContain('checked')
        ->and($switch('villas'))->toContain('checked');

    $this->actingAs($this->admin)
        ->get(route('admin.content.create', ['section' => 'journal_articles']))
        ->assertSee('data-article-editor', false);

    $this->actingAs($this->admin)
        ->get(route('admin.content.create', ['section' => 'villas']))
        ->assertDontSee('data-article-editor', false);
});

test('the article list labels entries as published or draft', function () {
    journalArticle('Open Story', published: true);
    journalArticle('Secret Draft Story', published: false, extra: ['sort_order' => 2]);

    $this->actingAs($this->admin)
        ->get(route('admin.content.index', ['section' => 'journal_articles']))
        ->assertOk()
        ->assertSee('Published')
        ->assertSee('Draft');
});
