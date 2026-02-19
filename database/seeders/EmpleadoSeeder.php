<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class EmpleadoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Creando empleados...');
        
        $columns = Schema::getColumnListing('empleados');
        
        // 50 empleados con datos realistas y coherentes
        $empleadosBase = [
            // === GERENCIA (3) ===
            ['codigo'=>'GER-001','nombres'=>['Juan','Carlos'],'apellidos'=>['Pérez','García'],'doc'=>'1234567890','sexo'=>'M','nacimiento'=>'1975-03-15','email'=>'juan.perez@empresa.com','tel'=>'3001234567','dir'=>'Calle 123 #45-67','cargo'=>'Gerente General','depto'=>'Gerencia','ingreso'=>'2015-01-15','tipo_contrato'=>'indefinido','salario'=>12000000,'eps'=>'Sura','pension'=>'Porvenir','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'GER-002','nombres'=>['María','Elena'],'apellidos'=>['Rodríguez','López'],'doc'=>'9876543210','sexo'=>'F','nacimiento'=>'1978-07-22','email'=>'maria.rodriguez@empresa.com','tel'=>'3109876543','dir'=>'Carrera 45 #12-34','cargo'=>'Gerente Financiero','depto'=>'Gerencia','ingreso'=>'2016-03-01','tipo_contrato'=>'indefinido','salario'=>10000000,'eps'=>'Sanitas','pension'=>'Protección','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'GER-003','nombres'=>['Luis','Fernando'],'apellidos'=>['Hernández','Torres'],'doc'=>'7778889990','sexo'=>'M','nacimiento'=>'1980-09-25','email'=>'luis.hernandez@empresa.com','tel'=>'3007778889','dir'=>'Transversal 12 #34-56','cargo'=>'Gerente Comercial','depto'=>'Gerencia','ingreso'=>'2017-08-20','tipo_contrato'=>'indefinido','salario'=>11000000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            
            // === CONTABILIDAD (6) ===
            ['codigo'=>'CON-001','nombres'=>['Carlos','Alberto'],'apellidos'=>['Martínez','Sánchez'],'doc'=>'1112223334','sexo'=>'M','nacimiento'=>'1982-11-10','email'=>'carlos.martinez@empresa.com','tel'=>'3201112233','dir'=>'Avenida 68 #23-45','cargo'=>'Contador Principal','depto'=>'Contabilidad','ingreso'=>'2018-06-15','tipo_contrato'=>'indefinido','salario'=>6500000,'eps'=>'Compensar','pension'=>'Colfondos','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'CON-002','nombres'=>['Ana','Patricia'],'apellidos'=>['González','Ramírez'],'doc'=>'5556667778','sexo'=>'F','nacimiento'=>'1985-05-18','email'=>'ana.gonzalez@empresa.com','tel'=>'3155556677','dir'=>'Calle 50 #30-20','cargo'=>'Auxiliar Contable Senior','depto'=>'Contabilidad','ingreso'=>'2019-01-10','tipo_contrato'=>'indefinido','salario'=>4500000,'eps'=>'Salud Total','pension'=>'Porvenir','arl'=>'Positiva','caja'=>'Comfama'],
            ['codigo'=>'CON-003','nombres'=>['Diego','Andrés'],'apellidos'=>['Castro','Ruiz'],'doc'=>'2223334445','sexo'=>'M','nacimiento'=>'1988-12-05','email'=>'diego.castro@empresa.com','tel'=>'3202223334','dir'=>'Calle 100 #15-30','cargo'=>'Auxiliar Contable','depto'=>'Contabilidad','ingreso'=>'2020-09-15','tipo_contrato'=>'indefinido','salario'=>3800000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'CON-004','nombres'=>['Sandra','Milena'],'apellidos'=>['Ospina','Mejía'],'doc'=>'6667778889','sexo'=>'F','nacimiento'=>'1990-03-12','email'=>'sandra.ospina@empresa.com','tel'=>'3156667778','dir'=>'Carrera 30 #50-70','cargo'=>'Asistente Contable','depto'=>'Contabilidad','ingreso'=>'2021-02-01','tipo_contrato'=>'indefinido','salario'=>3200000,'eps'=>'Sanitas','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'CON-005','nombres'=>['Jorge','Enrique'],'apellidos'=>['Vélez','Cardona'],'doc'=>'8889990001','sexo'=>'M','nacimiento'=>'1987-08-20','email'=>'jorge.velez@empresa.com','tel'=>'3008889990','dir'=>'Calle 72 #10-25','cargo'=>'Tesorero','depto'=>'Contabilidad','ingreso'=>'2018-11-05','tipo_contrato'=>'indefinido','salario'=>5500000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'CON-006','nombres'=>['Gloria','Patricia'],'apellidos'=>['Morales','Gómez'],'doc'=>'9990001112','sexo'=>'F','nacimiento'=>'1983-06-15','email'=>'gloria.morales@empresa.com','tel'=>'3109990001','dir'=>'Transversal 45 #80-12','cargo'=>'Analista Financiero','depto'=>'Contabilidad','ingreso'=>'2019-07-10','tipo_contrato'=>'indefinido','salario'=>5000000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Positiva','caja'=>'Compensar'],
            
            // === RECURSOS HUMANOS (5) ===
            ['codigo'=>'RRH-001','nombres'=>['Laura','Sofía'],'apellidos'=>['Vargas','Moreno'],'doc'=>'3334445556','sexo'=>'F','nacimiento'=>'1986-02-14','email'=>'laura.vargas@empresa.com','tel'=>'3103334445','dir'=>'Carrera 78 #45-67','cargo'=>'Jefe de RRHH','depto'=>'Recursos Humanos','ingreso'=>'2017-02-01','tipo_contrato'=>'indefinido','salario'=>7000000,'eps'=>'Sanitas','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'RRH-002','nombres'=>['Andrés','Felipe'],'apellidos'=>['Rojas','Silva'],'doc'=>'1114445557','sexo'=>'M','nacimiento'=>'1984-10-08','email'=>'andres.rojas@empresa.com','tel'=>'3201114445','dir'=>'Calle 85 #20-45','cargo'=>'Analista de Selección','depto'=>'Recursos Humanos','ingreso'=>'2019-04-15','tipo_contrato'=>'indefinido','salario'=>4200000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'RRH-003','nombres'=>['Paola','Andrea'],'apellidos'=>['Quintero','Díaz'],'doc'=>'2225556668','sexo'=>'F','nacimiento'=>'1991-12-20','email'=>'paola.quintero@empresa.com','tel'=>'3152225556','dir'=>'Carrera 15 #60-80','cargo'=>'Coordinadora de Nómina','depto'=>'Recursos Humanos','ingreso'=>'2020-01-10','tipo_contrato'=>'indefinido','salario'=>4800000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'RRH-004','nombres'=>['Roberto','Carlos'],'apellidos'=>['Mendoza','Parra'],'doc'=>'3336667778','sexo'=>'M','nacimiento'=>'1989-05-25','email'=>'roberto.mendoza@empresa.com','tel'=>'3003336667','dir'=>'Avenida 80 #45-30','cargo'=>'Asistente de RRHH','depto'=>'Recursos Humanos','ingreso'=>'2021-06-01','tipo_contrato'=>'indefinido','salario'=>3000000,'eps'=>'Sanitas','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'RRH-005','nombres'=>['Camila','Valentina'],'apellidos'=>['Suárez','León'],'doc'=>'4447778889','sexo'=>'F','nacimiento'=>'1993-07-30','email'=>'camila.suarez@empresa.com','tel'=>'3104447778','dir'=>'Calle 90 #25-50','cargo'=>'Psicóloga Organizacional','depto'=>'Recursos Humanos','ingreso'=>'2022-03-15','tipo_contrato'=>'indefinido','salario'=>4500000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            
            // === TECNOLOGÍA (8) ===
            ['codigo'=>'TEC-001','nombres'=>['Miguel','Ángel'],'apellidos'=>['Torres','Ríos'],'doc'=>'5558889990','sexo'=>'M','nacimiento'=>'1985-04-18','email'=>'miguel.torres@empresa.com','tel'=>'3205558889','dir'=>'Carrera 50 #100-25','cargo'=>'Jefe de Tecnología','depto'=>'Tecnología','ingreso'=>'2016-05-20','tipo_contrato'=>'indefinido','salario'=>9000000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'TEC-002','nombres'=>['Santiago','José'],'apellidos'=>['Medina','Herrera'],'doc'=>'6669990001','sexo'=>'M','nacimiento'=>'1987-09-22','email'=>'santiago.medina@empresa.com','tel'=>'3156669990','dir'=>'Calle 120 #50-70','cargo'=>'Desarrollador Senior','depto'=>'Tecnología','ingreso'=>'2017-08-10','tipo_contrato'=>'indefinido','salario'=>7000000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'TEC-003','nombres'=>['Valentina','María'],'apellidos'=>['Cortés','Muñoz'],'doc'=>'7770001112','sexo'=>'F','nacimiento'=>'1990-11-15','email'=>'valentina.cortes@empresa.com','tel'=>'3007770001','dir'=>'Avenida 100 #30-40','cargo'=>'Desarrolladora Full Stack','depto'=>'Tecnología','ingreso'=>'2019-02-15','tipo_contrato'=>'indefinido','salario'=>6000000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'TEC-004','nombres'=>['Daniel','Felipe'],'apellidos'=>['Gutiérrez','Reyes'],'doc'=>'8881112223','sexo'=>'M','nacimiento'=>'1992-03-08','email'=>'daniel.gutierrez@empresa.com','tel'=>'3108881112','dir'=>'Carrera 70 #80-15','cargo'=>'Desarrollador Frontend','depto'=>'Tecnología','ingreso'=>'2020-06-01','tipo_contrato'=>'indefinido','salario'=>5500000,'eps'=>'Sanitas','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'TEC-005','nombres'=>['Isabella','Sofía'],'apellidos'=>['Ramírez','Castro'],'doc'=>'9992223334','sexo'=>'F','nacimiento'=>'1991-08-12','email'=>'isabella.ramirez@empresa.com','tel'=>'3209992223','dir'=>'Calle 95 #20-50','cargo'=>'Desarrolladora Backend','depto'=>'Tecnología','ingreso'=>'2020-09-15','tipo_contrato'=>'indefinido','salario'=>5500000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'TEC-006','nombres'=>['Sebastián','David'],'apellidos'=>['Pineda','Gómez'],'doc'=>'1003334445','sexo'=>'M','nacimiento'=>'1994-01-25','email'=>'sebastian.pineda@empresa.com','tel'=>'3151003334','dir'=>'Transversal 60 #40-70','cargo'=>'Ingeniero DevOps','depto'=>'Tecnología','ingreso'=>'2021-03-10','tipo_contrato'=>'indefinido','salario'=>6500000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'TEC-007','nombres'=>['Mariana','Alejandra'],'apellidos'=>['Salazar','Ortiz'],'doc'=>'1114445556','sexo'=>'F','nacimiento'=>'1993-06-30','email'=>'mariana.salazar@empresa.com','tel'=>'3001114445','dir'=>'Calle 110 #25-80','cargo'=>'Analista de QA','depto'=>'Tecnología','ingreso'=>'2021-07-01','tipo_contrato'=>'indefinido','salario'=>4800000,'eps'=>'Sanitas','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'TEC-008','nombres'=>['Nicolás','Eduardo'],'apellidos'=>['Bermúdez','Vargas'],'doc'=>'1225556667','sexo'=>'M','nacimiento'=>'1995-10-05','email'=>'nicolas.bermudez@empresa.com','tel'=>'3101225556','dir'=>'Carrera 40 #70-90','cargo'=>'Soporte Técnico','depto'=>'Tecnología','ingreso'=>'2022-05-15','tipo_contrato'=>'indefinido','salario'=>3500000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            
            // === COMERCIAL / VENTAS (10) ===
            ['codigo'=>'COM-001','nombres'=>['Claudia','Patricia'],'apellidos'=>['Jiménez','Ortiz'],'doc'=>'4445556667','sexo'=>'F','nacimiento'=>'1982-04-30','email'=>'claudia.jimenez@empresa.com','tel'=>'3154445556','dir'=>'Avenida 19 #80-25','cargo'=>'Gerente Comercial','depto'=>'Comercial','ingreso'=>'2016-05-10','tipo_contrato'=>'indefinido','salario'=>8500000,'eps'=>'Salud Total','pension'=>'Protección','arl'=>'Positiva','caja'=>'Comfama'],
            ['codigo'=>'VEN-001','nombres'=>['Fernando','José'],'apellidos'=>['Arango','Silva'],'doc'=>'1336667778','sexo'=>'M','nacimiento'=>'1986-08-15','email'=>'fernando.arango@empresa.com','tel'=>'3201336667','dir'=>'Calle 75 #30-50','cargo'=>'Coordinador de Ventas','depto'=>'Ventas','ingreso'=>'2018-03-20','tipo_contrato'=>'indefinido','salario'=>5500000,'eps'=>'Sura','pension'=>'Porvenir','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'VEN-002','nombres'=>['Carolina','Andrea'],'apellidos'=>['Espinosa','Márquez'],'doc'=>'1447778889','sexo'=>'F','nacimiento'=>'1990-02-20','email'=>'carolina.espinosa@empresa.com','tel'=>'3151447778','dir'=>'Carrera 55 #40-60','cargo'=>'Ejecutiva de Ventas Senior','depto'=>'Ventas','ingreso'=>'2019-06-10','tipo_contrato'=>'indefinido','salario'=>4800000,'eps'=>'Compensar','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'VEN-003','nombres'=>['Julián','Andrés'],'apellidos'=>['Patiño','Rojas'],'doc'=>'1558889990','sexo'=>'M','nacimiento'=>'1988-12-08','email'=>'julian.patino@empresa.com','tel'=>'3001558889','dir'=>'Avenida 68 #50-70','cargo'=>'Ejecutivo de Ventas','depto'=>'Ventas','ingreso'=>'2020-01-15','tipo_contrato'=>'indefinido','salario'=>4200000,'eps'=>'Sanitas','pension'=>'Protección','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'VEN-004','nombres'=>['Natalia','Cristina'],'apellidos'=>['Arias','Montoya'],'doc'=>'1669990001','sexo'=>'F','nacimiento'=>'1992-05-18','email'=>'natalia.arias@empresa.com','tel'=>'3101669990','dir'=>'Calle 85 #45-80','cargo'=>'Ejecutiva de Ventas','depto'=>'Ventas','ingreso'=>'2020-08-01','tipo_contrato'=>'indefinido','salario'=>4000000,'eps'=>'Sura','pension'=>'Porvenir','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'VEN-005','nombres'=>['Oscar','Javier'],'apellidos'=>['Londoño','Pérez'],'doc'=>'1770001112','sexo'=>'M','nacimiento'=>'1991-09-25','email'=>'oscar.londono@empresa.com','tel'=>'3201770001','dir'=>'Transversal 80 #20-40','cargo'=>'Asesor Comercial','depto'=>'Ventas','ingreso'=>'2021-02-10','tipo_contrato'=>'indefinido','salario'=>3800000,'eps'=>'Compensar','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'VEN-006','nombres'=>['Daniela','Marcela'],'apellidos'=>['Valencia','Ramírez'],'doc'=>'1881112223','sexo'=>'F','nacimiento'=>'1993-11-12','email'=>'daniela.valencia@empresa.com','tel'=>'3151881112','dir'=>'Calle 100 #60-25','cargo'=>'Asesora Comercial','depto'=>'Ventas','ingreso'=>'2021-09-01','tipo_contrato'=>'indefinido','salario'=>3600000,'eps'=>'Sanitas','pension'=>'Protección','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'VEN-007','nombres'=>['Esteban','Mauricio'],'apellidos'=>['Duque','González'],'doc'=>'1992223334','sexo'=>'M','nacimiento'=>'1994-03-22','email'=>'esteban.duque@empresa.com','tel'=>'3001992223','dir'=>'Carrera 90 #30-55','cargo'=>'Asesor Comercial Junior','depto'=>'Ventas','ingreso'=>'2022-04-01','tipo_contrato'=>'indefinido','salario'=>3200000,'eps'=>'Sura','pension'=>'Porvenir','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'VEN-008','nombres'=>['Gabriela','Lucía'],'apellidos'=>['Molina','Torres'],'doc'=>'2003334445','sexo'=>'F','nacimiento'=>'1995-07-08','email'=>'gabriela.molina@empresa.com','tel'=>'3102003334','dir'=>'Avenida 120 #40-70','cargo'=>'Asesora Comercial Junior','depto'=>'Ventas','ingreso'=>'2022-07-15','tipo_contrato'=>'indefinido','salario'=>3000000,'eps'=>'Compensar','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'VEN-009','nombres'=>['Mateo','Alejandro'],'apellidos'=>['Cárdenas','Ruiz'],'doc'=>'2114445556','sexo'=>'M','nacimiento'=>'1996-01-15','email'=>'mateo.cardenas@empresa.com','tel'=>'3202114445','dir'=>'Calle 130 #25-60','cargo'=>'Practicante de Ventas','depto'=>'Ventas','ingreso'=>'2023-01-10','tipo_contrato'=>'fijo','salario'=>1500000,'eps'=>'Sanitas','pension'=>'Protección','arl'=>'Positiva','caja'=>'Colsubsidio'],
            
            // === MARKETING (5) ===
            ['codigo'=>'MKT-001','nombres'=>['Adriana','Marcela'],'apellidos'=>['Cano','Moreno'],'doc'=>'2225556667','sexo'=>'F','nacimiento'=>'1984-06-20','email'=>'adriana.cano@empresa.com','tel'=>'3152225556','dir'=>'Carrera 25 #80-35','cargo'=>'Jefe de Marketing','depto'=>'Marketing','ingreso'=>'2017-04-15','tipo_contrato'=>'indefinido','salario'=>7500000,'eps'=>'Sura','pension'=>'Porvenir','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'MKT-002','nombres'=>['Ricardo','Andrés'],'apellidos'=>['Bernal','López'],'doc'=>'2336667778','sexo'=>'M','nacimiento'=>'1988-10-12','email'=>'ricardo.bernal@empresa.com','tel'=>'3002336667','dir'=>'Avenida 50 #60-80','cargo'=>'Analista de Marketing Digital','depto'=>'Marketing','ingreso'=>'2019-08-01','tipo_contrato'=>'indefinido','salario'=>5000000,'eps'=>'Compensar','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'MKT-003','nombres'=>['Luisa','Fernanda'],'apellidos'=>['Franco','Henao'],'doc'=>'2447778889','sexo'=>'F','nacimiento'=>'1991-04-28','email'=>'luisa.franco@empresa.com','tel'=>'3102447778','dir'=>'Calle 65 #35-50','cargo'=>'Community Manager','depto'=>'Marketing','ingreso'=>'2020-05-10','tipo_contrato'=>'indefinido','salario'=>4000000,'eps'=>'Sanitas','pension'=>'Protección','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'MKT-004','nombres'=>['Alejandro','David'],'apellidos'=>['Urrego','Sánchez'],'doc'=>'2558889990','sexo'=>'M','nacimiento'=>'1992-12-05','email'=>'alejandro.urrego@empresa.com','tel'=>'3202558889','dir'=>'Transversal 30 #70-90','cargo'=>'Diseñador Gráfico','depto'=>'Marketing','ingreso'=>'2021-01-15','tipo_contrato'=>'indefinido','salario'=>3800000,'eps'=>'Sura','pension'=>'Porvenir','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'MKT-005','nombres'=>['Sofía','Alejandra'],'apellidos'=>['Bustamante','Gil'],'doc'=>'2669990001','sexo'=>'F','nacimiento'=>'1994-08-18','email'=>'sofia.bustamante@empresa.com','tel'=>'3152669990','dir'=>'Carrera 45 #85-25','cargo'=>'Asistente de Marketing','depto'=>'Marketing','ingreso'=>'2022-02-01','tipo_contrato'=>'indefinido','salario'=>2800000,'eps'=>'Compensar','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Compensar'],
            
            // === PRODUCCIÓN (7) ===
            ['codigo'=>'PRO-001','nombres'=>['Héctor','Manuel'],'apellidos'=>['Restrepo','Zapata'],'doc'=>'2770001112','sexo'=>'M','nacimiento'=>'1979-05-10','email'=>'hector.restrepo@empresa.com','tel'=>'3002770001','dir'=>'Calle 40 #50-70','cargo'=>'Jefe de Producción','depto'=>'Producción','ingreso'=>'2015-09-20','tipo_contrato'=>'indefinido','salario'=>6500000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'PRO-002','nombres'=>['William','Alberto'],'apellidos'=>['Giraldo','Botero'],'doc'=>'2881112223','sexo'=>'M','nacimiento'=>'1983-07-15','email'=>'william.giraldo@empresa.com','tel'=>'3102881112','dir'=>'Avenida 30 #40-60','cargo'=>'Supervisor de Producción','depto'=>'Producción','ingreso'=>'2017-03-10','tipo_contrato'=>'indefinido','salario'=>4800000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'PRO-003','nombres'=>['Jhon','Jairo'],'apellidos'=>['Montoya','Ríos'],'doc'=>'2992223334','sexo'=>'M','nacimiento'=>'1985-11-22','email'=>'jhon.montoya@empresa.com','tel'=>'3202992223','dir'=>'Carrera 20 #30-50','cargo'=>'Operario Calificado','depto'=>'Producción','ingreso'=>'2018-06-15','tipo_contrato'=>'indefinido','salario'=>3500000,'eps'=>'Sanitas','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'PRO-004','nombres'=>['José','Luis'],'apellidos'=>['Agudelo','Martínez'],'doc'=>'3003334445','sexo'=>'M','nacimiento'=>'1987-03-18','email'=>'jose.agudelo@empresa.com','tel'=>'3153003334','dir'=>'Transversal 15 #50-70','cargo'=>'Operario Calificado','depto'=>'Producción','ingreso'=>'2019-01-20','tipo_contrato'=>'indefinido','salario'=>3500000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'PRO-005','nombres'=>['Edison','Fabián'],'apellidos'=>['Valencia','Gómez'],'doc'=>'3114445556','sexo'=>'M','nacimiento'=>'1990-09-08','email'=>'edison.valencia@empresa.com','tel'=>'3003114445','dir'=>'Calle 25 #40-60','cargo'=>'Operario','depto'=>'Producción','ingreso'=>'2020-04-10','tipo_contrato'=>'indefinido','salario'=>2600000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'PRO-006','nombres'=>['Carlos','Andrés'],'apellidos'=>['Osorio','Henao'],'doc'=>'3225556667','sexo'=>'M','nacimiento'=>'1992-12-25','email'=>'carlos.osorio@empresa.com','tel'=>'3103225556','dir'=>'Carrera 10 #25-45','cargo'=>'Operario','depto'=>'Producción','ingreso'=>'2021-07-01','tipo_contrato'=>'indefinido','salario'=>2600000,'eps'=>'Sanitas','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'PRO-007','nombres'=>['Manuel','Antonio'],'apellidos'=>['Jaramillo','Arboleda'],'doc'=>'3336667779','sexo'=>'M','nacimiento'=>'1994-06-12','email'=>'manuel.jaramillo@empresa.com','tel'=>'3203336667','dir'=>'Avenida 15 #30-55','cargo'=>'Auxiliar de Producción','depto'=>'Producción','ingreso'=>'2022-09-15','tipo_contrato'=>'indefinido','salario'=>1800000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            
            // === LOGÍSTICA (6) ===
            ['codigo'=>'LOG-001','nombres'=>['Armando','José'],'apellidos'=>['Cardona','Mejía'],'doc'=>'3447778889','sexo'=>'M','nacimiento'=>'1981-08-30','email'=>'armando.cardona@empresa.com','tel'=>'3153447778','dir'=>'Calle 55 #60-80','cargo'=>'Jefe de Logística','depto'=>'Logística','ingreso'=>'2016-11-10','tipo_contrato'=>'indefinido','salario'=>6000000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'LOG-002','nombres'=>['Gustavo','Adolfo'],'apellidos'=>['Murillo','Castro'],'doc'=>'3558889990','sexo'=>'M','nacimiento'=>'1984-02-14','email'=>'gustavo.murillo@empresa.com','tel'=>'3003558889','dir'=>'Transversal 40 #70-90','cargo'=>'Coordinador de Bodega','depto'=>'Logística','ingreso'=>'2018-05-20','tipo_contrato'=>'indefinido','salario'=>4200000,'eps'=>'Sanitas','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'LOG-003','nombres'=>['Jhonatan','David'],'apellidos'=>['Ochoa','Pérez'],'doc'=>'3669990001','sexo'=>'M','nacimiento'=>'1989-10-05','email'=>'jhonatan.ochoa@empresa.com','tel'=>'3103669990','dir'=>'Carrera 35 #45-65','cargo'=>'Auxiliar de Bodega','depto'=>'Logística','ingreso'=>'2019-08-15','tipo_contrato'=>'indefinido','salario'=>2400000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
            ['codigo'=>'LOG-004','nombres'=>['Robinson','Javier'],'apellidos'=>['Sierra','Toro'],'doc'=>'3770001112','sexo'=>'M','nacimiento'=>'1991-12-18','email'=>'robinson.sierra@empresa.com','tel'=>'3203770001','dir'=>'Avenida 25 #55-75','cargo'=>'Auxiliar de Bodega','depto'=>'Logística','ingreso'=>'2020-10-01','tipo_contrato'=>'indefinido','salario'=>2400000,'eps'=>'Compensar','pension'=>'Porvenir','arl'=>'Positiva','caja'=>'Compensar'],
            ['codigo'=>'LOG-005','nombres'=>['Fredy','Alexander'],'apellidos'=>['Hurtado','Ramírez'],'doc'=>'3881112223','sexo'=>'M','nacimiento'=>'1993-04-22','email'=>'fredy.hurtado@empresa.com','tel'=>'3153881112','dir'=>'Calle 45 #30-50','cargo'=>'Conductor','depto'=>'Logística','ingreso'=>'2021-05-10','tipo_contrato'=>'indefinido','salario'=>2800000,'eps'=>'Sanitas','pension'=>'Colfondos','arl'=>'Positiva','caja'=>'Colsubsidio'],
            ['codigo'=>'LOG-006','nombres'=>['Nelson','Enrique'],'apellidos'=>['Parra','González'],'doc'=>'3992223334','sexo'=>'M','nacimiento'=>'1995-08-08','email'=>'nelson.parra@empresa.com','tel'=>'3003992223','dir'=>'Transversal 50 #40-60','cargo'=>'Mensajero','depto'=>'Logística','ingreso'=>'2022-11-01','tipo_contrato'=>'indefinido','salario'=>1600000,'eps'=>'Sura','pension'=>'Protección','arl'=>'Sura ARL','caja'=>'Compensar'],
        ];
        
        foreach ($empleadosBase as $idx => $empBase) {
            $empleado = [
                'tipo_documento' => 'CC',
                'numero_documento' => $empBase['doc'],
                'primer_nombre' => $empBase['nombres'][0],
                'primer_apellido' => $empBase['apellidos'][0],
                'fecha_nacimiento' => $empBase['nacimiento'],
                'email' => $empBase['email'],
                'codigo_empleado' => $empBase['codigo'],
                'fecha_ingreso' => $empBase['ingreso'],
                'tipo_contrato' => $empBase['tipo_contrato'],
                'cargo' => $empBase['cargo'],
                'salario_basico' => $empBase['salario'],
                'estado' => 'activo',
                'clase_riesgo' => 0.00522,
                'aplica_auxilio_transporte' => ($empBase['salario'] <= 2600000) ? 1 : 0,
                'numero_hijos' => rand(0, 3),
                'estado_civil' => ['soltero','casado','union_libre'][rand(0,2)],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            if (!empty($empBase['nombres'][1])) $empleado['segundo_nombre'] = $empBase['nombres'][1];
            if (!empty($empBase['apellidos'][1])) $empleado['segundo_apellido'] = $empBase['apellidos'][1];
            
            if (in_array('sexo', $columns)) $empleado['sexo'] = $empBase['sexo'];
            if (in_array('telefono_movil', $columns)) $empleado['telefono_movil'] = $empBase['tel'];
            if (in_array('direccion', $columns)) $empleado['direccion'] = $empBase['dir'];
            if (in_array('dependencia', $columns)) $empleado['dependencia'] = $empBase['depto'];
            if (in_array('eps', $columns)) $empleado['eps'] = $empBase['eps'];
            if (in_array('fondo_pension', $columns)) $empleado['fondo_pension'] = $empBase['pension'];
            if (in_array('arl', $columns)) $empleado['arl'] = $empBase['arl'];
            if (in_array('caja_compensacion', $columns)) $empleado['caja_compensacion'] = $empBase['caja'];
            
            DB::table('empleados')->insert($empleado);
            
            if (($idx + 1) % 10 == 0) {
                $this->command->line("   ✅ " . ($idx + 1) . " empleados creados...");
            }
        }
        
        $total = DB::table('empleados')->count();
        $this->command->info("✅ Total empleados: {$total}");
    }
}