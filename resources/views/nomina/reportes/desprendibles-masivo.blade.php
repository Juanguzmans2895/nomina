<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desprendibles de Pago - {{ $nomina->numero_nomina }}</title>
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
        }
        .page {
            page-break-after: always;
            padding: 15mm;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .company-info {
            font-size: 8pt;
            color: #666;
        }
        .title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            color: #1e40af;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 15px;
            background: #f9fafb;
            padding: 10px;
            border-radius: 5px;
        }
        .info-item {
            font-size: 8pt;
        }
        .info-label {
            font-weight: bold;
            color: #4b5563;
        }
        .info-value {
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8pt;
        }
        th {
            background-color: #1e40af;
            color: white;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
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
        .section-title {
            background-color: #dbeafe;
            padding: 5px 8px;
            font-weight: bold;
            margin: 10px 0 5px 0;
            font-size: 9pt;
            border-left: 3px solid #1e40af;
        }
        .total-row {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .total-row td {
            border-top: 2px solid #333;
            padding: 8px 4px;
        }
        .summary-box {
            background-color: #eff6ff;
            border: 2px solid #1e40af;
            padding: 10px;
            margin-top: 15px;
            text-align: center;
        }
        .summary-label {
            font-size: 8pt;
            color: #4b5563;
            margin-bottom: 3px;
        }
        .summary-value {
            font-size: 16pt;
            font-weight: bold;
            color: #1e40af;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 7pt;
            color: #6b7280;
            text-align: center;
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
    @foreach($detalles as $detalle)
    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div class="company-name">{{ config('app.name', 'MI EMPRESA S.A.S.') }}</div>
            <div class="company-info">NIT: {{ config('nomina.empresa.nit', '900.000.000-1') }}</div>
            <div class="company-info">{{ config('nomina.empresa.direccion', 'Dirección') }} - {{ config('nomina.empresa.ciudad', 'Ciudad') }}</div>
        </div>

        <div class="title">DESPRENDIBLE DE PAGO</div>

        {{-- Información de la nómina --}}
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Número de Nómina:</span>
                <span class="info-value">{{ $nomina->numero_nomina }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Período:</span>
                <span class="info-value">{{ $nomina->periodo->nombre ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Fecha de Pago:</span>
                <span class="info-value">{{ $nomina->fecha_pago->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Tipo de Nómina:</span>
                <span class="info-value">{{ $nomina->tipo->nombre ?? 'N/A' }}</span>
            </div>
        </div>

        {{-- Información del empleado --}}
        <div class="section-title">DATOS DEL EMPLEADO</div>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Nombre:</span>
                <span class="info-value">{{ $detalle->empleado->nombre_completo }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Documento:</span>
                <span class="info-value">{{ $detalle->empleado->tipo_documento }} {{ $detalle->empleado->numero_documento }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Cargo:</span>
                <span class="info-value">{{ $detalle->empleado->cargo ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Salario Básico:</span>
                <span class="info-value">${{ number_format($detalle->salario_basico, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Devengados --}}
        <div class="section-title">DEVENGADOS</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 60%;">CONCEPTO</th>
                    <th style="width: 20%;" class="text-center">CANTIDAD</th>
                    <th style="width: 20%;" class="text-right">VALOR</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Salario Básico</td>
                    <td class="text-center">30</td>
                    <td class="text-right">${{ number_format($detalle->salario_basico, 0, ',', '.') }}</td>
                </tr>
                {{-- Aquí irían otros conceptos devengados si existen --}}
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">TOTAL DEVENGADO</td>
                    <td class="text-right">${{ number_format($detalle->total_devengado, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Deducciones --}}
        <div class="section-title">DEDUCCIONES</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 60%;">CONCEPTO</th>
                    <th style="width: 20%;" class="text-center">%</th>
                    <th style="width: 20%;" class="text-right">VALOR</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Salud (EPS)</td>
                    <td class="text-center">4.0%</td>
                    <td class="text-right">${{ number_format($detalle->salud_empleado ?? ($detalle->salario_basico * 0.04), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pensión</td>
                    <td class="text-center">4.0%</td>
                    <td class="text-right">${{ number_format($detalle->pension_empleado ?? ($detalle->salario_basico * 0.04), 0, ',', '.') }}</td>
                </tr>
                {{-- Aquí irían otras deducciones si existen --}}
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">TOTAL DEDUCCIONES</td>
                    <td class="text-right">${{ number_format($detalle->total_deducciones, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Resumen --}}
        <div class="summary-box">
            <div class="summary-label">VALOR NETO A PAGAR</div>
            <div class="summary-value">${{ number_format($detalle->total_neto, 0, ',', '.') }}</div>
        </div>

        {{-- Aportes del Empleador (informativo) --}}
        <div class="section-title">APORTES DEL EMPLEADOR (Informativo)</div>
        <table>
            <tbody>
                <tr>
                    <td>Salud</td>
                    <td class="text-right">${{ number_format($detalle->salud_empleador ?? ($detalle->salario_basico * 0.085), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pensión</td>
                    <td class="text-right">${{ number_format($detalle->pension_empleador ?? ($detalle->salario_basico * 0.12), 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>ARL</td>
                    <td class="text-right">${{ number_format($detalle->arl_empleador ?? ($detalle->salario_basico * 0.00522), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <p>Este documento es un desprendible de pago generado electrónicamente</p>
            <p>Documento generado el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p><strong>Importante:</strong> Conserve este documento para sus registros personales</p>
        </div>
    </div>
    @endforeach
</body>
</html>