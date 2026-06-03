@include('errors.layout', [
    'status' => 419,
    'title' => __('Session expired'),
    'message' => __('Your session has expired for security reasons. Please refresh the page and try again.'),
    'actionUrl' => request()->is('admin*') ? url('/admin/login') : route('customer.login'),
    'actionLabel' => __('Login again'),
])
