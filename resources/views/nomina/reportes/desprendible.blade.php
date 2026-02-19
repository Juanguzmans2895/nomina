<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desprendible de Pago</title>
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
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            color: #333;
        }
        
        .company-info {
            font-size: 9pt;
            color: #666;
        }
        
        .section-title {
            background-color: #f0f0f0;
            padding: 5px 10px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
            border-left: 4px solid #333;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            width: 30%;
            padding: 3px 5px;
            font-weight: bold;
            background-color: #f9f9f9;
        }
        
        .info-value {
            display: table-cell;
            width: 70%;
            padding: 3px 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .concepts-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .concepts-table th {
            background-color: #333;
            color: white;
            padding: 6px;
            text-align: left;
            font-size: 9pt;
        }
        
        .concepts-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #ddd;
        }
        
        .concepts-table .concept-name {
            width: 70%;
        }
        
        .concepts-table .concept-value {
            width: 30%;
            text-align: right;
        }
        
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .net-pay {
            background-color: #333;
            color: white;
            font-size: 12pt;
            padding: 10px;
            text-align: center;
            margin: 15px 0;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            text-align: center;
            color: #666;
        }
        
        .money {
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <!-- ENCABEZADO -->
    <div class="header">
        <div class="company-name">{{ $empresa['nombre'] }}</div>
        <div class="company-info">
            NIT: {{ $empresa['nit'] }}<br>
            {{ $empresa['direccion'] }} - {{ $empresa['ciudad'] }}
        </div>
        <div style="margin-top: 10px; font-size: 12pt; font-weight: bold;">
            DESPRENDIBLE DE PAGO
        </div>
    </div>

    <!-- INFORMACIÓN DEL EMPLEADO -->
    <div class="section-title">INFORMACIÓN DEL EMPLEADO</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Nombre:</div>
            <div class="info-value">{{ $empleado['nombre'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Documento:</div>
            <div class="info-value">{{ $empleado['documento'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Cargo:</div>
            <div class="info-value">{{ $empleado['cargo'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Dependencia:</div>
            <div class="info-value">{{ $empleado['dependencia'] ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- INFORMACIÓN DE LA NÓMINA -->
    <div class="section-title">INFORMACIÓN DEL PAGO</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Nómina:</div>
            <div class="info-value">{{ $nomina['numero'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Período:</div>
            <div class="info-value">{{ $nomina['periodo'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha de Pago:</div>
            <div class="info-value">{{ $nomina['fecha_pago'] }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Días Trabajados:</div>
            <div class="info-value">{{ $dias_trabajados }} días</div>
        </div>
    </div>

    <!-- DEVENGADOS -->
    <div class="section-title">DEVENGADOS</div>
    <table class="concepts-table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devengados as $concepto)
                @if($concepto['valor'] > 0)
                <tr>
                    <td class="concept-name">{{ $concepto['concepto'] }}</td>
                    <td class="concept-value money">${{ number_format($concepto['valor'], 2) }}</td>
                </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td>TOTAL DEVENGADO</td>
                <td class="money">${{ number_format($total_devengado, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- DEDUCCIONES -->
    <div class="section-title">DEDUCCIONES</div>
    <table class="concepts-table">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deducciones as $concepto)
                @if($concepto['valor'] > 0)
                <tr>
                    <td class="concept-name">{{ $concepto['concepto'] }}</td>
                    <td class="concept-value money">${{ number_format($concepto['valor'], 2) }}</td>
                </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td>TOTAL DEDUCCIONES</td>
                <td class="money">${{ number_format($total_deducciones, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- NETO A PAGAR -->
    <div class="net-pay">
        NETO A PAGAR: <span class="money">${{ number_format($neto_pagar, 2) }}</span>
    </div>

    <!-- INFORMACIÓN BANCARIA -->
    <div class="section-title">FORMA DE PAGO</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Banco:</div>
            <div class="info-value">{{ $banco['entidad'] ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tipo de Cuenta:</div>
            <div class="info-value">{{ ucfirst($banco['tipo_cuenta'] ?? 'N/A') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Número de Cuenta:</div>
            <div class="info-value">{{ $banco['numero_cuenta'] ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- INFORMACIÓN ADICIONAL -->
    <div class="section-title">APORTES DEL EMPLEADOR (INFORMATIVO)</div>
    <table class="concepts-table">
        <tbody>
            @foreach($seguridad_social_empleador as $concepto)
                @if($concepto['valor'] > 0)
                <tr>
                    <td class="concept-name">{{ $concepto['concepto'] }}</td>
                    <td class="concept-value money">${{ number_format($concepto['valor'], 2) }}</td>
                </tr>
                @endif
            @endforeach
            @foreach($parafiscales as $concepto)
                @if($concepto['valor'] > 0)
                <tr>
                    <td class="concept-name">{{ $concepto['concepto'] }}</td>
                    <td class="concept-value money">${{ number_format($concepto['valor'], 2) }}</td>
                </tr>
                @endif
            @endforeach
            @foreach($provisiones as $concepto)
                @if($concepto['valor'] > 0)
                <tr>
                    <td class="concept-name">{{ $concepto['concepto'] }}</td>
                    <td class="concept-value money">${{ number_format($concepto['valor'], 2) }}</td>
                </tr>
                @endif
            @endforeach
            <tr class="total-row">
                <td>COSTO TOTAL EMPLEADOR</td>
                <td class="money">${{ number_format($costo_empleador, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <p>Este es un documento generado electrónicamente y no requiere firma.</p>
        <p>Para cualquier consulta, comuníquese con el Departamento de Recursos Humanos.</p>
        <p>Generado el {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>