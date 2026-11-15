<div style="font-family:Arial,sans-serif;color:#12241f;line-height:1.6;max-width:600px">
    <h2 style="margin:0 0 8px;color:#06211b">New Yoga Reset request {{ $booking->reference }}</h2>
    <table cellpadding="6" style="border-collapse:collapse;width:100%;font-size:15px;background:#f7f2ea">
        <tr><td><strong>Experience</strong></td><td>{{ $booking->option_title }}</td></tr>
        @if($booking->session)<tr><td><strong>Class</strong></td><td>{{ $booking->session }}</td></tr>@endif
        <tr><td><strong>{{ $booking->type === 'retreat' ? 'Arrival' : 'Preferred date' }}</strong></td><td>{{ $booking->start_date->format('D, M j, Y') }}</td></tr>
        @if($booking->end_date)<tr><td><strong>Departure</strong></td><td>{{ $booking->end_date->format('D, M j, Y') }} ({{ $booking->nights }} nights)</td></tr>@endif
        @if($booking->time_pref)<tr><td><strong>Preferred time</strong></td><td>{{ $booking->time_pref }}</td></tr>@endif
        <tr><td><strong>Participants</strong></td><td>{{ $booking->participants }}</td></tr>
        @if($booking->level)<tr><td><strong>Experience level</strong></td><td>{{ $booking->level }}</td></tr>@endif
        <tr><td><strong>Name</strong></td><td>{{ $booking->name }}</td></tr>
        <tr><td><strong>Email</strong></td><td>{{ $booking->email }}</td></tr>
        <tr><td><strong>Phone</strong></td><td>{{ $booking->phone }}</td></tr>
        <tr><td><strong>Country</strong></td><td>{{ $booking->country }}</td></tr>
        @if($booking->notes)<tr><td><strong>Notes</strong></td><td>{!! nl2br(e($booking->notes)) !!}</td></tr>@endif
    </table>
    <p style="color:#52625c;font-size:14px">Reply to this email to answer the guest directly.</p>
</div>
