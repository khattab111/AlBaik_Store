@include('errors.layout', [
    'status' => 404,
    'title' => __('Page not found'),
    'message' => __('The page you are looking for may have been moved, removed, or the link is incorrect.'),
    'actionUrl' => route('home'),
    'actionLabel' => __('Back to home'),
])
