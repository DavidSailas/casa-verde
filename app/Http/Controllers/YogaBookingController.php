<?php

namespace App\Http\Controllers;

use App\Mail\YogaBookingReceived;
use App\Mail\YogaBookingRequested;
use App\Models\HomeContent;
use App\Models\YogaBooking;
use App\Support\BookingSettings;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class YogaBookingController extends Controller
{
    protected const MAX_PARTICIPANTS = 10;

    protected const MAX_NIGHTS = 30;

    /** The Yoga Reset request page: choose an experience, date and guests, then send. */
    public function index(Request $request): View
    {
        $data = $this->pageData();

        $type = $request->query('type');
        $type = in_array($type, array_keys($data['options']), true) ? $type : (array_key_first($data['options']) ?? 'retreat');

        return view('yoga-book', $data + $this->layoutData() + [
            'selectedType' => old('type', $type),
            'selectedSession' => old('session', (string) $request->query('session', '')),
            'maxParticipants' => self::MAX_PARTICIPANTS,
            'maxNights' => self::MAX_NIGHTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->pageData();
        $types = array_keys($data['options']);
        $sessions = array_keys($data['sessions']);

        $input = $request->validate([
            'type' => ['required', Rule::in($types)],
            'session' => ['nullable', 'string', 'max:160', Rule::in($sessions), Rule::requiredIf(fn () => $request->input('type') === 'class' && $sessions !== [])],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => [Rule::requiredIf(fn () => $request->input('type') === 'retreat'), 'nullable', 'date', 'after:start_date'],
            'time_pref' => ['nullable', Rule::in($data['times'])],
            'participants' => ['required', 'integer', 'min:1', 'max:'.self::MAX_PARTICIPANTS],
            'level' => ['nullable', Rule::in($data['levels'])],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+()\-.\s]{6,}$/'],
            'country' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'terms' => ['accepted'],
            'website' => ['nullable', 'max:0'], // honeypot
        ], [
            'start_date.after_or_equal' => 'The date cannot be in the past.',
            'end_date.required' => 'Please choose your departure date.',
            'end_date.after' => 'Departure must be after arrival.',
            'session.required' => 'Please choose a class.',
            'terms.accepted' => 'Please confirm to continue.',
            'phone.regex' => 'Please enter a valid phone number.',
            'website.max' => 'Something went wrong. Please try again.',
        ]);

        $type = $input['type'];
        $start = Carbon::parse($input['start_date'])->startOfDay();
        $end = $type === 'retreat' ? Carbon::parse($input['end_date'])->startOfDay() : null;
        $nights = $end ? (int) $start->diffInDays($end) : null;

        if ($nights !== null && $nights > self::MAX_NIGHTS) {
            return back()->withInput()->withErrors([
                'end_date' => 'Online requests are limited to '.self::MAX_NIGHTS.' nights. Please contact us for longer stays.',
            ]);
        }

        $booking = YogaBooking::create([
            'reference' => $this->newReference(),
            'type' => $type,
            'option_title' => $data['options'][$type]['title'],
            'session' => $type === 'class' ? ($data['sessions'][$input['session'] ?? ''] ?? null) : null,
            'start_date' => $start,
            'end_date' => $end,
            'nights' => $nights,
            'time_pref' => $type === 'private' ? ($input['time_pref'] ?? null) : null,
            'participants' => $input['participants'],
            'level' => $input['level'] ?? null,
            'name' => trim($input['name']),
            'email' => $input['email'],
            'phone' => trim($input['phone']),
            'country' => trim($input['country']),
            'notes' => filled($input['notes'] ?? null) ? trim($input['notes']) : null,
            'status' => 'pending',
            'terms_accepted_at' => now(),
        ]);

        // The request is saved first; an email problem must never lose a request.
        try {
            Mail::to(BookingSettings::all()['notify'])->send(new YogaBookingRequested($booking));
            Mail::to($booking->email)->send(new YogaBookingReceived($booking));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('yoga.book.confirmed')->with('yoga_booking_id', $booking->id);
    }

    /** "Request received" page. Only shown right after a request. */
    public function confirmed(Request $request): View|RedirectResponse
    {
        $id = $request->session()->get('yoga_booking_id') ?? $request->session()->get('last_yoga_booking_id');
        $booking = $id ? YogaBooking::find($id) : null;

        if (! $booking) {
            return redirect()->route('yoga.book');
        }

        $request->session()->put('last_yoga_booking_id', $booking->id); // survives a refresh

        return view('yoga-book-confirmed', ['booking' => $booking] + $this->layoutData());
    }

    /**
     * What the shared header needs ($contents for the menu, $site for the logo and name).
     *
     * @return array<string, mixed>
     */
    protected function layoutData(): array
    {
        $contents = HomeContent::where('active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('section');

        return [
            'contents' => $contents,
            'site' => $contents->get('site', collect())->first(),
        ];
    }

    /**
     * What the form offers, taken from the Yoga Reset page itself so the classes, price and photos
     * always match what the guest just read.
     *
     * @return array<string, mixed>
     */
    protected function pageData(): array
    {
        $page = app(YogaController::class)->pageData();
        $copy = config('yoga.booking');

        $sessions = [];
        foreach ($page['classes']['groups'] ?? [] as $group) {
            if (preg_match('/appointment/i', (string) $group['label'])) {
                continue;
            }
            foreach ($group['sessions'] as $session) {
                $sessions[(string) $session['name']] = trim($group['label'].' · '.$session['name']);
            }
        }

        $options = [];
        foreach (['retreat', 'class', 'private'] as $type) {
            if ($type === 'class' && $sessions === []) {
                continue;
            }
            $options[$type] = $copy['options'][$type];
        }

        $price = $page['package']['price'] ?? null;
        $options['retreat']['price'] = $price['big'] ?? null;
        $options['class']['price'] = null;
        $options['private']['price'] = null;

        return [
            'page' => $page,
            'copy' => $copy,
            'options' => $options,
            'sessions' => $sessions,
            'times' => $copy['times'],
            'levels' => $copy['levels'],
            'priceNote' => $price['lead'] ?? $price['text'] ?? null,
        ];
    }

    protected function newReference(): string
    {
        do {
            $reference = 'YR-'.strtoupper(Str::random(6));
        } while (YogaBooking::where('reference', $reference)->exists());

        return $reference;
    }
}
