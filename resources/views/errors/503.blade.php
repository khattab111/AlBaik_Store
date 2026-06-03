@include('errors.layout', [
    'status' => 503,
    'title' => __('Store temporarily unavailable'),
    'message' => __('We are performing maintenance or the service is temporarily busy. Please try again shortly.'),
    'actionUrl' => route('home'),
    'actionLabel' => __('Back to home'),
])
