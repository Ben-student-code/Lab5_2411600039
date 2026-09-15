<x-guest-layout>
  {{-- Session status (e.g. "password reset email sent") --}}
  @if (session('status'))
  <div class="alert alert-success mb-3" role="alert">
    {{ session('status') }}
  </div>
  @endif

  {{-- Validation errors --}}
  @if ($errors->any())
  <div class="alert alert-danger mb-3" role="alert">
    @foreach ($errors->all() as $error)
    <div>{{ $error }}</div>
    @endforeach
  </div>
  @endif

  <form method="POST" action="{{ route('login') }}">
    @csrf

    {{-- Email field (Breeze expects email; the original used username) --}}
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-person"></i></span>
        <input type="email"
          class="form-control @error('email') is-invalid @enderror"
          id="email"
          name="email"
          value="{{ old('email') }}"
          placeholder="Enter email"
          required
          autofocus
          autocomplete="username">
      </div>
      @error('email')
      <div class="text-danger small mt-1">{{ $message }}</div>
      @enderror
    </div>

    {{-- Password field --}}
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-lock"></i></span>
        <input type="password"
          class="form-control @error('password') is-invalid @enderror"
          id="password"
          name="password"
          placeholder="Enter password"
          required
          autocomplete="current-password">
      </div>
      @error('password')
      <div class="text-danger small mt-1">{{ $message }}</div>
      @enderror
    </div>

    {{-- Remember me + Forgot password --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div class="form-check">
        <input type="checkbox"
          class="form-check-input"
          id="remember_me"
          name="remember">
        <label class="form-check-label" for="remember_me">Remember Me</label>
      </div>
      @if (Route::has('password.request'))
      <a href="{{ route('password.request') }}" class="small">Forgot password?</a>
      @endif
    </div>

    {{-- Login button --}}
    <button type="submit" class="btn btn-primary w-100 py-2">
      Login
    </button>
  </form>

  {{-- Register link --}}
  <div class="text-center mt-3">
    <small class="text-muted">
      Don't have an account?
      <a href="{{ route('register') }}">Register here</a>
    </small>
  </div>
</x-guest-layout>