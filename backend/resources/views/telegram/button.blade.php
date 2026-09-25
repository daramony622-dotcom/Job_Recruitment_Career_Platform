{{-- Put this on your login / register page:  @include('auth.telegram-button') --}}
<form method="POST" action="{{ route('telegram.start') }}">
    @csrf
    <button type="submit"
            style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;border:0;border-radius:8px;background:#229ED9;color:#fff;font-size:15px;cursor:pointer">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M9.78 15.34 9.6 19.1c.4 0 .58-.17.79-.38l1.9-1.82 3.94 2.89c.72.4 1.24.19 1.44-.67l2.6-12.2c.24-1.08-.39-1.5-1.1-1.24L3.9 10.6c-1.05.41-1.03 1-.18 1.26l3.9 1.22 9.06-5.71c.43-.26.82-.12.5.14"/>
        </svg>
        Login / Register with Telegram
    </button>
</form>