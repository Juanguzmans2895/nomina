<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado Laboral</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.8;
            padding: 60px 80px;
            color: #000;
        }
        
        .membrete {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
        }
        
        .logo {
            max-width: 120px;
            margin-bottom: 10px;
        }
        
        .empresa-nombre {
            font-size: 18pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .empresa-nit {
            font-size: 11pt;
            margin-bottom: 3px;
        }
        
        .empresa-info {
            font-size: 10pt;
            color: #333;
        }
        
        .titulo {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 40px 0 30px 0;
            text-decoration: underline;
        }
        
        .motivo {
            text-align: center;
            font-size: 11pt;
            margin-bottom: 30px;
            font-style: italic;
        }
        
        .contenido {
            text-align: justify;
            margin-bottom: 20px;
        }
        
        .parrafo {
            margin-bottom: 20px;
            text-indent: 50px;
        }
        
        .dato-importante {
            font-weight: bold;
        }
        
        .firma-seccion {
            margin-top: 80px;
        }
        
        .firma-linea {
            width: 300px;
            border-top: 2px solid #000;
            margin: 0 auto 10px auto;
        }
        
        .firma-nombre {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .firma-cargo {
            text-align: center;
            font-size: 11pt;
        }
        
        .pie {
            position: fixed;
            bottom: 30px;
            left: 80px;
            right: 80px;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        
        .fecha-expedicion {
            margin-top: 30px;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <!-- MEMBRETE -->
    <div class="membrete">
        @if(isset($empresa['logo']))
        <img src="{{ $empresa['logo'] }}" alt="Logo" class="logo">
        @endif
        <div class="empresa-nombre">{{ $empresa['nombre'] }}</div>
        <div class="empresa-nit">NIT: {{ $empresa['nit'] }}</div>
        <div class="empresa-info">
            {{ $empresa['direccion'] }}<br>
            {{ $empresa['ciudad'] }} - Tel: {{ $empresa['telefono'] ?? '' }}
        </div>
    </div>

    <!-- TÍTULO -->
    <div class="titulo">Certificado Laboral</div>

    <!-- MOTIVO -->
    <div class="motivo">{{ $motivo }}</div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <p class="parrafo">
            El suscrito <span class="dato-importante">{{ $empresa['representante'] ?? 'REPRESENTANTE LEGAL' }}</span>,
            en su calidad de <span class="dato-importante">{{ $empresa['cargo_representante'] ?? 'GERENTE GENERAL' }}</span>
            de <span class="dato-importante">{{ strtoupper($empresa['nombre']) }}</span>, identificada con NIT
            <span class="dato-importante">{{ $empresa['nit'] }}</span>,
        </p>

        <p class="parrafo" style="text-align: center; font-weight: bold; font-size: 14pt;">
            CERTIFICA QUE:
        </p>

        <p class="parrafo">
            <span class="dato-importante">{{ strtoupper($empleado['nombre']) }}</span>, identificado(a) con
            <span class="dato-importante">{{ $empleado['documento'] }}</span>,
            @if($tipo === 'vigencia')
                labora actualmente en esta entidad
            @else
                laboró en esta entidad
            @endif
            desde el <span class="dato-importante">{{ $empleado['fecha_ingreso_texto'] }}</span>
            @if($tipo !== 'vigencia' && isset($empleado['fecha_retiro']))
                hasta el <span class="dato-importante">{{ $empleado['fecha_retiro_texto'] }}</span>
            @endif
            , desempeñando el cargo de <span class="dato-importante">{{ strtoupper($empleado['cargo']) }}</span>
            @if(isset($empleado['dependencia']))
                en la dependencia de <span class="dato-importante">{{ strtoupper($empleado['dependencia']) }}</span>
            @endif
            , con un contrato de tipo <span class="dato-importante">{{ strtoupper($empleado['tipo_contrato']) }}</span>.
        </p>

        @if($incluir_salario)
        <p class="parrafo">
            Su asignación salarial mensual es de
            <span class="dato-importante">{{ strtoupper($empleado['salario_texto']) }} PESOS M/CTE ($ {{ number_format($empleado['salario_basico'], 2) }})</span>.
        </p>
        @endif

        @if($incluir_funciones && isset($funciones))
        <p class="parrafo">
            Entre sus principales funciones se encuentran:
        </p>
        <p class="parrafo" style="margin-left: 50px;">
            {{ $funciones }}
        </p>
        @endif

        <p class="parrafo">
            El tiempo de servicio
            @if($tipo === 'vigencia')
                a la fecha
            @else
                fue
            @endif
            de <span class="dato-importante">{{ strtoupper($tiempo_servicio['texto']) }}</span>.
        </p>

        <p class="parrafo">
            Durante su vinculación laboral, {{ explode(' ', $empleado['nombre'])[0] }}
            @if($tipo === 'vigencia')
                ha demostrado
            @else
                demostró
            @endif
            ser una persona responsable, comprometida con sus funciones y con excelente desempeño laboral.
        </p>

        <p class="parrafo">
            La presente certificación se expide a solicitud del interesado para los fines que estime convenientes.
        </p>
    </div>

    <!-- FECHA DE EXPEDICIÓN -->
    <div class="fecha-expedicion">
        <p>
            Dado en {{ $empresa['ciudad'] }}, a los {{ $fecha_expedicion_texto }}.
        </p>
    </div>

    <!-- FIRMA -->
    <div class="firma-seccion">
        <div class="firma-linea"></div>
        <div class="firma-nombre">{{ strtoupper($empresa['representante'] ?? 'REPRESENTANTE LEGAL') }}</div>
        <div class="firma-cargo">{{ $empresa['cargo_representante'] ?? 'Gerente General' }}</div>
        <div class="firma-cargo" style="margin-top: 5px; font-size: 10pt;">
            {{ $empresa['nombre'] }}
        </div>
    </div>

    <!-- PIE DE PÁGINA -->
    <div class="pie">
        <table style="width: 100%; font-size: 9pt;">
            <tr>
                <td style="width: 50%;">
                    Certificado generado electrónicamente
                </td>
                <td style="width: 50%; text-align: right;">
                    Fecha de expedición: {{ $fecha_expedicion }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>