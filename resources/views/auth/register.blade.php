@extends('layouts.app')

@section('content')
<section class="row justify-content-center mt-4 mb-5">
    <div class="col-11 col-sm-10 col-md-8 col-lg-6">
        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold fs-3">Crea un Account</h2>
                    <p class="text-secondary">Unisciti a CampusCalcetto e inizia a giocare</p>
                </div>

                <form action="{{ route('register') }}" method="POST">
                    @csrf

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control bg-body-tertiary border-0 @error('name') is-invalid @enderror" id="name" name="name"
                            value="{{ old('name') }}" placeholder="Mario Rossi" required>
                        <label for="name">Nome Completo</label>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control bg-body-tertiary border-0 @error('email') is-invalid @enderror" id="email" name="email"
                            value="{{ old('email') }}" placeholder="mario@email.com" required>
                        <label for="email">Indirizzo Email</label>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <input type="tel" class="form-control bg-body-tertiary border-0 @error('phone') is-invalid @enderror" id="phone" name="phone"
                            value="{{ old('phone') }}" placeholder="3331234567" required>
                        <label for="phone">Telefono (Emergenza)</label>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select bg-body-tertiary border-0 @error('preferred_role') is-invalid @enderror" id="preferred_role" name="preferred_role">
                            <option value="Jolly" {{ old('preferred_role') == 'Jolly' || !old('preferred_role') ? 'selected' : '' }}>Jolly</option>
                            <option value="Portiere" {{ old('preferred_role') == 'Portiere' ? 'selected' : '' }}>Portiere</option>
                            <option value="Difensore" {{ old('preferred_role') == 'Difensore' ? 'selected' : '' }}>Difensore</option>
                            <option value="Centrocampista" {{ old('preferred_role') == 'Centrocampista' ? 'selected' : '' }}>Centrocampista</option>
                            <option value="Attaccante" {{ old('preferred_role') == 'Attaccante' ? 'selected' : '' }}>Attaccante</option>
                        </select>
                        <label for="preferred_role">Ruolo in Campo Preferito</label>
                        @error('preferred_role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-floating mb-4">
                        <input type="password" class="form-control bg-body-tertiary border-0 @error('password') is-invalid @enderror" id="password"
                            name="password" placeholder="Password" required minlength="6">
                        <label for="password">Password</label>
                        <div class="form-text mt-2 ms-1">Minimo 6 caratteri</div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                        Registrati
                    </button>

                    <div class="text-center mt-4">
                        <span class="text-secondary">Hai già un account? </span>
                        <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">Accedi qui</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
