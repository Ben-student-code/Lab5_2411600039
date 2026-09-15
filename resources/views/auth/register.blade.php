<x-guest-layout>
    @if ($errors->any())
        <div class="alert alert-danger mb-3" role="alert">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                <input type="text"
                       class="form-control"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       placeholder="Enter your name"
                       required
                       autofocus>
            </div>
        </div>

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
                       required>
            </div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password"
                       class="form-control"
                       id="password"
                       name="password"
                       placeholder="Create a password"
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
                       placeholder="Confirm password"
                       required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2">
            Register
        </button>
    </form>

    <div class="text-center mt-3">
        <small class="text-muted">
            Already registered?
            <a href="{{ route('login') }}">Login here</a>
        </small>
    </div>
</x-guest-layout>