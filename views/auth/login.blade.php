@extends('layouts.app')

@section('title', 'Đăng nhập - FilmStream')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center">
        <div class="w-full max-w-md bg-surface-soft/90 backdrop-blur rounded-2xl border border-zinc-800 p-6 shadow-2xl">
            <h1 class="text-xl font-semibold mb-1">Đăng nhập</h1>
            <p class="text-xs text-zinc-400 mb-4">
                Sử dụng form bên dưới hoặc đăng nhập nhanh qua popup trên header.
            </p>

            @if($error = getFlash('error'))
                <div class="mb-3 text-xs px-3 py-2 rounded-md bg-red-600/20 text-red-300 border border-red-600/50">
                    {{ $error }}
                </div>
            @endif

            @if($success = getFlash('success'))
                <div class="mb-3 text-xs px-3 py-2 rounded-md bg-emerald-600/20 text-emerald-300 border border-emerald-600/50">
                    {{ $success }}
                </div>
            @endif

            <form action="{{ route('/dang-nhap') }}" method="post" class="space-y-3">
                <div class="space-y-1">
                    <label class="text-xs text-zinc-300">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="w-full bg-zinc-900 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                        required
                    />
                </div>
                <div class="space-y-1">
                    <label class="text-xs text-zinc-300">Mật khẩu</label>
                    <input
                        type="password"
                        name="password"
                        class="w-full bg-zinc-900 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                        required
                    />
                </div>
                <button
                    type="submit"
                    class="w-full bg-primary-600 hover:bg-primary-500 text-sm font-semibold py-2 rounded-md transition"
                >
                    Đăng nhập
                </button>
            </form>

            <div class="mt-4 text-xs text-zinc-400">
                Chưa có tài khoản?
                <button
                    type="button"
                    data-open-auth-modal="register"
                    class="text-primary-400 hover:text-primary-300"
                >
                    Đăng ký ngay
                </button>
            </div>
        </div>
    </div>
@endsection

