<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Catalog\ShowProductCatalog;
use App\Livewire\Catalog\ProductDetail;
use App\Livewire\Catalog\ShowCategoryProducts;
use App\Livewire\CartPage;
use App\Livewire\CheckoutPage; // Importar CheckoutPage
use App\Livewire\UserRegistrationForm;
use App\Livewire\UserProfileForm;
use App\Livewire\EnhancedUserRegistrationPage;
use App\Http\Controllers\Payment\PaymentCallbackController; // Importar el controlador de callback

// Redirect old /login to Filament's admin login
Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login'); // Mantenemos el nombre 'login' por si Fortify u otros paquetes lo esperan

Route::get('/', function () {
    return view('welcome');
});

// Catalog and Product Routes
Route::get('/catalog', ShowProductCatalog::class)->name('catalog.index');
Route::get('/products/{product:slug}', ProductDetail::class)->name('catalog.product.detail');
Route::get('/category/{category:slug}', ShowCategoryProducts::class)->name('catalog.category');

// Cart Route
Route::get('/cart', CartPage::class)->name('cart.page');

// Checkout Route
Route::get('/checkout', CheckoutPage::class)->name('checkout.page'); // Renombrado de checkout.index a checkout.page por consistencia

// User Registration Route
Route::get('/registro', EnhancedUserRegistrationPage::class)->name('register.form'); // Cambiado a EnhancedUserRegistrationPage

// Nueva ruta para el Onboarding Unificado - ELIMINADA
// Route::get('/onboarding', EnhancedUserRegistrationPage::class)->name('onboarding.unified');

Route::middleware(['auth', 'verified'])->group(function () { // Added 'verified' middleware for protected routes
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // User Profile Route
    Route::get('/my-profile', UserProfileForm::class)->name('profile.show');
});

// Payment Gateway Callbacks
Route::post('/payment/callback/{gateway}', [PaymentCallbackController::class, 'handleCallback'])->name('payment.callback');
Route::get('/payment/callback/{gateway}', [PaymentCallbackController::class, 'handleCallback']); // Algunas pasarelas usan GET para redirección final
