<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consolidado de Seguridad Social - {{ $nomina->numero_nomina }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .company-info {
            font-size: 9pt;
            color: #666;
            margin-bottom: 3px;
        }
        .report-title {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 15px;
            color: #1e40af;
        }
        .report-subtitle {
            font-size: 10pt;
            color: #666;
        }
        .info-section {
            background-color: #f3f4f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
            font-size: 8pt;
        }
        .info-value {
            color: #1f2937;
            font-size: 10pt;
        }
        .summary-boxes {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
            margin-bottom: 25px;
        }
        .summary-box {
            background: #ffffff;
            border: 2px solid #e5e7eb;
            border-radius: 5px;
            padding: 12px;
            text-align: center;
        }
        .summary-box.salud {
            border-color: #3b82f6;
        }
        .summary-box.pension {
            border-color: #10b981;
        }
        .summary-box.arl {
            border-color: #f59e0b;
        }
        .summary-box.sena {
            border-color: #8b5cf6;
        }
        .summary-box.icbf {
            border-color: #ec4899;
        }
        .summary-label {
            font-size: 8pt;
            color: #6b7280;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 14pt;
            font-weight: bold;
        }
        .summary-box.salud .summary-value { color: #3b82f6; }
        .summary-box.pension .summary-value { color: #10b981; }
        .summary-box.arl .summary-value { color: #f59e0b; }
        .summary-box.sena .summary-value { color: #8b5cf6; }
        .summary-box.icbf .summary-value { color: #ec4899; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
        }
        td {
            padding: 6px 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        tr:hover {
            background-color: #f3f4f6;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            background-color: #dbeafe !important;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #2563eb;
            padding: 10px 5px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1e40af;
            margin: 25px 0 15px 0;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 5px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 8pt;
            color: #6b7280;
        }
        .signature-section {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 60px;
        }
        .signature-box {
            border-top: 1px solid #000;
            padding-top: 10px;
            text-align: center;
        }
        .signature-name {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .signature-role {
            font-size: 8pt;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <div class="company-name">{{ config('nomina.empresa.razon_social') }}</div>
            <div class="company-info">NIT: {{ config('nomina.empresa.nit') }}</div>
            <div class="company-info">{{ config('nomina.empresa.direccion') }}</div>
            <div class="company-info">{{ config('nomina.empresa.ciudad') }}</div>
            
            <div class="report-title">CONSOLIDADO DE SEGURIDAD SOCIAL Y PARAFISCALES</div>
            <div class="report-subtitle">{{ $nomina->nombre }}</div>
            <div class="report-subtitle">Período: {{ $nomina->periodo->nombre }}</div>
        </div>

        {{-- Información General --}}
        <div class="info-section">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Número de Nómina:</div>
                    <div class="info-value">{{ $nomina->numero_nomina }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Liquidación:</div>
                    <div class="info-value">{{ $nomina->created_at->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Pago:</div>
                    <div class="info-value">{{ $nomina->fecha_pago->format('d/m/Y') }}</div>
                </div>
                <div class="info-item">
                    <div class="info-label">Empleados:</div>
                    <div class="info-value">{{ $nomina->numero_empleados }}</div>
                </div>
            </div>
        </div>

        {{-- Resumen de Totales --}}
        <div class="summary-boxes">
            <div class="summary-box salud">
                <div class="summary-label">SALUD</div>
                <div class="summary-value">${{ number_format($totales['salud_total'] / 1000000, 1) }}M</div>
                <div class="summary-label" style="margin-top: 5px;">
                    Empleado: ${{ number_format($totales['salud_empleado'], 0) }}<br>
                    Empleador: ${{ number_format($totales['salud_empleador'], 0) }}
                </div>
            </div>

            <div class="summary-box pension">
                <div class="summary-label">PENSIÓN</div>
                <div class="summary-value">${{ number_format($totales['pension_total'] / 1000000, 1) }}M</div>
                <div class="summary-label" style="margin-top: 5px;">
                    Empleado: ${{ number_format($totales['pension_empleado'], 0) }}<br>
                    Empleador: ${{ number_format($totales['pension_empleador'], 0) }}
                </div>
            </div>

            <div class="summary-box arl">
                <div class="summary-label">ARL</div>
                <div class="summary-value">${{ number_format($totales['arl'], 0) }}</div>
                <div class="summary-label" style="margin-top: 5px;">
                    Empleador: 100%
                </div>
            </div>

            <div class="summary-box sena">
                <div class="summary-label">SENA + ICBF</div>
                <div class="summary-value">${{ number_format($totales['parafiscales'], 0) }}</div>
                <div class="summary-label" style="margin-top: 5px;">
                    SENA: ${{ number_format($totales['sena'], 0) }}<br>
                    ICBF: ${{ number_format($totales['icbf'], 0) }}
                </div>
            </div>

            <div class="summary-box icbf">
                <div class="summary-label">CAJA</div>
                <div class="summary-value">${{ number_format($totales['caja'], 0) }}</div>
                <div class="summary-label" style="margin-top: 5px;">
                    Compensación: 4%
                </div>
            </div>
        </div>

        {{-- SECCIÓN 1: Seguridad Social por Empleado --}}
        <div class="section-title">1. DETALLE DE SEGURIDAD SOCIAL POR EMPLEADO</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">EMPLEADO</th>
                    <th style="width: 10%;" class="text-center">DOCUMENTO</th>
                    <th style="width: 12%;" class="text-right">IBC</th>
                    <th style="width: 10%;" class="text-right">SALUD EMP.</th>
                    <th style="width: 10%;" class="text-right">SALUD EMPR.</th>
                    <th style="width: 10%;" class="text-right">PENSIÓN EMP.</th>
                    <th style="width: 10%;" class="text-right">PENSIÓN EMPR.</th>
                    <th style="width: 9%;" class="text-right">FSP</th>
                    <th style="width: 9%;" class="text-right">ARL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles as $detalle)
                <tr>
                    <td>{{ $detalle->empleado->nombre_completo }}</td>
                    <td class="text-center">{{ $detalle->empleado->numero_documento }}</td>
                    <td class="text-right">${{ number_format($detalle->salario_basico, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->salud_empleado, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->salud_empleador, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->pension_empleado, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->pension_empleador, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->arl_empleador, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2"><strong>TOTALES:</strong></td>
                    <td class="text-right"><strong>${{ number_format(collect($detalles)->sum('salario_basico'), 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['salud_empleado'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['salud_empleador'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['pension_empleado'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['pension_empleador'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['arl'], 0) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div class="page-break"></div>

        {{-- SECCIÓN 2: Parafiscales por Empleado --}}
        <div class="section-title">2. DETALLE DE APORTES PARAFISCALES POR EMPLEADO</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">EMPLEADO</th>
                    <th style="width: 12%;" class="text-center">DOCUMENTO</th>
                    <th style="width: 15%;" class="text-right">IBC PARAFISCALES</th>
                    <th style="width: 12%;" class="text-right">SENA (2%)</th>
                    <th style="width: 12%;" class="text-right">ICBF (3%)</th>
                    <th style="width: 12%;" class="text-right">CAJA (4%)</th>
                    <th style="width: 12%;" class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles as $detalle)
                <tr>
                    <td>{{ $detalle->empleado->nombre_completo }}</td>
                    <td class="text-center">{{ $detalle->empleado->numero_documento }}</td>
                    <td class="text-right">${{ number_format($detalle->salario_basico, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->sena, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->icbf, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->caja, 0) }}</td>
                    <td class="text-right">${{ number_format($detalle->total_parafiscales ?? 0, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3"><strong>TOTALES:</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['sena'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['icbf'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['caja'], 0) }}</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['parafiscales'], 0) }}</strong></td>
                </tr>
            </tfoot>
        </table>

        {{-- SECCIÓN 3: Consolidado por Entidad --}}
        <div class="section-title">3. CONSOLIDADO POR ENTIDAD</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">ENTIDAD</th>
                    <th style="width: 20%;">NOMBRE</th>
                    <th style="width: 15%;" class="text-center">EMPLEADOS</th>
                    <th style="width: 20%;" class="text-right">APORTE EMPLEADO</th>
                    <th style="width: 20%;" class="text-right">APORTE EMPLEADOR</th>
                    <th style="width: 10%;" class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($consolidadoEntidades as $entidad)
                <tr>
                    <td><strong>{{ $entidad['tipo'] }}</strong></td>
                    <td>{{ $entidad['nombre'] }}</td>
                    <td class="text-center">{{ $entidad['cantidad_empleados'] }}</td>
                    <td class="text-right">${{ number_format($entidad['aporte_empleado'], 0) }}</td>
                    <td class="text-right">${{ number_format($entidad['aporte_empleador'], 0) }}</td>
                    <td class="text-right"><strong>${{ number_format($entidad['total'], 0) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- SECCIÓN 4: Resumen de Totales a Pagar --}}
        <div class="section-title">4. RESUMEN TOTAL A PAGAR</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">CONCEPTO</th>
                    <th style="width: 25%;" class="text-right">VALOR</th>
                    <th style="width: 25%;" class="text-right">PORCENTAJE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Seguridad Social Total</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['seguridad_social_total'], 0) }}</strong></td>
                    <td class="text-right">{{ number_format(($totales['seguridad_social_total'] / $nomina->total_devengado) * 100, 2) }}%</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">Salud (Empleado + Empleador)</td>
                    <td class="text-right">${{ number_format($totales['salud_total'], 0) }}</td>
                    <td class="text-right">{{ number_format(($totales['salud_total'] / $nomina->total_devengado) * 100, 2) }}%</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">Pensión (Empleado + Empleador)</td>
                    <td class="text-right">${{ number_format($totales['pension_total'], 0) }}</td>
                    <td class="text-right">{{ number_format(($totales['pension_total'] / $nomina->total_devengado) * 100, 2) }}%</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">ARL</td>
                    <td class="text-right">${{ number_format($totales['arl'], 0) }}</td>
                    <td class="text-right">{{ number_format(($totales['arl'] / $nomina->total_devengado) * 100, 2) }}%</td>
                </tr>
                <tr>
                    <td><strong>Parafiscales Total</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['parafiscales'], 0) }}</strong></td>
                    <td class="text-right">{{ number_format(($totales['parafiscales'] / $nomina->total_devengado) * 100, 2) }}%</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">SENA</td>
                    <td class="text-right">${{ number_format($totales['sena'], 0) }}</td>
                    <td class="text-right">2.00%</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">ICBF</td>
                    <td class="text-right">${{ number_format($totales['icbf'], 0) }}</td>
                    <td class="text-right">3.00%</td>
                </tr>
                <tr>
                    <td style="padding-left: 20px;">Caja de Compensación</td>
                    <td class="text-right">${{ number_format($totales['caja'], 0) }}</td>
                    <td class="text-right">4.00%</td>
                </tr>
                <tr class="total-row">
                    <td><strong>GRAN TOTAL A PAGAR</strong></td>
                    <td class="text-right"><strong>${{ number_format($totales['gran_total'], 0) }}</strong></td>
                    <td class="text-right"><strong>{{ number_format(($totales['gran_total'] / $nomina->total_devengado) * 100, 2) }}%</strong></td>
                </tr>
            </tbody>
        </table>

        {{-- Firmas --}}
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-name">_______________________________</div>
                <div class="signature-role">Elaborado por</div>
                <div class="signature-role">{{ auth()->user()->name ?? 'Sistema de Nómina' }}</div>
            </div>
            <div class="signature-box">
                <div class="signature-name">_______________________________</div>
                <div class="signature-role">Revisado por</div>
                <div class="signature-role">Jefe de Recursos Humanos</div>
            </div>
            <div class="signature-box">
                <div class="signature-name">_______________________________</div>
                <div class="signature-role">Aprobado por</div>
                <div class="signature-role">Director Financiero</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <div style="text-align: center;">
                Documento generado el {{ now()->format('d/m/Y H:i:s') }}<br>
                Este documento es un reporte consolidado de las obligaciones de seguridad social y parafiscales
            </div>
        </div>
    </div>
</body>
</html>