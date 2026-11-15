<div style="font-family:Arial,sans-serif;color:#12241f;line-height:1.6;max-width:560px">
    <h2 style="margin:0 0 8px;color:#06211b">Thank you, {{ \Illuminate\Support\Str::before($booking->name, ' ') }}!</h2>
    <p>We received your request for <strong>{{ $booking->option_title }}</strong> at Casa Verde. Our team will confirm availability and details by email shortly. <strong>Your place is not confirmed until you hear from us.</strong></p>
    <table cellpadding="6" style="border-collapse:collapse;width:100%;font-size:15px;background:#f7f2ea">
        <tr><td><strong>Reference</strong></td><td>{{ $booking->reference }}</td></tr>
        @if($booking->session)<tr><td><strong>Class</strong></td><td>{{ $booking->session }}</td></tr>@endif
        <tr><td><strong>{{ $booking->type === 'retreat' ? 'Arrival' : 'Preferred date' }}</strong></td><td>{{ $booking->start_date->format('D, M j, Y') }}</td></tr>
        @if($booking->end_date)<tr><td><strong>Departure</strong></td><td>{{ $booking->end_date->format('D, M j, Y') }} ({{ $booking->nights }} {{ \Illuminate\Support\Str::plural('night', $booking->nights) }})</td></tr>@endif
        @if($booking->time_pref)<tr><td><strong>Preferred time</strong></td><td>{{ $booking->time_pref }}</td></tr>@endif
        <tr><td><strong>Participants</strong></td><td>{{ $booking->participants }}</td></tr>
    </table>
    <p style="color:#52625c;font-size:14px">Please keep this reference if you need to contact us.</p>
    <p>Casa Verde Cliff Resort &amp; Spa</p>
</div>
