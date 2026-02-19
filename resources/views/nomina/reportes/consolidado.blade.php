<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consolidado - {{ $nomina->numero_nomina }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #000;
        }
        
        .container {
            padding: 15mm;
        }
        
        /* Header */
        .header {
            text-align: center;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 3px;
        }
        
        .company-info {
            font-size: 8pt;
            color: #666;
        }
        
        .report-title {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 10px;
            color: #1e40af;
        }
        
        .report-subtitle {
            font-size: 9pt;
            color: #666;
        }
        
        /* Info Section */
        .info-section {
            background-color: #f3f4f6;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        
        .info-item {
            font-size: 8pt;
        }
        
        .info-label {
            font-weight: bold;
            color: #4b5563;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        /* Summary Cards */
        .summary-section {
            margin-bottom: 15px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .summary-card {
            background: #f9fafb;
            border-left: 3px solid;
            padding: 8px;
            text-align: center;
        }
        
        .summary-card.blue { border-left-color: #3b82f6; }
        .summary-card.green { border-left-color: #10b981; }
        .summary-card.orange { border-left-color: #f59e0b; }
        .summary-card.purple { border-left-color: #8b5cf6; }
        .summary-card.indigo { border-left-color: #6366f1; }
        
        .summary-label {
            font-size: 7pt;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        .summary-value {
            font-size: 11pt;
            font-weight: bold;
        }
        
        .summary-card.blue .summary-value { color: #3b82f6; }
        .summary-card.green .summary-value { color: #10b981; }
        .summary-card.orange .summary-value { color: #f59e0b; }
        .summary-card.purple .summary-value { color: #8b5cf6; }
        .summary-card.indigo .summary-value { color: #6366f1; }
        
        .summary-detail {
            font-size: 7pt;
            color: #6b7280;
            line-height: 1.3;
        }
        
        /* Section Title */
        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1e40af;
            margin: 15px 0 8px 0;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 3px;
        }
        
        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8pt;
        }
        
        thead {
            background-color: #1e40af;
            color: white;
        }
        
        th {
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 7pt;
        }
        
        td {
            padding: 5px 4px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        tfoot {
            background-color: #dbeafe;
            font-weight: bold;
        }
        
        tfoot td {
            padding: 6px 4px;
            border-top: 2px solid #1e40af;
        }
        
        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 7pt;
            color: #6b7280;
            text-align: center;
        }
        
        /* Page Break */
        .page-break {
            page-break-after: always;
        }
        
        /* Print Styles */
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            
            .container {
                padding: 10mm;
            }
        }
        
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <div class="company-name">{{ config('app.name', 'MI EMPRESA S.A.S.') }}</div>
            <div class="company-info">NIT: {{ config('nomina.empresa.nit', '900.000.000-1') }}</div>
            <div class="company-info">{{ config('nomina.empresa.direccion', 'Dirección') }} - {{ config('nomina.empresa.ciudad', 'Ciudad') }}</div>
            
            <div class="report-title">CONSOLIDADO DE SEGURIDAD SOCIAL Y PARAFISCALES</div>
            <div class="report-subtitle">{{ $nomina->nombre ?? 'N/A' }}</div>
            <div class="report-subtitle">Período: {{ $nomina->periodo->nombre ?? 'N/A' }}</div>
        </div>

        {{-- Información General --}}
        <div class="info-section">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Número de Nómina:</span>
                    <span class="info-value">{{ $nomina->numero_nomina }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha Liquidación:</span>
                    <span class="info-value">{{ $nomina->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fecha de Pago:</span>
                    <span class="info-value">{{ $nomina->fecha_pago->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Empleados:</span>
                    <span class="info-value">{{ $nomina->numero_empleados }}</span>
                </div>
            </div>
        </div>

        {{-- Resumen de Totales --}}
        <div class="summary-section">
            <div class="summary-grid">
                <div class="summary-card blue">
                    <div class="summary-label">Total Devengado</div>
                    <div class="summary-value">${{ number_format($totales['total_devengado'] ?? 0, 0) }}</div>
                </div>

                <div class="summary-card red">
                    <div class="summary-label">Total Deducciones</div>
                    <div class="summary-value">${{ number_format($totales['total_deducciones'] ?? 0, 0) }}</div>
                </div>

                <div class="summary-card green">
                    <div class="summary-label">Total Neto</div>
                    <div class="summary-value">${{ number_format($totales['total_neto'] ?? 0, 0) }}</div>
                </div>

                <div class="summary-card purple">
                    <div class="summary-label">Seguridad Social</div>
                    <div class="summary-value">${{ number_format(($totales['total_salud_empleador'] ?? 0) + ($totales['total_pension_empleador'] ?? 0) + ($totales['total_arl'] ?? 0), 0) }}</div>
                </div>

                <div class="summary-card orange">
                    <div class="summary-label">Costo Total</div>
                    <div class="summary-value">${{ number_format($totales['costo_total_empleador'] ?? 0, 0) }}</div>
                </div>
            </div>
        </div>

        {{-- Tabla: Seguridad Social por Empleado --}}
        <div class="section-title">1. Detalle de Seguridad Social por Empleado</div>

        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Empleado</th>
                    <th style="width: 10%;" class="text-center">Documento</th>
                    <th style="width: 12%;" class="text-right">IBC</th>
                    <th style="width: 10%;" class="text-right">Salud Emp</th>
                    <th style="width: 10%;" class="text-right">Salud Empr</th>
                    <th style="width: 10%;" class="text-right">Pensión Emp</th>
                    <th style="width: 10%;" class="text-right">Pensión Empr</th>
                    <th style="width: 9%;" class="text-right">ARL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles as $detalle)
                <tr>
                    <td>{{ $detalle->empleado->nombre_completo }}</td>
                    <td class="text-center">{{ $detalle->empleado->numero_documento }}</td>
                    <td class="text-right">${{ number_format($detalle->salario_basico, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->total_devengado, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->total_deducciones, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->total_neto, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->salud_empleador + $detalle->pension_empleador + $detalle->arl_empleador, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->total_parafiscales, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->total_provisiones, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->costo_total_empleador, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><strong>TOTALES ({{ $numero_empleados }} empleados):</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['total_devengado'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['total_deducciones'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['total_neto'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['total_salud_empleador'] + $totales['total_pension_empleador'] + $totales['total_arl'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['total_parafiscales'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['total_provisiones'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['costo_total_empleador'], 0) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Este reporte corresponde al consolidado de aportes a seguridad social y parafiscales</p>
        </div>
    </div>
</body>
</html>