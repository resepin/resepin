@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="col-md-6 mx-auto">
        <div class="card shadow border-0 rounded-4">
            <div class="card-body p-5">
                <h3 class="fw-bold mb-4 text-center">Login</h3>
                
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100 py-2 fw-bold">Masuk</button>
                </form>
                <p class="mt-3 text-center">Belum punya akun? <a href="{{ route('register') }}">Daftar disini</a></p>
            </div>
        </div>
    </div>
</div>
@endsection