@extends('layouts.app')

@section('title', 'Programas cargados')

@section('content')

    <div class="section-header">
        <h1>Programas por carrera — {{ $anioActual }}</h1>
    </div>

    @if($stats->isEmpty())
        <div class="data-table-wrapper">
            <p class="empty" style="padding: 24px;">
                Todavía no tenés carreras o cursos asignados. Pedile al administrador que te los asigne.
            </p>
        </div>
    @else
        <div class="cards-grid">
            @foreach($stats as $item)
                @php
                    $porcentaje = $item['requerido'] > 0
                        ? round(($item['cargados'] / $item['requerido']) * 100)
                        : 0;
                @endphp

                <a href="{{ route('consulta.programas.carrera', $item['carrera']) }}" class="stat-card">

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
    @endif

@endsection