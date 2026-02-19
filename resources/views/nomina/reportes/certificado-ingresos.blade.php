<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Certificado de Ingresos y Retenciones</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            padding: 40px;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
            text-align: center;
        }
        
        .info-section {
            margin: 20px 0;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .info-table td {
            padding: 5px 10px;
            border: 1px solid #ccc;
        }
        
        .info-label {
            background-color: #f0f0f0;
            font-weight: bold;
            width: 35%;
        }
        
        .amounts-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .amounts-table th {
            background-color: #333;
            color: white;
            padding: 8px;
            text-align: left;
            border: 1px solid #333;
        }
        
        .amounts-table td {
            padding: 6px 8px;
            border: 1px solid #ccc;
        }
        
        .amounts-table .number {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .final-total {
            background-color: #333;
            color: white;
            font-weight: bold;
            font-size: 11pt;
        }
        
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-left: 4px solid #333;
            font-size: 9pt;
        }
        
        .signature {
            margin-top: 60px;
            text-align: center;
        }
        
        .signature-line {
            width: 300px;
            border-top: 2px solid #000;
            margin: 0 auto 10px auto;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <div class="company-name">{{ $empresa['nombre'] }}</div>
        <div>NIT: {{ $empresa['nit'] }}</div>
    </div>

    <!-- TÍTULO -->
    <div class="document-title">
        Certificado de Ingresos y Retenciones<br>
        Año Gravable {{ $periodo['anio'] }}
    </div>

    <!-- INFORMACIÓN DEL EMPLEADO -->
    <div class="info-section">
        <h3>DATOS DEL EMPLEADO</h3>
        <table class="info-table">
            <tr>
                <td class="info-label">Nombre Completo:</td>
                <td>{{ $empleado['nombre'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Identificación:</td>
                <td>{{ $empleado['documento'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Dirección:</td>
                <td>{{ $empleado['direccion'] ?? 'No registrada' }}</td>
            </tr>
            <tr>
                <td class="info-label">Cargo:</td>
                <td>{{ $empleado['cargo'] }}</td>
            </tr>
        </table>
    </div>

    <!-- INFORMACIÓN DEL PERÍODO -->
    <div class="info-section">
        <h3>PERÍODO CERTIFICADO</h3>
        <table class="info-table">
            <tr>
                <td class="info-label">Año Gravable:</td>
                <td>{{ $periodo['anio'] }}</td>
            </tr>
            <tr>
                <td class="info-label">Período:</td>
                <td>{{ $periodo['texto'] }}</td>
            </tr>
        </table>
    </div>

    <!-- INGRESOS -->
    <div class="info-section">
        <h3>INGRESOS RECIBIDOS</h3>
        <table class="amounts-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th style="width: 200px;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total de Pagos Recibidos (Devengados)</td>
                    <td class="number">${{ number_format($ingresos['total_devengado'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL INGRESOS</td>
                    <td class="number">${{ number_format($ingresos['total_devengado'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- DEDUCCIONES -->
    <div class="info-section">
        <h3>DEDUCCIONES Y APORTES</h3>
        <table class="amounts-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th style="width: 200px;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Aportes Obligatorios a Salud (4%)</td>
                    <td class="number">${{ number_format($deducciones['salud'], 2) }}</td>
                </tr>
                <tr>
                    <td>Aportes Obligatorios a Pensión (4%)</td>
                    <td class="number">${{ number_format($deducciones['pension'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>Total Aportes Obligatorios</td>
                    <td class="number">${{ number_format($deducciones['salud'] + $deducciones['pension'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- RETENCIONES -->
    <div class="info-section">
        <h3>RETENCIONES EN LA FUENTE</h3>
        <table class="amounts-table">
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th style="width: 200px;">Valor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Retención en la Fuente por Salarios</td>
                    <td class="number">${{ number_format($deducciones['retencion'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL RETENCIONES</td>
                    <td class="number">${{ number_format($deducciones['retencion'], 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- RESUMEN -->
    <div class="info-section">
        <h3>RESUMEN GENERAL</h3>
        <table class="amounts-table">
            <tbody>
                <tr>
                    <td><strong>Total Ingresos Recibidos</strong></td>
                    <td class="number"><strong>${{ number_format($ingresos['total_devengado'], 2) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Total Deducciones</strong></td>
                    <td class="number"><strong>${{ number_format($deducciones['total'], 2) }}</strong></td>
                </tr>
                <tr class="final-total">
                    <td>NETO RECIBIDO</td>
                    <td class="number">${{ number_format($neto, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- NOTAS -->
    <div class="notes">
        <strong>NOTAS IMPORTANTES:</strong><br>
        <ul style="margin-left: 20px; margin-top: 10px;">
            <li>Este certificado se expide para efectos de la declaración de renta año gravable {{ $periodo['anio'] }}.</li>
            <li>Los valores corresponden a pagos efectivamente recibidos durante el período certificado.</li>
            <li>Las retenciones practicadas fueron consignadas a la DIAN según normativa vigente.</li>
        </ul>
    </div>

    <!-- FIRMA -->
    <div class="signature">
        <div class="signature-line"></div>
        <div><strong>REPRESENTANTE LEGAL</strong></div>
        <div>{{ $empresa['nombre'] }}</div>
        <div style="margin-top: 10px; font-size: 9pt;">
            Fecha de expedición: {{ $fecha_expedicion }}
        </div>
    </div>

    <!-- FOOTER -->
    <div class="footer">
        <p>Certificado generado para {{ $empleado['nombre'] }}</p>
        <p>Este documento es válido sin firma manuscrita de acuerdo a la normatividad vigente</p>
    </div>
</body>
</html>