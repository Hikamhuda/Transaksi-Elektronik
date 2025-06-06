<?php
namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Filament\Http\Responses\Auth\LoginResponse;

class Login extends BaseLogin
{
    public function authenticate(): ?LoginResponse
    {
        $this->validate();
        $inputEmail = $this->form->getState()['email'];
        $user = User::all()->first(function ($user) use ($inputEmail) {
            return \App\Helpers\EncryptHelper::decrypt($user->email) === $inputEmail;
        });
        if (! $user || ! Hash::check($this->form->getState()['password'], $user->password)) {
            $this->addError('email', __('filament-panels::pages/auth/login.messages.failed'));
            return null;
        }
        auth()->login($user, $this->form->getState()['remember'] ?? false);
        session()->regenerate();
        return app(LoginResponse::class);
    }
}
