<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'ends_with:@abc.edu.ar',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'nombre.required' => 'El nombre completo es obligatorio.',

            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Ingresá un email válido.',
            'email.ends_with' => 'Debés utilizar un email institucional @abc.edu.ar.',
            'email.unique' => 'Ya existe una cuenta registrada con este email.',

            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        DB::transaction(function () use ($validated) {

            $user = User::create([
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            Profesor::create([
                'user_id' => $user->id,
                // rol_id queda null: lo completa el admin después
            ]);

        });

        return redirect()
            ->route('login')
            ->with('success', 'Cuenta creada correctamente. Ahora podés iniciar sesión.');
    }
}