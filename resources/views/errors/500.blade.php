@include('errors.layout', [
    'status' => 500,
    'title' => __('Something went wrong'),
    'message' => __('The store encountered an unexpected problem. Our team can review it from the logs.'),
    'actionUrl' => route('home'),
    'actionLabel' => __('Back to home'),
])
