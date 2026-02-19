<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado de Cesantías</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.8;
            color: #000;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: white;
            padding: 60px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border: 3px double #1e40af;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e40af;
        }
        .logo-space {
            height: 80px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .logo-placeholder {
            width: 120px;
            height: 80px;
            border: 2px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 10pt;
        }
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .company-info {
            font-size: 10pt;
            color: #666;
            margin-bottom: 4px;
        }
        .certificate-title {
            font-size: 20pt;
            font-weight: bold;
            text-align: center;
            color: #1e40af;
            margin: 30px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .certificate-number {
            text-align: right;
            font-size: 10pt;
            color: #666;
            margin-bottom: 20px;
        }
        .intro-text {
            text-align: justify;
            margin-bottom: 20px;
            line-height: 2;
        }
        .certifies-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin: 30px 0;
            text-transform: uppercase;
        }
        .content-text {
            text-align: justify;
            margin-bottom: 20px;
            line-height: 2;
        }
        .employee-data {
            background-color: #f8f9fa;
            border-left: 4px solid #1e40af;
            padding: 20px;
            margin: 30px 0;
        }
        .data-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            margin-bottom: 10px;
        }
        .data-label {
            font-weight: bold;
            color: #333;
        }
        .data-value {
            color: #000;
        }
        .amounts-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .amounts-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        .amount-item {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            margin-bottom: 10px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        .amount-label {
            font-weight: bold;
        }
        .amount-value {
            font-size: 14pt;
            font-weight: bold;
        }
        .total-amount {
            border-top: 2px solid white;
            padding-top: 15px;
            margin-top: 10px;
        }
        .total-amount .amount-value {
            font-size: 18pt;
        }
        .closing-text {
            text-align: justify;
            margin: 30px 0;
            line-height: 2;
        }
        .date-location {
            text-align: right;
            margin: 40px 0;
            font-style: italic;
        }
        .signature-section {
            margin-top: 80px;
            text-align: center;
        }
        .signature-line {
            width: 350px;
            margin: 0 auto;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
        .signature-name {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 5px;
        }
        .signature-title {
            font-size: 11pt;
            color: #666;
            margin-bottom: 3px;
        }
        .signature-company {
            font-size: 10pt;
            color: #999;
        }
        .footer {
            margin-top: 60px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 9pt;
            color: #666;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100pt;
            color: rgba(30, 64, 175, 0.05);
            font-weight: bold;
            z-index: -1;
            user-select: none;
        }
        .btn-print {
            background-color: #1e40af;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 11pt;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .btn-print:hover {
            background-color: #1e3a8a;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 40px;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir Certificado</button>

    <div class="container">
        <div class="watermark">ORIGINAL</div>

        {{-- Header --}}
        <div class="header">
            <div class="logo-space">
                <div class="logo-placeholder">[LOGO]</div>
            </div>
            <div class="company-name">EMPRESA XYZ S.A.S.</div>
            <div class="company-info">NIT: 900.000.000-1</div>
            <div class="company-info">Calle 123 #45-67, Bogotá D.C., Colombia</div>
            <div class="company-info">Tel: (601) 123-4567 | Email: rrhh@empresaxyz.com</div>
        </div>

        <div class="certificate-number">
            Certificado No. CES-2026-0015
        </div>

        <div class="certificate-title">
            CERTIFICADO DE CESANTÍAS
        </div>

        {{-- Intro --}}
        <div class="intro-text">
            La suscrita Gerente de Recursos Humanos de <strong>EMPRESA XYZ S.A.S.</strong>, 
            identificada con NIT <strong>900.000.000-1</strong>, debidamente facultada para 
            expedir el presente documento,
        </div>

        <div class="certifies-title">
            CERTIFICA QUE:
        </div>

        {{-- Employee Data --}}
        <div class="employee-data">
            <div class="data-row">
                <div class="data-label">Nombre Completo:</div>
                <div class="data-value">JUAN CARLOS PÉREZ GARCÍA</div>
            </div>
            <div class="data-row">
                <div class="data-label">Tipo de Documento:</div>
                <div class="data-value">Cédula de Ciudadanía</div>
            </div>
            <div class="data-row">
                <div class="data-label">Número de Documento:</div>
                <div class="data-value">1.234.567.890</div>
            </div>
            <div class="data-row">
                <div class="data-label">Cargo:</div>
                <div class="data-value">Gerente General</div>
            </div>
            <div class="data-row">
                <div class="data-label">Fecha de Ingreso:</div>
                <div class="data-value">15 de Enero de 2020</div>
            </div>
            <div class="data-row">
                <div class="data-label">Salario Mensual:</div>
                <div class="data-value">$8.000.000</div>
            </div>
            <div class="data-row">
                <div class="data-label">Estado:</div>
                <div class="data-value">Activo</div>
            </div>
        </div>

        {{-- Content --}}
        <div class="content-text">
            El señor(a) <strong>JUAN CARLOS PÉREZ GARCÍA</strong> ha laborado en nuestra empresa 
            de manera ininterrumpida desde el <strong>15 de enero de 2020</strong> hasta la fecha, 
            desempeñando el cargo de <strong>Gerente General</strong>, devengando actualmente un 
            salario mensual de <strong>OCHO MILLONES DE PESOS M/CTE ($8.000.000)</strong>.
        </div>

        {{-- Amounts Box --}}
        <div class="amounts-box">
            <div class="amounts-title">Valores de Cesantías Consolidadas</div>
            
            <div class="amount-item">
                <div class="amount-label">Cesantías Consolidadas:</div>
                <div class="amount-value">$5.500.000</div>
            </div>
            
            <div class="amount-item">
                <div class="amount-label">Intereses sobre Cesantías (12% anual):</div>
                <div class="amount-value">$660.000</div>
            </div>
            
            <div class="amount-item total-amount">
                <div class="amount-label">TOTAL CESANTÍAS:</div>
                <div class="amount-value">$6.160.000</div>
            </div>
        </div>

        {{-- Additional Info --}}
        <div class="content-text">
            Los valores anteriormente mencionados corresponden al cálculo de cesantías consolidadas 
            hasta la fecha de expedición del presente certificado, calculados de conformidad con 
            lo establecido en el Código Sustantivo del Trabajo y demás normas concordantes.
        </div>

        <div class="content-text">
            <strong>NOTA IMPORTANTE:</strong> Las cesantías son consignadas anualmente antes del 
            15 de febrero de cada año en el fondo de cesantías <strong>PORVENIR</strong>, 
            de acuerdo con lo establecido por la ley.
        </div>

        {{-- Closing --}}
        <div class="closing-text">
            El presente certificado se expide a solicitud del interesado para los fines que 
            considere pertinentes, en la ciudad de Bogotá D.C., a los <strong>trece (13)</strong> 
            días del mes de <strong>febrero</strong> del año <strong>dos mil veintiséis (2026)</strong>.
        </div>

        {{-- Date and Location --}}
        <div class="date-location">
            Bogotá D.C., 13 de febrero de 2026
        </div>

        {{-- Signature --}}
        <div class="signature-section">
            <div class="signature-line">
                <div class="signature-name">_____________________________________</div>
                <div class="signature-title">LAURA PATRICIA GONZÁLEZ RAMÍREZ</div>
                <div class="signature-title">Gerente de Recursos Humanos</div>
                <div class="signature-company">EMPRESA XYZ S.A.S.</div>
                <div class="signature-company">NIT: 900.000.000-1</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p><strong>Nota:</strong> Este certificado es válido únicamente con firma y sello original.</p>
            <p>Documento generado electrónicamente el 13/02/2026 a las 22:35:00</p>
            <p>Para verificar la autenticidad de este documento, visite www.empresaxyz.com/verificar</p>
        </div>
    </div>
</body>
</html>