<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Servicio - #{{ $solicitud->id }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.4; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; }
        
        .section { margin-bottom: 20px; }
        .section-title { background: #f0f0f0; padding: 5px 10px; font-weight: bold; text-transform: uppercase; border-left: 5px solid #333; margin-bottom: 10px; font-size: 14px; }
        
        .grid { display: flex; flex-wrap: wrap; }
        .col { flex: 1; min-width: 50%; margin-bottom: 10px; }
        .label { font-weight: bold; font-size: 12px; color: #555; display: block; }
        .value { font-size: 14px; }
        
        .description-box { border: 1px solid #ccc; padding: 10px; min-height: 80px; font-size: 13px; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 12px; }
        .table th { background: #f9f9f9; }
        
        .signatures { margin-top: 50px; display: flex; justify-content: space-between; }
        .sig-box { border-top: 1px solid #333; width: 40%; text-align: center; padding-top: 10px; font-size: 12px; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #333; color: white; border: none; cursor: pointer; border-radius: 5px;">🖨️ Imprimir Ficha</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #eee; border: 1px solid #ccc; cursor: pointer; border-radius: 5px;">Cerrar</button>
    </div>

    <div class="header">
        <img src="{{ asset('60issste.png') }}" style="height: 60px; margin-bottom: 10px;">
        <h1>Ficha de Servicio de Mantenimiento</h1>
        <p>Sistema de Gestión Centralizada | Folio Interno: #{{ str_pad($solicitud->id, 5, '0', STR_PAD_LEFT) }}</p>
    </div>

    <div class="section">
        <div class="section-title">Información General</div>
        <div class="grid">
            <div class="col">
                <span class="label">Unidad Solicitante:</span>
                <span class="value">{{ $solicitud->unidad->nombre }}</span>
            </div>
            <div class="col">
                <span class="label">Estatus Actual:</span>
                <span class="value" style="text-transform: uppercase; font-weight: bold;">{{ $solicitud->estatus }}</span>
            </div>
            <div class="col">
                <span class="label">Folio de Oficio:</span>
                <span class="value">{{ $solicitud->folio_oficio ?: 'N/A' }}</span>
            </div>
            <div class="col">
                <span class="label">Orden de Servicio:</span>
                <span class="value">{{ $solicitud->orden_servicio ?: 'Pendiente' }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Detalles del Servicio</div>
        <div class="grid">
            <div class="col">
                <span class="label">Área de Servicio:</span>
                <span class="value">{{ $solicitud->servicio->nombre }}</span>
            </div>
            <div class="col">
                <span class="label">Tipo de Mantenimiento:</span>
                <span class="value">{{ $solicitud->tipoMantenimiento->nombre }}</span>
            </div>
            <div class="col">
                <span class="label">Prioridad:</span>
                <span class="value">{{ $solicitud->prioridad->nombre }} (SLA: {{ $solicitud->prioridad->tiempo_respuesta_horas }}h)</span>
            </div>
            <div class="col">
                <span class="label">Fecha de Solicitud:</span>
                <span class="value">{{ $solicitud->fecha_solicitud->format('d/m/Y H:i') }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Descripción del Problema</div>
        <div class="description-box">
            <strong>{{ $solicitud->titulo }}</strong><br>
            {{ $solicitud->descripcion }}
            @if($solicitud->descripcion_servicio_otro)
                <p><em>Específicación: {{ $solicitud->descripcion_servicio_otro }}</em></p>
            @endif
        </div>
    </div>

    <div class="section">
        <div class="section-title">Trazabilidad y Avances</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Registrado por</th>
                    <th>%</th>
                    <th>Comentarios / Avances</th>
                </tr>
            </thead>
            <tbody>
                @forelse($solicitud->avances as $avance)
                    <tr>
                        <td width="15%">{{ $avance->fecha->format('d/m/Y H:i') }}</td>
                        <td width="20%">{{ $avance->user->name }}</td>
                        <td width="5%">{{ $avance->porcentaje }}%</td>
                        <td>{{ $avance->comentario }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #999;">No hay avances registrados aún.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Fechas de Control</div>
        <div class="grid">
            <div class="col">
                <span class="label">Fecha Atención:</span>
                <span class="value">{{ $solicitud->fecha_atencion ? $solicitud->fecha_atencion->format('d/m/Y H:i') : '---' }}</span>
            </div>
            <div class="col">
                <span class="label">Fecha Cierre:</span>
                <span class="value">{{ $solicitud->fecha_cierre ? $solicitud->fecha_cierre->format('d/m/Y H:i') : '---' }}</span>
            </div>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-box">
            <p>Firma y Sello</p>
            <strong>Unidad Solicitante</strong>
        </div>
        <div class="sig-box">
            <p>Firma y Sello</p>
            <strong>Departamento de Mantenimiento</strong>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #aaa;">
        Documento generado el {{ now()->format('d/m/Y H:i') }} por {{ Auth::user()->name }}.
    </div>

    <script>
        // Opcional: auto-abrir diálogo de impresión al cargar
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
