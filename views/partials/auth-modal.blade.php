<div
    id="auth-modal"
    class="hidden fixed inset-0 z-50 bg-black/70 flex items-center justify-center"
    data-mode="login"
    data-close-auth-modal="true"
>
    <div
        class="w-full max-w-md bg-surface-soft rounded-xl border border-zinc-800 shadow-xl p-6 relative"
        data-close-auth-modal="false"
    >
        <button
            type="button"
            class="absolute top-3 right-3 text-zinc-400 hover:text-white"
            data-close-auth-modal="true"
        >
            ✕
        </button>

        <h2 class="text-xl font-semibold mb-1" id="auth-modal-title">Đăng nhập</h2>
        <p class="text-xs text-zinc-400 mb-4" id="auth-modal-subtitle">
            Chào mừng bạn quay lại với FilmStream.
        </p>

        <form
            id="login-form"
            action="{{ route('/dang-nhap') }}"
            method="post"
            class="space-y-3 auth-form"
            data-mode="login"
        >
            <div class="space-y-1">
                <label class="text-xs text-zinc-300">Email</label>
                <input
                    type="email"
                    name="email"
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                />
            </div>
            <div class="space-y-1">
                <label class="text-xs text-zinc-300">Mật khẩu</label>
                <input
                    type="password"
                    name="password"
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                />
            </div>
            <button
                type="submit"
                class="w-full bg-primary-600 hover:bg-primary-500 text-sm font-semibold py-2 rounded-md transition"
            >
                Đăng nhập
            </button>
        </form>

        <form
            id="register-form"
            action="{{ route('/dang-ky') }}"
            method="post"
            class="space-y-3 auth-form hidden"
            data-mode="register"
        >
            <div class="space-y-1">
                <label class="text-xs text-zinc-300">Họ tên</label>
                <input
                    type="text"
                    name="name"
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                />
            </div>
            <div class="space-y-1">
                <label class="text-xs text-zinc-300">Email</label>
                <input
                    type="email"
                    name="email"
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                />
            </div>
            <div class="space-y-1">
                <label class="text-xs text-zinc-300">Mật khẩu</label>
                <input
                    type="password"
                    name="password"
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                />
            </div>
            <div class="space-y-1">
                <label class="text-xs text-zinc-300">Nhập lại mật khẩu</label>
                <input
                    type="password"
                    name="password_confirmation"
                    class="w-full bg-zinc-900 border border-zinc-700 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-primary-500"
                />
            </div>
            <button
                type="submit"
                class="w-full bg-primary-600 hover:bg-primary-500 text-sm font-semibold py-2 rounded-md transition"
            >
                Đăng ký
            </button>
        </form>

        <div class="mt-4 text-xs text-zinc-400">
            <button
                type="button"
                id="auth-toggle-btn"
                class="text-primary-400 hover:text-primary-300"
            >
                Chưa có tài khoản? Đăng ký ngay
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('auth-modal');
        if (!modal) return;

        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const toggleBtn = document.getElementById('auth-toggle-btn');
        const title = document.getElementById('auth-modal-title');
        const subtitle = document.getElementById('auth-modal-subtitle');

        function setMode(mode) {
            modal.dataset.mode = mode;
            if (mode === 'login') {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                title.textContent = 'Đăng nhập';
                subtitle.textContent = 'Chào mừng bạn quay lại với FilmStream.';
                toggleBtn.textContent = 'Chưa có tài khoản? Đăng ký ngay';
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                title.textContent = 'Đăng ký tài khoản';
                subtitle.textContent = 'Tạo tài khoản để lưu phim yêu thích và lịch sử xem.';
                toggleBtn.textContent = 'Đã có tài khoản? Đăng nhập';
            }
        }

        toggleBtn.addEventListener('click', () => {
            setMode(modal.dataset.mode === 'login' ? 'register' : 'login');
        });

        function bindAjax(form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(form);
                const res = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                });
                const data = await res.json();
                if (data.ok) {
                    showToast(data.message || 'Thành công');
                    if (form.dataset.mode === 'login') {
                        window.location.reload();
                    } else {
                        setMode('login');
                    }
                } else {
                    showToast(data.error || 'Có lỗi xảy ra', 'error');
                }
            });
        }

        bindAjax(loginForm);
        bindAjax(registerForm);
    })();
</script>

