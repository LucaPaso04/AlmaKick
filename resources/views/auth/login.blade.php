@extends('layouts.app')

@section('content')
<section class="row justify-content-center mt-5 mb-5 align-items-center">
    <div class="col-11 col-sm-8 col-md-6 col-lg-5">
        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex bg-primary text-white rounded-circle p-3 mb-3 shadow-sm">
                        <i class="bi bi-dribbble fs-1"></i>
                    </div>
                    <h2 class="fw-bold fs-3 mt-2">Bentornato</h2>
                    <p class="text-secondary">Accedi a CampusCalcetto</p>
                </div>

                <form action="{{ route('login') }}" method="POST">
                    @csrf

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control bg-body-tertiary border-0 shadow-none @error('email') is-invalid @enderror" id="email"
                            name="email" value="{{ old('email') }}" placeholder="nome@esempio.com" required>
                        <label for="email">Indirizzo Email</label>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control bg-body-tertiary border-0 shadow-none @error('password') is-invalid @enderror" id="password"
                            name="password" placeholder="Password" required>
                        <label for="password">Password</label>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                        Accedi <i class="bi bi-box-arrow-in-right ms-1"></i>
                    </button>

                    <div class="text-center mt-4">
                        <span class="text-secondary">Non hai un account? </span>
                        <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">Registrati ora</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
