@extends('page')

@section('title', 'Login')

@section('content')
    <div class="hero">
        <div class="hero-content flex-col lg:flex-row-reverse">
            <div class="text-center lg:text-left lg:ml-8">
                <h1 class="text-5xl font-bold">Login</h1>
                <p class="py-6">Access your {{ config('app.name') }} account to backup your photos and memories safely.</p>
            </div>
            <div class="card shrink-0 w-full max-w-sm shadow-2xl bg-base-100">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-error mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.submit') }}">
                        @csrf
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Email</span>
                            </label>
                            <input type="email" name="email" placeholder="email@example.com" class="input input-bordered @error('email') input-error @enderror" value="{{ old('email') }}" required />
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Password</span>
                            </label>
                            <input type="password" name="password" placeholder="password" class="input input-bordered @error('password') input-error @enderror" required />
                        </div>
                        <input type="hidden" name="device_name" value="{{ $deviceName ?? 'web_browser' }}">
                        <input type="hidden" name="redirect" value="{{ $redirect }}">
                        <div class="form-control mt-6">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                    @if (Route::has('register'))
                        <div class="text-center mt-4">
                            <span>Don't have an account? </span>
                            <a href="{{ route('register') }}" class="link link-primary">Register now</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
