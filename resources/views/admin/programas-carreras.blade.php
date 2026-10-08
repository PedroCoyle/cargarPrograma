@extends('layouts.app')

@section('title', 'Programas')

@section('content')

    <div class="section-header">
        <h1>Programas por carrera</h1>
    </div>

    <div class="section-header">
    <h1>Programas por carrera — {{ $anioActual }}</h1>
</div>

    <div class="cards-grid">
        @foreach($stats as $item)
            @php
                $porcentaje = $item['requerido'] > 0
                    ? round(($item['cargados'] / $item['requerido']) * 100)
                    : 0;
            @endphp

            <a href="{{ route('admin.programas.carrera', $item['carrera']) }}" class="stat-card">

                <h2>{{ $item['carrera']->nombre }}</h2>

                <div class="stat-card-numbers">
                    {{ $item['cargados'] }}/{{ $item['requerido'] }}
                </div>

                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: {{ $porcentaje }}%;"></div>
                </div>

                <span class="stat-card-percent">{{ $porcentaje }}% completo</span>

            </a>
        @endforeach
    </div>

@endsection