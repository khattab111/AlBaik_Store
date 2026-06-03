@include('errors.layout', [
    'status' => 403,
    'title' => __('Access denied'),
    'message' => __('You do not have permission to open this page or perform this action.'),
    'actionUrl' => request()->is('admin*') ? url('/admin') : route('home'),
    'actionLabel' => request()->is('admin*') ? __('Back to dashboard') : __('Back to home'),
])
