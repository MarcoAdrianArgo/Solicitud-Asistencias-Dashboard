<?php
/**
* © Argo Almacenadora ®
* Fecha: 20/01/2023
* Developer: DIEGO ALTAMIRANO SUAREZ.
* Proyecto: Dashboard Talento Humano
* Version --
*/
include_once '../libs/conOra.php';

include_once 'Perfil.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion']) && $_POST['accion'] === 'cargarDatos') {
        if (isset($_POST['clase']) && isset($_POST['metodo'])) {
            $clase = $_POST['clase'];
            $metodo = $_POST['metodo'];

            if (class_exists($clase) && method_exists($clase, $metodo)) {
                // Verificar si 'iidPlaza' está presente en los datos POST
                if (isset($_POST['iidPlaza'])) {
                    $iidPlaza = $_POST['iidPlaza'];

                    // Crear una instancia de la clase
                    $instancia = new $clase();
                    // Llamar al método y pasar 'iidPlaza' como argumento
                    $datos = $instancia->$metodo($iidPlaza);

                    // Devolver los datos como respuesta AJAX
                    echo json_encode($datos);
                    exit;
                }else{
                    $instancia = new $clase();
                    // Llamar al método y pasar 'iidPlaza' como argumento
                    $datos = $instancia->$metodo();

                    // Devolver los datos como respuesta AJAX
                    echo json_encode($datos);
                }
            }
        }
    }
}


class AsistenciaPersonal
{
	/*######################## TABLA DE EMPLEADOS #########################*/
	public function plazasActivas()
	{
		$conn = conexion::conectar();
		$res_array = array();


		$sql = "SELECT PL.V_RAZON_SOCIAL, PL.IID_PLAZA FROM PLAZA PL WHERE PL.I_EMPRESA_PADRE = 1 ";
		#echo $sql;
		$stid = oci_parse($conn, $sql);
		oci_execute($stid);

		while (($row = oci_fetch_assoc($stid)) != false)
		{
			$res_array[]= $row;
		}

		oci_free_statement($stid);
		oci_close($conn);

		return $res_array;
	}

    public function obtenerAsistencia($iid_plaza){
        $conn = conexion::conectar();
        $res_array = array();

        $sql = "SELECT NP.IID_EMPLEADO AS IID,
                        NP.V_NOMBRE || ' ' || NP.V_APE_PAT || ' ' || NP.V_APE_MAT AS NOMBRE,
                        CASE
                            WHEN RCD.IID_DEPTO = 1 THEN
                            RA.V_DESCRIPCION || '-' || REPLACE(AL.V_NOMBRE, '-', '')
                            WHEN RCD.IID_DEPTO >= 2 THEN
                            RCD.V_DESCRIPCION || '-' || REPLACE(AL.V_NOMBRE, '-', '')
                        END AS V_DESCRIPCION,
                        RP.V_DESCRIPCION as DESCRIPCION,
                        PL.V_RAZON_SOCIAL AS PLAZA,
                        A.formatted_punch_time AS TIEMPO,
                        CASE
                            WHEN A.formatted_punch_time IS NOT NULL OR
                                (SELECT COUNT(*)
                                    FROM RH_FALTAS R
                                    INNER JOIN RH_FALTAS_CAT RC ON R.ID_TIPO_FALTA =
                                                                RC.ID_TIPO_FALTA
                                    WHERE trunc(R.D_FEC_INICIO) <= trunc(SYSDATE)
                                    AND trunc(R.D_FEC_FIN) >= trunc(SYSDATE)
                                    AND R.IID_EMPLEADO = NP.IID_EMPLEADO
                                    AND R.ID_TIPO_FALTA IN (8, 14)) > 0 THEN
                            A.HORA
                            WHEN AL.IID_ALMACEN IN
                                (1468, 1562, 43, 1706, 1770, 1773, 1136, 1705, 1554, 1775, 1755, 1387) THEN
                            '09:00:00'
                        END AS HORA,
                        CASE  WHEN (SELECT COUNT(*) FROM PERSONNEL_EMPLOYEE@DLRELOJ MP WHERE MP.EMP_CODE = NP.IID_EMPLEADO ) = 0 THEN
                            'FALTA REGISTRO EN RELOJ '
                            ELSE (SELECT CASE
                                    WHEN RC.S_DESCRIPCION IS NULL THEN
                                    ' '
                                    ELSE
                                    RC.S_DESCRIPCION
                                END AS FALTA
                            FROM RH_FALTAS R
                            INNER JOIN RH_FALTAS_CAT RC ON R.ID_TIPO_FALTA = RC.ID_TIPO_FALTA
                            WHERE trunc(R.D_FEC_INICIO) <= trunc(SYSDATE)
                            AND trunc(R.D_FEC_FIN) >= TRUNC(SYSDATE)
                            AND R.IID_EMPLEADO = NP.IID_EMPLEADO) END AS FALTA,
                        CASE
                            WHEN A.formatted_punch_time IS NOT NULL
                            THEN
                            'PRESENTE'
                            WHEN AL.IID_ALMACEN IN
                                (1468, 1562, 43, 1706, 1770, 1773, 1136, 1705, 1554, 1775, 1755, 1387) THEN
                            'PRESENTE'
                            WHEN ALMEMP.I_CHECA_RELOJ = 1 THEN
                            'PRESENTE'
                        END AS LLEGO_TIEMPO,
                        CASE
                            WHEN AL.V_NOMBRE IS NULL THEN
                            'SIN ALMACEN NOMBREALMA'
                            ELSE
                            AL.V_NOMBRE || 'NOMBREALMA'
                        END AS NOMBREALMA,
                        AL.IID_ALMACEN AS ID_ALMACEN,
                        RCD.IID_DEPTO,
                        CASE WHEN AL.IID_ALMACEN IS NOT NULL THEN 
                        CASE
                            WHEN RCD.IID_DEPTO = 1 THEN
                            (SELECT COUNT(*)
                            FROM NO_CONTRATO PERSCON
                                INNER JOIN NO_PERSONAL NUM_PERSONAL ON PERSCON.IID_CONTRATO =
                                                                    NUM_PERSONAL.IID_CONTRATO
                                                                AND PERSCON.IID_EMPLEADO =
                                                                    NUM_PERSONAL.IID_EMPLEADO
                                LEFT JOIN RH_CAT_AREAS RAREAS ON PERSCON.IID_AREA =
                                                                RAREAS.IID_AREA
                                                            AND PERSCON.IID_DEPTO =
                                                                RAREAS.IID_DEPTO            
                                LEFT JOIN RH_ALMACEN_EMP REMP ON REMP.NID_EMPLEADO = PERSCON.IID_EMPLEADO AND REMP.N_BASE = 1
                                WHERE PERSCON.IID_DEPTO = RCD.IID_DEPTO
                                AND PERSCON.IID_AREA = RA.IID_AREA
                                AND PERSCON.IID_PLAZA = PL.IID_PLAZA
                                AND NUM_PERSONAL.S_STATUS = 1
                                AND REMP.NID_ALMACEN = AL.IID_ALMACEN)
                            WHEN RCD.IID_DEPTO >= 2 THEN
                            (SELECT COUNT(*)
                            FROM NO_CONTRATO PERSCON
                                INNER JOIN NO_PERSONAL NUM_PERSONAL ON PERSCON.IID_CONTRATO =
                                                                    NUM_PERSONAL.IID_CONTRATO
                                                                AND PERSCON.IID_EMPLEADO =
                                                                    NUM_PERSONAL.IID_EMPLEADO
                                LEFT JOIN RH_CAT_AREAS RAREAS ON PERSCON.IID_AREA =
                                                                RAREAS.IID_AREA
                                                            AND PERSCON.IID_DEPTO =
                                                                RAREAS.IID_DEPTO            
                                LEFT JOIN RH_ALMACEN_EMP REMP ON REMP.NID_EMPLEADO = PERSCON.IID_EMPLEADO AND REMP.N_BASE = 1
                                WHERE PERSCON.IID_DEPTO = RCD.IID_DEPTO
                                AND PERSCON.IID_PLAZA = PL.IID_PLAZA
                                AND NUM_PERSONAL.S_STATUS = 1
                                AND REMP.NID_ALMACEN = AL.IID_ALMACEN
                                )
                        END 
                        ELSE
                            (select count(*) from no_personal np 
                                    left join rh_almacen_emp remp on remp.nid_empleado = np.iid_empleado 
                            where np.s_status = 1
                                    and np.iid_plaza = PL.IID_PLAZA
                                    and remp.nid_empleado is null)
                        END AS TOTAL_EMPLEADO,
                        CASE
                            WHEN AL.IID_ALMACEN IN
                                (1468, 1562, 43, 1706, 1770, 1773, 1136, 1705, 1554, 1775, 1755, 1387) THEN
                            0
                            WHEN AL.IID_ALMACEN IS NULL THEN
                                (SELECT COUNT(DISTINCT(EMP_CODE))
                                FROM RH_PERSONAL_CONTRATO PERSCON
                                INNER JOIN NO_PERSONAL NUM_PERSONAL ON PERSCON.IID_RCONTRATO =
                                                                    NUM_PERSONAL.IID_CONTRATO
                                                                AND PERSCON.IID_EMPLEADO =
                                                                    NUM_PERSONAL.IID_EMPLEADO
                                LEFT JOIN RH_ALMACEN_EMP REMP ON REMP.NID_EMPLEADO = NUM_PERSONAL.IID_EMPLEADO 
                                WHERE NUM_PERSONAL.S_STATUS = 1 
                                    AND NUM_PERSONAL.IID_PLAZA = PL.IID_PLAZA
                                    AND REMP.NID_EMPLEADO IS NULL 
                                )
                            ELSE
                            CASE
                            WHEN RCD.IID_DEPTO = 1 THEN
                            (SELECT COUNT(*) FROM (SELECT NP.*, TO_NUMBER(B.EMP_CODE) FROM NO_PERSONAL NP 
                                       LEFT JOIN ICLOCK_TRANSACTION@DLRELOJ B ON NP.IID_EMPLEADO = B.EMP_CODE AND TO_CHAR(punch_time, 'DD/MM/YYYY') = TO_CHAR(SYSDATE, 'DD/MM/YYYY')       
                                       INNER JOIN RH_ALMACEN_EMP REMP ON REMP.NID_EMPLEADO = NP.IID_EMPLEADO AND REMP.N_BASE = 1 AND REMP.I_CHECA_RELOJ <> 1 AND REMP.NID_ALMACEN NOT IN (1468, 1562, 43, 1706, 1770, 1773, 1136, 1705, 1554, 1775, 1755, 1387)
                                       INNER JOIN PERSONNEL_EMPLOYEE@DLRELOJ RGR ON NP.IID_EMPLEADO = RGR.EMP_CODE
                                       LEFT JOIN RH_FALTAS RF ON RF.IID_EMPLEADO = NP.IID_EMPLEADO AND RF.ID_TIPO_FALTA <> 6 AND (trunc(RF.d_fec_inicio) <= trunc(SYSDATE) AND trunc(RF.d_fec_fin) >= TRUNC(SYSDATE))
                                       INNER JOIN NO_CONTRATO PSCON ON PSCON.IID_EMPLEADO = NP.IID_EMPLEADO AND PSCON.IID_CONTRATO = NP.IID_CONTRATO
                                       WHERE NP.S_STATUS = 1 AND B.EMP_CODE IS NULL AND NP.IID_PLAZA = PL.IID_PLAZA AND REMP.NID_ALMACEN = AL.IID_ALMACEN AND RF.IID_EMPLEADO IS NULL AND PSCON.IID_DEPTO = RCD.IID_DEPTO AND PSCON.IID_AREA = RA.IID_AREA
                              UNION ALL
                              SELECT NP.*, NP.IID_EMPLEADO FROM NO_PERSONAL NP 
                                      INNER JOIN RH_FALTAS RF ON RF.IID_EMPLEADO = NP.IID_EMPLEADO
                                      INNER JOIN NO_CONTRATO PSCON ON PSCON.IID_CONTRATO = NP.IID_CONTRATO AND PSCON.IID_EMPLEADO = NP.IID_EMPLEADO 
                                      INNER JOIN RH_ALMACEN_EMP REM ON NP.IID_EMPLEADO = REM.NID_EMPLEADO AND REM.N_BASE =1
                                      WHERE RF.ID_TIPO_FALTA = 6 
                                            AND NP.IID_PLAZA = PL.IID_PLAZA 
                                            AND PSCON.IID_DEPTO = RCD.IID_DEPTO
                                            AND PSCON.IID_AREA = RA.IID_AREA
                                            AND REM.NID_ALMACEN = AL.IID_ALMACEN
                                            AND (trunc(RF.d_fec_inicio) <= trunc(SYSDATE) AND trunc(RF.d_fec_fin) >= TRUNC(SYSDATE))
                                            ) X)                           
                            ELSE
                            (SELECT COUNT(*) FROM (SELECT NP.*, TO_NUMBER(B.EMP_CODE) FROM NO_PERSONAL NP 
                                       LEFT JOIN ICLOCK_TRANSACTION@DLRELOJ B ON NP.IID_EMPLEADO = B.EMP_CODE AND TO_CHAR(punch_time, 'DD/MM/YYYY') = TO_CHAR(SYSDATE, 'DD/MM/YYYY')        
                                       INNER JOIN RH_ALMACEN_EMP REMP ON REMP.NID_EMPLEADO = NP.IID_EMPLEADO AND REMP.N_BASE = 1 AND REMP.I_CHECA_RELOJ <> 1 AND REMP.NID_ALMACEN NOT IN (1468, 1562, 43, 1706, 1770, 1773, 1136, 1705, 1554, 1775, 1755, 1387)
                                       INNER JOIN PERSONNEL_EMPLOYEE@DLRELOJ RGR ON NP.IID_EMPLEADO = RGR.EMP_CODE
                                       LEFT JOIN RH_FALTAS RF ON RF.IID_EMPLEADO = NP.IID_EMPLEADO AND RF.ID_TIPO_FALTA <> 6 AND (trunc(RF.d_fec_inicio) <= trunc(SYSDATE) AND trunc(RF.d_fec_fin) >= TRUNC(SYSDATE))
                                       INNER JOIN NO_CONTRATO PSCON ON PSCON.IID_EMPLEADO = NP.IID_EMPLEADO AND PSCON.IID_CONTRATO = NP.IID_CONTRATO
                                       WHERE NP.S_STATUS = 1 AND B.EMP_CODE IS NULL AND NP.IID_PLAZA = PL.IID_PLAZA AND REMP.NID_ALMACEN = AL.IID_ALMACEN AND RF.IID_EMPLEADO IS NULL AND PSCON.IID_DEPTO = RCD.IID_DEPTO
                              UNION ALL
                              SELECT NP.*, NP.IID_EMPLEADO FROM NO_PERSONAL NP 
                                      INNER JOIN RH_FALTAS RF ON RF.IID_EMPLEADO = NP.IID_EMPLEADO
                                      INNER JOIN NO_CONTRATO PSCON ON PSCON.IID_CONTRATO = NP.IID_CONTRATO AND PSCON.IID_EMPLEADO = NP.IID_EMPLEADO 
                                      INNER JOIN RH_ALMACEN_EMP REM ON NP.IID_EMPLEADO = REM.NID_EMPLEADO AND REM.N_BASE =1
                                      WHERE RF.ID_TIPO_FALTA = 6 
                                            AND NP.IID_PLAZA = PL.IID_PLAZA
                                            AND PSCON.IID_DEPTO = RCD.IID_DEPTO
                                            AND REM.NID_ALMACEN = AL.IID_ALMACEN
                                            AND (trunc(RF.d_fec_inicio) <= trunc(SYSDATE) AND trunc(RF.d_fec_fin) >= TRUNC(SYSDATE))
                                            ) X)                        
                        END END AS TOTAL_ASISTENCIAS,
                        (SELECT COUNT(CASE
                                        WHEN NCON.IID_ALMACEN IN
                                                (1468, 1562, 43, 1706, 1770, 1773, 1136, 1705, 1554, 1775, 1755, 1387) THEN
                                            1
                                        ELSE
                                            NULL
                                        END)
                            FROM RH_PERSONAL_CONTRATO PERSCON
                            INNER JOIN NO_PERSONAL NUM_PERSONAL ON PERSCON.IID_RCONTRATO =
                                                                    NUM_PERSONAL.IID_CONTRATO
                                                                AND PERSCON.IID_EMPLEADO =
                                                                    NUM_PERSONAL.IID_EMPLEADO
                            INNER JOIN RH_CAT_AREAS RAREAS ON PERSCON.IID_AREA =
                                                            RAREAS.IID_AREA
                                                        AND PERSCON.IID_DEPTO =
                                                            RAREAS.IID_DEPTO
                            INNER JOIN NO_CONTRATO NCON ON PERSCON.IID_RCONTRATO =
                                                            NCON.IID_CONTRATO
                                                        AND PERSCON.IID_EMPLEADO =
                                                            NCON.IID_EMPLEADO
                            WHERE PERSCON.IID_DEPTO = RCD.IID_DEPTO
                            AND PERSCON.IID_PLAZA = PL.IID_PLAZA
                            AND NUM_PERSONAL.S_STATUS = 1
                            AND NCON.IID_ALMACEN = AL.IID_ALMACEN) AS TOTAL_HABILITADOS,
                        CASE
                            WHEN RCD.IID_DEPTO = 1 THEN
                            (SELECT COUNT(DISTINCT(PERSCON.IID_EMPLEADO))
                                FROM RH_PERSONAL_CONTRATO PERSCON
                                INNER JOIN NO_PERSONAL NUM_PERSONAL ON PERSCON.IID_RCONTRATO =
                                                                    NUM_PERSONAL.IID_CONTRATO
                                                                AND PERSCON.IID_EMPLEADO =
                                                                    NUM_PERSONAL.IID_EMPLEADO
                                INNER JOIN RH_CAT_AREAS RAREAS ON PERSCON.IID_AREA =
                                                                RAREAS.IID_AREA
                                                            AND PERSCON.IID_DEPTO =
                                                                RAREAS.IID_DEPTO
                                INNER JOIN NO_CONTRATO NCON ON PERSCON.IID_RCONTRATO =
                                                            NCON.IID_CONTRATO
                                                        AND PERSCON.IID_EMPLEADO =
                                                            NCON.IID_EMPLEADO
                                INNER JOIN rh_faltas RF ON NUM_PERSONAL.IID_EMPLEADO =
                                                        RF.IID_EMPLEADO
                                WHERE PERSCON.IID_DEPTO = RCD.IID_DEPTO
                                AND PERSCON.IID_AREA = RA.IID_AREA
                                AND PERSCON.IID_PLAZA = PL.IID_PLAZA
                                AND NUM_PERSONAL.S_STATUS = 1
                                AND NCON.IID_ALMACEN = AL.IID_ALMACEN
                                AND RF.id_tipo_falta IN (8, 14)
                                AND trunc(RF.D_FEC_INICIO) <= trunc(SYSDATE)
                                AND trunc(RF.D_FEC_FIN) >= trunc(SYSDATE))
                            ELSE
                            (SELECT COUNT(DISTINCT(PERSCON.IID_EMPLEADO))
                                FROM RH_PERSONAL_CONTRATO PERSCON
                                INNER JOIN NO_PERSONAL NUM_PERSONAL ON PERSCON.IID_RCONTRATO =
                                                                    NUM_PERSONAL.IID_CONTRATO
                                                                AND PERSCON.IID_EMPLEADO =
                                                                    NUM_PERSONAL.IID_EMPLEADO
                                INNER JOIN RH_CAT_AREAS RAREAS ON PERSCON.IID_AREA =
                                                                RAREAS.IID_AREA
                                                            AND PERSCON.IID_DEPTO =
                                                                RAREAS.IID_DEPTO
                                INNER JOIN NO_CONTRATO NCON ON PERSCON.IID_RCONTRATO =
                                                            NCON.IID_CONTRATO
                                                        AND PERSCON.IID_EMPLEADO =
                                                            NCON.IID_EMPLEADO
                                INNER JOIN rh_faltas RF ON NUM_PERSONAL.IID_EMPLEADO =
                                                        RF.IID_EMPLEADO
                                WHERE PERSCON.IID_DEPTO = RCD.IID_DEPTO
                                AND PERSCON.IID_PLAZA = PL.IID_PLAZA
                                AND NUM_PERSONAL.S_STATUS = 1
                                AND NCON.IID_ALMACEN = AL.IID_ALMACEN
                                AND RF.id_tipo_falta IN (8, 14)
                                AND trunc(RF.D_FEC_INICIO) <= trunc(SYSDATE)
                                AND trunc(RF.D_FEC_FIN) >= trunc(SYSDATE))
                        END AS FALTAS_PERMITIDAS,
                        CASE
                            WHEN RCD.IID_DEPTO = 1 THEN
                            (SELECT COUNT(DISTINCT(PERSCON.IID_EMPLEADO))
                                FROM RH_PERSONAL_CONTRATO PERSCON
                                INNER JOIN NO_PERSONAL NUM_PERSONAL ON PERSCON.IID_RCONTRATO =
                                                                    NUM_PERSONAL.IID_CONTRATO
                                                                AND PERSCON.IID_EMPLEADO =
                                                                    NUM_PERSONAL.IID_EMPLEADO
                                INNER JOIN RH_CAT_AREAS RAREAS ON PERSCON.IID_AREA =
                                                                RAREAS.IID_AREA
                                                            AND PERSCON.IID_DEPTO =
                                                                RAREAS.IID_DEPTO
                                INNER JOIN NO_CONTRATO NCON ON PERSCON.IID_RCONTRATO =
                                                            NCON.IID_CONTRATO
                                                        AND PERSCON.IID_EMPLEADO =
                                                            NCON.IID_EMPLEADO
                                INNER JOIN rh_faltas RF ON NUM_PERSONAL.IID_EMPLEADO =
                                                        RF.IID_EMPLEADO
                                WHERE PERSCON.IID_DEPTO = RCD.IID_DEPTO
                                AND PERSCON.IID_AREA = RA.IID_AREA
                                AND PERSCON.IID_PLAZA = PL.IID_PLAZA
                                AND NUM_PERSONAL.S_STATUS = 1
                                AND NCON.IID_ALMACEN = AL.IID_ALMACEN
                                AND RF.id_tipo_falta IN (6)
                                AND trunc(RF.D_FEC_INICIO) <= trunc(SYSDATE)
                                AND trunc(RF.D_FEC_FIN) >= trunc(SYSDATE))
                            ELSE
                            (SELECT COUNT(DISTINCT(PERSCON.IID_EMPLEADO))
                                FROM RH_PERSONAL_CONTRATO PERSCON
                                INNER JOIN NO_PERSONAL NUM_PERSONAL ON PERSCON.IID_RCONTRATO =
                                                                    NUM_PERSONAL.IID_CONTRATO
                                                                AND PERSCON.IID_EMPLEADO =
                                                                    NUM_PERSONAL.IID_EMPLEADO
                                INNER JOIN RH_CAT_AREAS RAREAS ON PERSCON.IID_AREA =
                                                                RAREAS.IID_AREA
                                                            AND PERSCON.IID_DEPTO =
                                                                RAREAS.IID_DEPTO
                                INNER JOIN NO_CONTRATO NCON ON PERSCON.IID_RCONTRATO =
                                                            NCON.IID_CONTRATO
                                                        AND PERSCON.IID_EMPLEADO =
                                                            NCON.IID_EMPLEADO
                                INNER JOIN rh_faltas RF ON NUM_PERSONAL.IID_EMPLEADO =
                                                        RF.IID_EMPLEADO
                                WHERE PERSCON.IID_DEPTO = RCD.IID_DEPTO
                                AND PERSCON.IID_PLAZA = PL.IID_PLAZA
                                AND NUM_PERSONAL.S_STATUS = 1
                                AND NCON.IID_ALMACEN = AL.IID_ALMACEN
                                AND RF.id_tipo_falta IN (6)
                                AND trunc(RF.D_FEC_INICIO) <= trunc(SYSDATE)
                                AND trunc(RF.D_FEC_FIN) >= trunc(SYSDATE))
                        END AS FALTAS_INJUSTIFICADAS
                    FROM (select *
                            from (SELECT TO_NUMBER(emp_code) AS EMP_CODE,
                                        TO_CHAR(punch_time, 'DD/MM/YYYY HH24:MI:SS') AS formatted_punch_time,
                                        TO_CHAR(punch_time, 'DD/MM/YYYY') AS formatted_punch_time2,
                                        TO_CHAR(punch_time, 'HH24:MI AM') AS HORA
                                    FROM (SELECT emp_code,
                                                punch_time,
                                                ROW_NUMBER() OVER(PARTITION BY emp_code, TRUNC(punch_time) ORDER BY punch_time ASC) as rn
                                            FROM ICLOCK_TRANSACTION@DLRELOJ)
                                    WHERE rn = 1
                                    ORDER BY punch_time DESC) b
                            where b.formatted_punch_time2 = TO_CHAR(SYSDATE, 'DD/MM/YYYY')) A
                            RIGHT JOIN NO_PERSONAL NP ON A.EMP_CODE = NP.IID_EMPLEADO
                    INNER JOIN NO_CONTRATO NC ON NP.IID_EMPLEADO = NC.IID_EMPLEADO
                                                    AND NP.IID_CONTRATO = NC.IID_CONTRATO
                    INNER JOIN RH_PUESTOS RP ON NC.Iid_Puesto = RP.IID_PUESTO
                    LEFT JOIN RH_CAT_AREAS RA ON NC.IID_AREA = RA.IID_AREA
                                            AND RA.IID_DEPTO = NC.IID_DEPTO
                    INNER JOIN PLAZA PL ON NP.IID_PLAZA = PL.IID_PLAZA
                    INNER JOIN RH_CAT_DEPTO RCD ON RCD.IID_DEPTO = NC.IID_DEPTO
                    LEFT JOIN RH_ALMACEN_EMP ALMEMP ON ALMEMP.NID_EMPLEADO =
                                                        NC.IID_EMPLEADO
                                                    AND ALMEMP.N_BASE = 1
                    LEFT JOIN ALMACEN AL ON ALMEMP.NID_ALMACEN = AL.IID_ALMACEN
                                    WHERE NP.S_STATUS = 1
                                        AND NP.IID_PLAZA = $iid_plaza
                                        AND NC.IID_EMPLEADO NOT IN (209, 2400, 1025)
                                    ORDER BY NP.IID_PLAZA, ALMEMP.NID_ALMACEN, RCD.IID_DEPTO, RA.IID_AREA, RA.IID_DEPTO, NC.IID_PUESTO, NC.IID_EMPLEADO";

               # echo $sql;
                $stid = oci_parse($conn, $sql);
                oci_execute($stid);

                while (($row = oci_fetch_assoc($stid)) != false)
                {
                    $res_array[]= $row;
                }

                oci_free_statement($stid);
                oci_close($conn);

                // Devolver los datos en formato JSON
                header('Content-Type: application/json');
                echo json_encode($res_array);
                exit;
    }

    public function obtenerReporteGeneral(){
        #Conexion
        $conn = conexion::conectar();
        $res_array = array();
        $and_plaza = "";

        $entra_todos_1 =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.1);
        $entra_todos_2 =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70);
        $entra_todos_corp =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.2);
        $entra_todos_cord =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.3);
        $entra_todos_mex =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.4);
        $entra_todos_gol =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.5);
        $entra_todos_pen =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.6);
        $entra_todos_pue =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.7);
        $entra_todos_baj =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.8);
        $entra_todos_occ =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.17);
        $entra_todos_nor =  Perfil::modulos_valida($_SESSION['iid_empleado'], 70.18);
   
            if ($entra_todos_1 == '1' && $entra_todos_2 === '1') {
                $in_plaza = " ";
                
            }else {
                if ($entra_todos_corp == '1') {
                    $and_plaza = $and_plaza.", 2 ";
                } 
                if ($entra_todos_cord === '1') {
                    $and_plaza = $and_plaza.", 3 ";
                }
                if ($entra_todos_mex === '1') {
                    $and_plaza = $and_plaza.", 4 ";
                } 
                if ($entra_todos_gol === '1') {
                    $and_plaza = $and_plaza.", 5 ";
                }
                if ($entra_todos_pen === '1') {
                    $and_plaza = $and_plaza.", 6 ";
                }
                if ($entra_todos_pue === '1') {
                    $and_plaza = $and_plaza.", 7 ";
                }
                if ($entra_todos_baj === '1') {
                    $and_plaza = $and_plaza.", 8 ";
                }
                if ($entra_todos_occ === '1') {
                    $and_plaza = $and_plaza.", 17 ";
                }
                if ($entra_todos_nor === '1') {
                    $and_plaza = $and_plaza.", 18 ";
                }

                $andPlazaIn = ltrim($and_plaza, ',');

                $in_plaza = " AND PL.IID_PLAZA IN ($andPlazaIn) ";
            }    


        #echo $in_plaza."valor de plaza";
        $sql = "SELECT PL.V_RAZON_SOCIAL,
        PL.IID_PLAZA,
        COUNT(CASE WHEN NP.IID_NUMNOMINA = 1 OR NP.IID_NUMNOMINA IS NULL THEN 1 END) AS QUINCENAL,
        COUNT(CASE WHEN NP.IID_NUMNOMINA = 2 THEN 1 END) AS SEMANAL,
        COUNT(NP.IID_EMPLEADO) AS PERSONAL_ACTIVO,    
        (SELECT COUNT(*) FROM PERSONNEL_EMPLOYEE@DLRELOJ W
        INNER JOIN NO_PERSONAL NUM ON W.EMP_CODE = NUM.IID_EMPLEADO
        INNER JOIN RH_PERSONAL_CONTRATO RCUN ON RCUN.IID_EMPLEADO = NUM.IID_EMPLEADO AND RCUN.IID_RCONTRATO = NUM.IID_CONTRATO
        INNER JOIN RH_CAT_AREAS RCAN ON RCUN.IID_AREA = RCAN.IID_AREA AND RCUN.IID_DEPTO = RCAN.IID_DEPTO
        INNER JOIN NO_CONTRATO NOMC ON NOMC.IID_CONTRATO = NUM.IID_CONTRATO AND NOMC.IID_EMPLEADO = NUM.IID_EMPLEADO
        WHERE NUM.S_STATUS = 1 AND NUM.IID_PLAZA = PL.IID_PLAZA 
        AND NUM.IID_EMPLEADO  NOT IN (209, 1025) )    AS PERSONAL_REGISTRADO,                                
        (SELECT COUNT(*)
                        FROM RH_FALTAS R
                        INNER JOIN NO_PERSONAL NUM ON R.IID_EMPLEADO = NUM.IID_EMPLEADO
                        INNER JOIN RH_FALTAS_CAT RC ON R.ID_TIPO_FALTA = RC.ID_TIPO_FALTA
                        INNER JOIN NO_CONTRATO RCUN ON RCUN.IID_EMPLEADO = NUM.IID_EMPLEADO AND RCUN.IID_CONTRATO = NUM.IID_CONTRATO
                        INNER JOIN RH_CAT_AREAS RCAN ON RCUN.IID_AREA = RCAN.IID_AREA AND RCUN.IID_DEPTO = RCAN.IID_DEPTO
                        WHERE trunc(R.D_FEC_INICIO) <= trunc(SYSDATE)
                            AND trunc(R.D_FEC_FIN) >= trunc(SYSDATE)
                            AND NUM.IID_EMPLEADO  NOT IN (209, 1025) 
                            AND NUM.S_STATUS = 1 AND NUM.IID_PLAZA = PL.IID_PLAZA AND R.ID_TIPO_FALTA IN (3, 4, 5, 12)) AS FALTA_INCAPACIDAD, 
        COUNT(CASE WHEN AL.IID_ALMACEN IN (1468, 1562, 43, 1706, 1770, 1773, 1136, 1705, 1554, 1775, 1755, 1387) THEN 1 END) AS FALTA_HABILITADO,   
        (SELECT COUNT(*)
                        FROM RH_FALTAS R
                        INNER JOIN NO_PERSONAL NUM ON R.IID_EMPLEADO = NUM.IID_EMPLEADO
                        INNER JOIN RH_FALTAS_CAT RC ON R.ID_TIPO_FALTA = RC.ID_TIPO_FALTA
                        INNER JOIN NO_CONTRATO RCUN ON RCUN.IID_EMPLEADO = NUM.IID_EMPLEADO AND RCUN.IID_CONTRATO = NUM.IID_CONTRATO
                        INNER JOIN RH_CAT_AREAS RCAN ON RCUN.IID_AREA = RCAN.IID_AREA AND RCUN.IID_DEPTO = RCAN.IID_DEPTO
                        WHERE trunc(R.D_FEC_INICIO) <= trunc(SYSDATE)
                            AND trunc(R.D_FEC_FIN) >= trunc(SYSDATE)
                            AND NUM.IID_EMPLEADO  NOT IN (209, 1025) 
                            AND NUM.S_STATUS = 1 AND NUM.IID_PLAZA = PL.IID_PLAZA AND R.ID_TIPO_FALTA IN (8)) AS FALTA_COMISION_PLAZAS, 
        (SELECT COUNT(*)
                        FROM RH_FALTAS R
                        INNER JOIN NO_PERSONAL NUM ON R.IID_EMPLEADO = NUM.IID_EMPLEADO
                        INNER JOIN RH_FALTAS_CAT RC ON R.ID_TIPO_FALTA = RC.ID_TIPO_FALTA
                        INNER JOIN NO_CONTRATO RCUN ON RCUN.IID_EMPLEADO = NUM.IID_EMPLEADO AND RCUN.IID_CONTRATO = NUM.IID_CONTRATO
                        INNER JOIN RH_CAT_AREAS RCAN ON RCUN.IID_AREA = RCAN.IID_AREA AND RCUN.IID_DEPTO = RCAN.IID_DEPTO
                        WHERE trunc(R.D_FEC_INICIO) <= trunc(SYSDATE)
                            AND trunc(R.D_FEC_FIN) >= trunc(SYSDATE)
                            AND NUM.IID_EMPLEADO  NOT IN (209, 1025) 
                            AND NUM.S_STATUS = 1 AND NUM.IID_PLAZA = PL.IID_PLAZA AND R.ID_TIPO_FALTA IN (6)) AS FALTA_INJUSTIFICADA, 
        (SELECT COUNT(*)
                        FROM RH_FALTAS R
                        INNER JOIN NO_PERSONAL NUM ON R.IID_EMPLEADO = NUM.IID_EMPLEADO
                        INNER JOIN RH_FALTAS_CAT RC ON R.ID_TIPO_FALTA = RC.ID_TIPO_FALTA
                        INNER JOIN NO_CONTRATO RCUN ON RCUN.IID_EMPLEADO = NUM.IID_EMPLEADO AND RCUN.IID_CONTRATO = NUM.IID_CONTRATO
                        INNER JOIN RH_CAT_AREAS RCAN ON RCUN.IID_AREA = RCAN.IID_AREA AND RCUN.IID_DEPTO = RCAN.IID_DEPTO
                        WHERE trunc(R.D_FEC_INICIO) <= trunc(SYSDATE)
                            AND trunc(R.D_FEC_FIN) >= trunc(SYSDATE)
                             AND NUM.IID_EMPLEADO  NOT IN (209, 1025) 
                            AND NUM.S_STATUS = 1 AND NUM.IID_PLAZA = PL.IID_PLAZA AND R.ID_TIPO_FALTA IN (14)) AS HOME_OFFICE,  
        (SELECT COUNT(*)
                        FROM RH_FALTAS R
                        INNER JOIN NO_PERSONAL NUM ON R.IID_EMPLEADO = NUM.IID_EMPLEADO
                        INNER JOIN RH_FALTAS_CAT RC ON R.ID_TIPO_FALTA = RC.ID_TIPO_FALTA
                        INNER JOIN NO_CONTRATO RCUN ON RCUN.IID_EMPLEADO = NUM.IID_EMPLEADO AND RCUN.IID_CONTRATO = NUM.IID_CONTRATO
                        INNER JOIN RH_CAT_AREAS RCAN ON RCUN.IID_AREA = RCAN.IID_AREA AND RCUN.IID_DEPTO = RCAN.IID_DEPTO
                        WHERE trunc(R.D_FEC_INICIO) <= trunc(SYSDATE)
                            AND trunc(R.D_FEC_FIN) >= trunc(SYSDATE)
                             AND NUM.IID_EMPLEADO  NOT IN (209, 1025) 
                            AND NUM.S_STATUS = 1 AND NUM.IID_PLAZA = PL.IID_PLAZA AND R.ID_TIPO_FALTA IN (9)) AS VACACIONES,     
        COUNT(A.formatted_punch_time) AS ASISTENCIA,
        (SELECT COUNT(*) FROM RH_SOL_RECLUTA_SEL RSEL WHERE RSEL.IID_PLAZA_ASIGNA = PL.IID_PLAZA AND RSEL.N_STATUS <= 2 AND (RSEL.N_STANDBYE <> 1 OR RSEL.N_STANDBYE IS NULL) ) AS VACANTES,
        (SELECT COUNT(*) FROM RH_SOL_RECLUTA_SEL RSEL WHERE RSEL.IID_PLAZA_ASIGNA = PL.IID_PLAZA AND RSEL.N_STATUS <= 2 AND RSEL.N_STANDBYE = 1 ) AS VACANTES_STANDBY,
        (SELECT COUNT(*)
          FROM (SELECT NP.*, TO_NUMBER(B.EMP_CODE)
                  FROM NO_PERSONAL NP
                  LEFT JOIN ICLOCK_TRANSACTION@DLRELOJ B ON NP.IID_EMPLEADO =
                                                            B.EMP_CODE
                                                        AND TO_CHAR(punch_time, 'DD/MM/YYYY') = TO_CHAR(SYSDATE, 'DD/MM/YYYY')
                                                        INNER JOIN RH_ALMACEN_EMP REMP ON REMP.NID_EMPLEADO = NP.IID_EMPLEADO AND REMP.N_BASE = 1 AND REMP.I_CHECA_RELOJ <> 1 AND REMP.NID_ALMACEN NOT IN (1468, 1562, 43, 1706, 1770, 1773, 1136, 1705, 1554, 1775, 1755, 1387)
                 LEFT JOIN RH_FALTAS RF ON RF.IID_EMPLEADO = NP.IID_EMPLEADO AND RF.ID_TIPO_FALTA <> 6 AND (trunc(RF.d_fec_inicio) <= trunc(SYSDATE) AND trunc(RF.d_fec_fin) >= TRUNC(SYSDATE))
                 WHERE NP.S_STATUS = 1
                    AND NP.IID_EMPLEADO  NOT IN (209, 1025) 
                   AND B.EMP_CODE IS NULL
                   AND NP.IID_PLAZA = PL.IID_PLAZA
                   AND NP.IID_NUMNOMINA IS NOT NULL
                   AND RF.IID_EMPLEADO IS NULL 
                UNION ALL
                SELECT NP.*, NP.IID_EMPLEADO
                  FROM NO_PERSONAL NP
                 INNER JOIN RH_FALTAS RF ON RF.IID_EMPLEADO = NP.IID_EMPLEADO
                 WHERE RF.ID_TIPO_FALTA = 6
                    AND NP.IID_EMPLEADO  NOT IN (209, 1025) 
                    AND NP.IID_NUMNOMINA IS NOT NULL
                   AND NP.IID_PLAZA = PL.IID_PLAZA
                   AND (trunc(RF.d_fec_inicio) <= trunc(SYSDATE) AND
                        trunc(RF.d_fec_fin) >= TRUNC(SYSDATE))) X) AS FALTAS_SIN_JUSTIFICACION 
FROM (select *
                        from (SELECT TO_NUMBER(emp_code) AS EMP_CODE,
                                        TO_CHAR(punch_time, 'DD/MM/YYYY HH24:MI:SS') AS formatted_punch_time,
                                        TO_CHAR(punch_time, 'DD/MM/YYYY') AS formatted_punch_time2
                                FROM (SELECT emp_code,
                                                punch_time,
                                                ROW_NUMBER() OVER(PARTITION BY emp_code, TRUNC(punch_time) ORDER BY punch_time ASC) as rn
                                        FROM ICLOCK_TRANSACTION@DLRELOJ)
                                WHERE rn = 1
                                ORDER BY punch_time DESC) b
                        where b.formatted_punch_time2 = TO_CHAR(SYSDATE, 'DD/MM/YYYY')) A
                RIGHT JOIN NO_PERSONAL NP ON A.EMP_CODE = NP.IID_EMPLEADO
                INNER JOIN NO_CONTRATO NOMC ON NOMC.IID_CONTRATO = NP.IID_CONTRATO AND NOMC.IID_EMPLEADO = NP.IID_EMPLEADO
                INNER JOIN RH_PUESTOS RP ON NOMC.IID_PUESTO = RP.IID_PUESTO
                INNER JOIN RH_CAT_AREAS RA ON NOMC.IID_AREA = RA.IID_AREA
                                            AND RA.IID_DEPTO = NOMC.IID_DEPTO
                INNER JOIN PLAZA PL ON NP.IID_PLAZA = PL.IID_PLAZA
                INNER JOIN RH_CAT_DEPTO RCD ON RCD.IID_DEPTO = RA.IID_DEPTO                                        
                LEFT  JOIN RH_ALMACEN_EMP ALMEMP ON ALMEMP.NID_EMPLEADO = NOMC.IID_EMPLEADO AND ALMEMP.N_BASE =1 
                LEFT JOIN ALMACEN AL ON ALMEMP.NID_ALMACEN = AL.IID_ALMACEN
WHERE NP.S_STATUS = 1  AND NP.IID_EMPLEADO  NOT IN (209, 1025)  ".$in_plaza."
GROUP BY PL.V_RAZON_SOCIAL, PL.IID_PLAZA        
ORDER BY PL.IID_PLAZA";

            #echo $sql;
            $stid = oci_parse($conn, $sql);
            oci_execute($stid);
            while (($row = oci_fetch_assoc($stid))!= false) {
                $res_array[]=$row;
            }

            oci_free_statement($stid);
            oci_close($conn);

            // Devolver los datos en formato JSON
            header('Content-Type: application/json');
            echo json_encode($res_array);
            exit;
    }

    public function obtenerVacantes($iid_plaza){
        $conn = conexion::conectar();
        $res_array = array();

        $consulta = "SELECT A.IID_SOLICITUD, RP.V_DESCRIPCION, A.N_STATUS, to_char(A.D_FEC_AUT_DO_DCAF, 'dd/mm/yyyy') as D_FEC_AUT_DG, A.N_STANDBYE, A.V_JUSTIFICACION,
                            A.N_V_OCUPADA
         FROM RH_SOL_RECLUTA_SEL A 
                            INNER JOIN RH_PUESTOS RP ON RP.IID_PUESTO = A.IID_PUESTO 
                     WHERE A.N_STATUS <= 2 AND A.IID_PLAZA_ASIGNA = $iid_plaza
                     ORDER BY A.IID_PUESTO";

        $stid = oci_parse($conn, $consulta);
        oci_execute($stid);
        while (($row = oci_fetch_assoc($stid))!= false) {
            $res_array[]=$row;
        }

        oci_free_statement($stid);
        oci_close($conn);

        // Devolver los datos en formato JSON
        header('Content-Type: application/json');
        echo json_encode($res_array);
        exit;
    }
}
?>