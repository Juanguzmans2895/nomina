<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ejecutivo de Nómina - {{ $periodo }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        .container { padding: 20px; }
        
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #2563eb; padding-bottom: 15px; }
        .company-name { font-size: 18pt; font-weight: bold; color: #1e40af; margin-bottom: 5px; }
        .company-info { font-size: 9pt; color: #666; margin-bottom: 3px; }
        .report-title { font-size: 14pt; font-weight: bold; margin-top: 15px; color: #1e40af; }
        .report-subtitle { font-size: 10pt; color: #666; margin-top: 5px; }
        
        .summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 30px 0; }
        .summary-card { border: 2px solid #e5e7eb; border-radius: 8px; padding: 15px; text-align: center; }
        .card-label { font-size: 8pt; color: #6b7280; margin-bottom: 8px; text-transform: uppercase; }
        .card-value { font-size: 18pt; font-weight: bold; }
        .card-change { font-size: 8pt; margin-top: 5px; }
        .card-change.positive { color: #10b981; }
        .card-change.negative { color: #ef4444; }
        
        .section-title { font-size: 12pt; font-weight: bold; color: #1e40af; margin: 25px 0 15px 0; 
                         border-bottom: 2px solid #2563eb; padding-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 9pt; }
        th { background-color: #1e40af; color: white; padding: 8px 5px; text-align: left; font-weight: bold; font-size: 8pt; }
        td { padding: 6px 5px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background-color: #f9fafb; }
        tr:hover { background-color: #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .total-row { background-color: #dbeafe !important; font-weight: bold; }
        .total-row td { border-top: 2px solid #2563eb; padding: 10px 5px; }
        
        .chart-placeholder { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; 
                            padding: 30px; text-align: center; color: #6b7280; margin: 20px 0; }
        
        .indicators-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin: 20px 0; }
        .indicator-box { border: 1px solid #e5e7eb; border-radius: 5px; padding: 10px; }
        .indicator-label { font-size: 8pt; color: #6b7280; margin-bottom: 5px; }
        .indicator-value { font-size: 14pt; font-weight: bold; color: #1e40af; }
        
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 8pt; color: #6b7280; text-align: center; }
        
        .highlight-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .highlight-title { font-weight: bold; color: #92400e; margin-bottom: 5px; }
        
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .page-break { page-break-after: always; }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <div class="company-name">{{ config('nomina.empresa.razon_social', 'EMPRESA') }}</div>
            <div class="company-info">NIT: {{ config('nomina.empresa.nit', '000000000-0') }}</div>
            <div class="company-info">{{ config('nomina.empresa.direccion', 'Dirección') }}</div>
            <div class="company-info">{{ config('nomina.empresa.ciudad', 'Ciudad') }}</div>
            
            <div class="report-title">REPORTE EJECUTIVO DE NÓMINA</div>
            <div class="report-subtitle">Período: {{ $periodo }}</div>
            <div class="report-subtitle">Generado: {{ now()->format('d/m/Y H:i') }}</div>
        </div>

        {{-- Métricas Principales --}}
        <div class="summary-grid">
            <div class="summary-card">
                <div class="card-label">Total Nómina</div>
                <div class="card-value" style="color: #2563eb;">${{ number_format($metricas['total_nomina'] / 1000000, 1) }}M</div>
                <div class="card-change {{ $metricas['variacion_mes_anterior'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $metricas['variacion_mes_anterior'] >= 0 ? '↑' : '↓' }} 
                    {{ abs($metricas['variacion_mes_anterior']) }}% vs mes anterior
                </div>
            </div>

            <div class="summary-card">
                <div class="card-label">Empleados</div>
                <div class="card-value" style="color: #7c3aed;">{{ $metricas['total_empleados'] }}</div>
                <div class="card-change {{ $metricas['variacion_empleados'] >= 0 ? 'positive' : 'negative' }}">
                    {{ $metricas['variacion_empleados'] >= 0 ? '↑' : '↓' }} 
                    {{ abs($metricas['variacion_empleados']) }} empleados
                </div>
            </div>

            <div class="summary-card">
                <div class="card-label">Seguridad Social</div>
                <div class="card-value" style="color: #059669;">${{ number_format($metricas['total_seguridad_social'] / 1000000, 1) }}M</div>
                <div class="card-change">
                    {{ number_format(($metricas['total_seguridad_social'] / $metricas['total_nomina']) * 100, 1) }}% del total
                </div>
            </div>

            <div class="summary-card">
                <div class="card-label">Costo Total Empleador</div>
                <div class="card-value" style="color: #dc2626;">${{ number_format($metricas['costo_total_empleador'] / 1000000, 1) }}M</div>
                <div class="card-change">
                    {{ number_format(($metricas['costo_total_empleador'] / $metricas['total_nomina']) * 100, 1) }}% sobre nómina
                </div>
            </div>
        </div>

        {{-- Indicadores Clave --}}
        <div class="section-title">INDICADORES CLAVE DE DESEMPEÑO</div>
        <div class="indicators-grid">
            <div class="indicator-box">
                <div class="indicator-label">Salario Promedio</div>
                <div class="indicator-value">${{ number_format($indicadores['salario_promedio'], 0) }}</div>
            </div>
            <div class="indicator-box">
                <div class="indicator-label">Costo Promedio por Empleado</div>
                <div class="indicator-value">${{ number_format($indicadores['costo_por_empleado'], 0) }}</div>
            </div>
            <div class="indicator-box">
                <div class="indicator-label">% Deducciones sobre Devengado</div>
                <div class="indicator-value">{{ number_format($indicadores['porcentaje_deducciones'], 2) }}%</div>
            </div>
            <div class="indicator-box">
                <div class="indicator-label">% Seguridad Social</div>
                <div class="indicator-value">{{ number_format($indicadores['porcentaje_seguridad_social'], 2) }}%</div>
            </div>
        </div>

        {{-- Resumen por Dependencia --}}
        <div class="section-title">DISTRIBUCIÓN POR DEPENDENCIA</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 30%;">DEPENDENCIA</th>
                    <th style="width: 10%;" class="text-center">EMPLEADOS</th>
                    <th style="width: 15%;" class="text-right">DEVENGADO</th>
                    <th style="width: 15%;" class="text-right">DEDUCCIONES</th>
                    <th style="width: 15%;" class="text-right">NETO</th>
                    <th style="width: 15%;" class="text-right">% DEL TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porDependencia as $dep)
                <tr>
                    <td><strong>{{ $dep['nombre'] }}</strong></td>
                    <td class="text-center">{{ $dep['empleados'] }}</td>
                    <td class="text-right">${{ number_format($dep['devengado'], 0) }}</td>
                    <td class="text-right">${{ number_format($dep['deducciones'], 0) }}</td>
                    <td class="text-right"><strong>${{ number_format($dep['neto'], 0) }}</strong></td>
                    <td class="text-right">{{ number_format($dep['porcentaje'], 2) }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTALES:</strong></td>
                    <td class="text-center"><strong>{{ $metricas['total_empleados'] }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['devengado'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['deducciones'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['neto'], 0) }}</strong></td>
                    <td class="text-right"><strong>100.00%</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="page-break"></div>

        {{-- Composición de la Nómina --}}
        <div class="section-title">COMPOSICIÓN DETALLADA DE LA NÓMINA</div>
        
        <h4 style="margin: 15px 0 10px 0; color: #059669; font-weight: bold;">DEVENGADOS</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">CONCEPTO</th>
                    <th style="width: 25%;" class="text-right">VALOR</th>
                    <th style="width: 25%;" class="text-right">% DEL TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($composicionDevengados as $concepto)
                <tr>
                    <td>{{ $concepto['nombre'] }}</td>
                    <td class="text-right">${{ number_format($concepto['valor'], 0) }}</td>
                    <td class="text-right">{{ number_format($concepto['porcentaje'], 2) }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTAL DEVENGADOS:</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['devengado'], 0) }}</strong></td>
                    <td class="text-right"><strong>100.00%</strong></td>
                </tr>
            </tfoot>
        </table>

        <h4 style="margin: 15px 0 10px 0; color: #dc2626; font-weight: bold;">DEDUCCIONES</h4>
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">CONCEPTO</th>
                    <th style="width: 25%;" class="text-right">VALOR</th>
                    <th style="width: 25%;" class="text-right">% DEL DEVENGADO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($composicionDeducciones as $concepto)
                <tr>
                    <td>{{ $concepto['nombre'] }}</td>
                    <td class="text-right">${{ number_format($concepto['valor'], 0) }}</td>
                    <td class="text-right">{{ number_format($concepto['porcentaje'], 2) }}%</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td><strong>TOTAL DEDUCCIONES:</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['deducciones'], 0) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format(($totales['deducciones'] / $totales['devengado']) * 100, 2) }}%</strong></td>
                </tr>
            </tfoot>
        </table>

        {{-- Análisis de Rangos Salariales --}}
        <div class="section-title">DISTRIBUCIÓN POR RANGOS SALARIALES</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 40%;">RANGO SALARIAL</th>
                    <th style="width: 15%;" class="text-center">EMPLEADOS</th>
                    <th style="width: 15%;" class="text-right">% EMPLEADOS</th>
                    <th style="width: 15%;" class="text-right">COSTO</th>
                    <th style="width: 15%;" class="text-right">% COSTO</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rangosSalariales as $rango)
                <tr>
                    <td>{{ $rango['descripcion'] }}</td>
                    <td class="text-center">{{ $rango['empleados'] }}</td>
                    <td class="text-right">{{ number_format($rango['porcentaje_empleados'], 2) }}%</td>
                    <td class="text-right">${{ number_format($rango['costo'], 0) }}</td>
                    <td class="text-right">{{ number_format($rango['porcentaje_costo'], 2) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Alertas y Notas --}}
        @if(count($alertas) > 0)
        <div class="section-title">ALERTAS Y OBSERVACIONES</div>
        @foreach($alertas as $alerta)
        <div class="highlight-box">
            <div class="highlight-title">{{ $alerta['titulo'] }}</div>
            <div style="font-size: 9pt; color: #78350f;">{{ $alerta['mensaje'] }}</div>
        </div>
        @endforeach
        @endif

        {{-- Resumen Final --}}
        <div class="section-title">RESUMEN FINANCIERO</div>
        <table>
            <tbody>
                <tr>
                    <td style="width: 50%;"><strong>Total Devengado</strong></td>
                    <td style="width: 50%;" class="text-right">${{ number_format($totales['devengado'], 0) }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">(-) Total Deducciones</td>
                    <td class="text-right">${{ number_format($totales['deducciones'], 0) }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>= NETO A PAGAR</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['neto'], 0) }}</strong></td>
                </tr>
                <tr>
                    <td style="padding-top: 10px;"><strong>(+) Aportes Empleador (Seg. Social + Parafiscales + Provisiones)</strong></td>
                    <td class="text-right" style="padding-top: 10px;">${{ number_format($totales['aportes_empleador'], 0) }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>= COSTO TOTAL PARA LA EMPRESA</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['costo_total'], 0) }}</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <div>
                <strong>{{ config('nomina.empresa.razon_social') }}</strong><br>
                Documento confidencial - Uso exclusivo interno<br>
                Generado: {{ now()->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>
</body>
</html>