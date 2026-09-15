<x-guest-layout>
    @if ($errors->any())
        <div class="alert alert-danger mb-3" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email"
                       class="form-control"
                       id="email"
                       name="email"
                       value="{{ old('email', $request->email) }}"
                       placeholder="Enter your email"
                       required
                       autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password"
                       class="form-control"
                       id="password"
                       name="password"
                       placeholder="New password"
                       required>
            </div>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password"
                       class="form-control"
                       id="password_confirmation"
                       name="password_confirmation"
                       placeholder="Confirm new password"
                       required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            Reset Password
        </button>
    </form>
</x-guest-layout>