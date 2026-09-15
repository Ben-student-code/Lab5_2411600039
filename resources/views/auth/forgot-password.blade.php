<x-guest-layout>
    <p class="text-muted small mb-3">
        Forgot your password? No problem. Enter your email and we'll send you a reset link.
    </p>

    @if (session('status'))
        <div class="alert alert-success mb-3" role="alert">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mb-3" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       class="form-control"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       placeholder="Enter your email"
                       required
                       autofocus>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            Email Password Reset Link
        </button>
    </form>

    <div class="text-center mt-3">
        <small class="text-muted">
            <a href="{{ route('login') }}">Back to login</a>
        </small>
    </div>
</x-guest-layout>