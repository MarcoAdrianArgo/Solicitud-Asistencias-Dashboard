<?php
//BY JTJ 28/12/2018
error_reporting(0);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header("location:asistenciaPersonal.php");
    //return;
}

session_start();
//comprobar sesion iniciada
if (!isset($_SESSION['usuario']))
    header('Location: ../index.php');
//comprobar tiempo de expiracion
$now = time();
if ($now > $_SESSION['expira']) {
    session_destroy();
    header('Location: ../index.php');
}
//objeto conexion a base de datos
include_once '../libs/conOra.php';
$conn   = conexion::conectar();

//objeto clase de la vista 
include_once '../class/AsistenciaPersonal.php';
$obj_class = new AsistenciaPersonal();
//Obtener Plazas
$plazas = $obj_class->plazasActivas();


//////////////////////////// INICIO DE AUTOLOAD
function autoload($clase)
{
    include "../class/" . $clase . ".php";
}
spl_autoload_register('autoload');
//////////////////////////// VALIDACION DEL MODULO ASIGNADO
$modulos_valida = Perfil::modulos_valida($_SESSION['iid_empleado'], 70);
if ($modulos_valida == 0) {
    header('Location: index.php');
}
?>
<!-- ####################################### Incluir Plantilla Principal ##########################-->
<?php include_once('../layouts/plantilla.php'); ?>
<!-- ########################################## Incia Contenido de la pagina ########################################## -->
<!-- DataTables -->
<link rel="stylesheet" href="../plugins/datatables/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="../plugins/datatables/extensions/buttons_datatable/buttons.dataTables.min.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

<!--leaflet js maps-->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />

<!-- MIS PRUEBAS -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowgroup/1.4.1/css/rowGroup.dataTables.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
<style>
    /* Cambiar color de fondo al pasar el ratón */
    .custom-btn:hover {
        filter: brightness(90%);
    }

    /* Estilos para el contenedor de datos */
    #datos-container {
        margin-top: 5px;
    }

    /* Añadir margen entre botones */
    .ui.buttons .button {
        margin: 2px;
    }

    /* Cambiar colores para cada fila */
    .row-1 {
        background-color: #3498db !important;
        color: white;
    }

    .row-2 {
        background-color: #3498db !important;
        color: white;
    }

    .row-3 {
        background-color: #3498db !important;
        color: white;
    }

    .row-active {
        background-color: yellow !important;
        color: white;
    }

    /* Añadir margen al contenedor de botones */
    .button-container {
        margin-bottom: 10px;
        /* Margen inferior */
    }

    .grupo-amarillo {
        background-color: yellow !important;
        /* Usa !important para asegurar que sobrescriba otros estilos */
        text-align: center;
    }

    .hidden {
        display: none;
    }

    #miTablaGeneral th,
    #miTablaGeneral td {
        text-align: center;
        /* Alinea el contenido al centro */
    }

    /* Cambia el color de fondo de la segunda columna (índice 1) */
    table.dataTable tbody td:nth-child(9) {
        background-color: grey;
        font-weight: bold;
        color: white;
        /* Cambia a tu color deseado */
    }

    

    .selected {
        background-color: #A2D3F6 !important;
        /* Cambia el color según tus preferencias */
    }

    /* Estilo para ocultar la columna con índice 0 */
    .hide-column-0 .dt-column-0 {
        display: none;
    }

    /* Estilo para mostrar la columna con índice 0 */
    .show-column-0 .dt-column-0 {
        display: table-cell;
    }

    /* Alinear el contenido del footer en el centro de las celdas */
    .dataTables tfoot th,
    .dataTables tfoot td {
        text-align: center;
    }

    /* Define la clase de rotación inicial */
    .initial-spin {
        animation: spin 5s linear infinite;
    }


    /* Define la animación de rotación */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }


    /* Agrega estos estilos a tu hoja de estilos CSS */
    #porcentajeWidget {
        transition: transform 0.5s ease-in-out; /* Ajusta la duración y el efecto según tus preferencias */
    }

    #porcentajeWidget.active {
        background-color: yellow !important; /* Cambia el color cuando está activo */
        transform: rotateX(360deg); /* Rota el widget 360 grados al pasar el mouse */
        color: #000; /* Cambia el color del texto a negro */
    }

    #porcentajeWidget.active .info-box-icon,
    #porcentajeWidget.active .info-box-text,
    #porcentajeWidget.active .info-box-footer,
    #porcentajeWidget.active .info-box-number {
        color: #000 !important; /* Cambia el color del ícono y del texto cuando está activo */
    }

    /* Agrega estos estilos a tu hoja de estilos CSS */
    #widgetsVacantesDisponibles {
        transition: transform 0.5s ease-in-out; /* Ajusta la duración y el efecto según tus preferencias */
    }

    #widgetsVacantesDisponibles.active {
        background-color: #B2E57C !important; /* Cambia el color cuando está activo */
        transform: rotateX(360deg); /* Rota el widget 360 grados al pasar el mouse */
        color: #000; /* Cambia el color del texto a negro */
    }

    #widgetsVacantesDisponibles.active .info-box-icon,
    #widgetsVacantesDisponibles.active .info-box-text,
    #widgetsVacantesDisponibles.active .info-box-footer,
    #widgetsVacantesDisponibles.active .info-box-number {
        color: #000 !important; /* Cambia el color del ícono y del texto cuando está activo */
    }
    

    /* Agrega estos estilos a tu hoja de estilos CSS */
    #widgetsVacantesDisponiblesStandby {
        transition: transform 0.5s ease-in-out; /* Ajusta la duración y el efecto según tus preferencias */
    }

    #widgetsVacantesDisponiblesStandby.active {
        background-color: #FFD699  !important; /* Cambia el color cuando está activo */
        transform: rotateX(360deg); /* Rota el widget 360 grados al pasar el mouse */
        color: #000; /* Cambia el color del texto a negro */
    }

    #widgetsVacantesDisponiblesStandby.active .info-box-icon,
    #widgetsVacantesDisponiblesStandby.active .info-box-text,
    #widgetsVacantesDisponiblesStandby.active .info-box-footer,
    #widgetsVacantesDisponiblesStandby.active .info-box-number {
        color: #000 !important; /* Cambia el color del ícono y del texto cuando está activo */
    }
    /* Agrega estos estilos a tu hoja de estilos CSS */
    #widgetsVacantesActivos {
        transition: transform 0.5s ease-in-out; /* Ajusta la duración y el efecto según tus preferencias */
    }

    #widgetsVacantesActivos.active {
        background-color: yellow !important; /* Cambia el color cuando está activo */
        transform: rotateX(360deg); /* Rota el widget 360 grados al pasar el mouse */
        color: #000; /* Cambia el color del texto a negro */
    }

    #widgetsVacantesActivos.active .info-box-icon,
    #widgetsVacantesActivos.active .info-box-text,
    #widgetsVacantesActivos.active .info-box-footer,
    #widgetsVacantesActivos.active .info-box-number {
        color: #000 !important; /* Cambia el color del ícono y del texto cuando está activo */
    }

    #miTabla td{
        background-color: white;
        color: #000;
    }

</style>
<!-- Make sure you put this AFTER Leaflet's CSS -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
<div class="content-wrapper"><!-- Inicia etiqueta content-wrapper principal -->
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
            <small>Asistencia De Personal</small>
        </h1>
    </section>
    <!-- Main content -->
    <!-- ############################ ./SECCION GRAFICA Y WIDGETS ############################# -->
    <section class="content">

<!--  filtros         -->
     <div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-filter"></i> Filtros de Fecha</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo de Filtro:</label>
                            <select id="tipoFiltro" class="form-control">
                                <option value="dia">Por Día</option>
                                <option value="semana">Por Semana</option>
                                <option value="mes">Por Mes</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3" id="filtroDia">
                        <div class="form-group">
                            <label>Seleccionar Día:</label>
                            <input type="date" id="fechaDia" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3" id="filtroSemana" style="display: none;">
                        <div class="form-group">
                            <label>Seleccionar Semana:</label>
                            <input type="week" id="fechaSemana" class="form-control" value="<?php echo date('Y-\WW'); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3" id="filtroMes" style="display: none;">
                        <div class="form-group">
                            <label>Seleccionar Mes:</label>
                            <input type="month" id="fechaMes" class="form-control" value="<?php echo date('Y-m'); ?>">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button id="btnAplicarFiltro" class="btn btn-primary btn-block">
                                <i class="fa fa-filter"></i> Aplicar Filtro
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!--  Fin filtros -->

        <div id="loaderTabla" style="display: none;"></div>

        <div class="row">

            <div class="col-md-3">
                <div class="info-box bg-blue" id="porcentajeWidget">
                    <span class="info-box-icon"><i class="fa fa-percent" id="textoPorcentajeWid"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="tituloPorcentaje">Porcentaje Asistencia</span>
                        <span class="info-box-number" id="porcentajeNumero">0</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <a href="#asistenciaPersonal" style="text-decoration: none; text-align:center;">
                        <div class="info-box-footer" style="color: white; font-weight: bold; margin: 10px;">
                            Ver <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box bg-green" id="widgetsVacantesDisponibles">
                    <span class="info-box-icon"><i class="fa fa-hashtag" id="textoVacantesWid"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="tituloVacantes">Vacantes Autorizadas</span>
                        <span class="info-box-number" id="cantidadVacantes">0</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <a href="#vacantesPersonal" style="text-decoration: none; text-align:center;">
                        <div class="info-box-footer" style="color: white; font-weight: bold; margin: 10px;">
                            Ver <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box bg-yellow" id="widgetsVacantesDisponiblesStandby">
                    <span class="info-box-icon"><i class="fa fa-hashtag" id="textoVacantesWidStandby"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="tituloVacantesStandby">Vacantes Stand By</span>
                        <span class="info-box-number" id="cantidadVacantesStandby">0</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <a href="#vacantesPersonal" style="text-decoration: none; text-align:center;">
                        <div class="info-box-footer" style="color: white; font-weight: bold; margin: 10px;">
                            Ver <i class="fa fa-arrow-right"></i>
                        </div>
                    </a>
                </div>
            </div>

            <div class="col-md-3">
                <div class="info-box bg-blue" id="widgetsVacantesActivos">
                    <span class="info-box-icon"><i class="fa fa-users" id="textoUsuarios"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text" id="tituloPersonal">Personal Activo</span>
                        <span class="info-box-number" id="cantidadActivos">0</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Reporte General De Plazas -->
        <!--TABLA -->
        <div class="table-responsive" id="contenidoReporteGral">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle"></i> Reporte General</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <table id="miTablaGeneral" class="display table table-bordered table-hover table-striped" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">IID_PLAZA
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">PLAZA
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">PERSONAL QUINCENAL ACTIVO
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">PERSONAL SEMANAL ACTIVO
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">TOTAL PERSONAL
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">VACANTES DISPONIBLES
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">VACANTES STAND BY
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">INCAPACIDAD
                                </th>
                                <!--<th class="small" bgcolor="#0073B7">
                                    <font color="white">PERSONAL EN MODULO
                                </th>-->
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">COMISION EN PLAZAS
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">PERSONAL QUE NO SE PRESENTO
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">HOME OFFICE
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">VACACIONES
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">PERSONAL ASISTIO
                                </th>
                                <!--<th class="small" bgcolor="#0073B7">
                                    <font color="white">PERSONAL ASISTENCIA
                                </th>-->
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">% DE ASISTENCIA
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot style="text-align: center;">
                            <tr>
                                <th>0</th>
                                <th>TOTALES</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <!--<th>0</th>-->
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <th>0</th>
                                <!--<th>0</th>-->
                                <th>0</th>

                            </tr>
                        </tfoot>

                    </table>
                </div>
            </div>
        </div>


        <!--TABLA -->
        <div class="table-responsive" id="asistenciaPersonal">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle" id="asistenciaPlazasTabla"></i> </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <table id="miTabla" class="display table table-bordered table-hover table-striped" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">IID EMPLEADO
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">NOMBRE
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">AREA
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">PUESTO
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">PLAZA
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">ALMACEN
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">ASISTENCIA
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">ULTIMO REGISTRO
                                </th>
                                
                                <!--<th class="small" bgcolor="#0073B7"><font color="white">HORA DE LLEGADA</th>-->
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">MOTIVO DE FALTA
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">FECHA
                                </th>


                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--TABLA VACANTES -->
        <div class="table-responsive" id="vacantesPersonal">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-info-circle" id="vacantesPlazaTabla"></i> </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <table id="miTabla2" class="display table table-bordered table-hover table-striped" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">IID SOLICITUD
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">PUESTO
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">ESTATUS
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">JUSTIFICACIÓN
                                </th>
                                <th class="small" bgcolor="#0073B7">
                                    <font color="white">FECHA AUTORIZACION
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </section>



    <!-- /.content -->
</div><!-- Termina etiqueta content-wrapper principal -->
<!-- ################################### Termina Contenido de la pagina ################################### -->
<?php include_once('../layouts/footer.php'); ?>



<script>
    var textoPorcentajeWid = document.getElementById('porcentajeWidget');
    var porcentajeNum = document.getElementById('porcentajeNumero');
    var tituloPorcentaje = document.getElementById('tituloPorcentaje');
    var contadorClics = 0;
    var fechaInicioStr = null;
    var fechaFinStr = null;



    textoPorcentajeWid.addEventListener('click', function() {
        contadorClics++;

        // Ejemplo de cómo obtener los valores desde fuera del DataTable
        var valoresFilaSeleccionada = obtenerUltimaFilaSeleccionada();

        // Cambia el texto del título según el estado del contador
        tituloPorcentaje.textContent = contadorClics % 2 === 0 ? 'Porcentaje Asistencia' : 'Porcentaje Asistencia Plaza';

        //
        var tott = ((parseInt(valoresFilaSeleccionada.ASISTENCIA) + parseInt(valoresFilaSeleccionada.FALTA_HABILITADO)) / (parseInt(valoresFilaSeleccionada.SEMANAL) + parseInt(valoresFilaSeleccionada.QUINCENAL)))*100;
        //var tott = (parseInt(valoresFilaSeleccionada.ASISTENCIA))*100;
        var tot_Gral = obtenerCookie('porcentajeTotal')
        // Muestra el valor de VACANTE de la fila seleccionada si existe, de lo contrario, muestra el valor inicial
        porcentajeNum.innerText = contadorClics % 2 === 0
            ? obtenerCookie('porcentajeTotal')
            : valoresFilaSeleccionada ? tott.toFixed(2)  : tot_Gral.toFixed(2);

        // Agrega o elimina las clases 'active' y 'active-yellow' para cambiar el color
        this.classList.toggle('active', contadorClics % 2 !== 0);
        this.classList.toggle('active-yellow', contadorClics % 2 === 0);
    });



    var widgetsVacantesDisponibles = document.getElementById('widgetsVacantesDisponibles');
    var vacantesNum = document.getElementById("cantidadVacantes");
    var tituloVacantes = document.getElementById('tituloVacantes');
    var contadorClicsVacantes = 0;

    widgetsVacantesDisponibles.addEventListener('click', function() {
        contadorClicsVacantes++;

        // Ejemplo de cómo obtener los valores desde fuera del DataTable
        var valoresFilaSeleccionada = obtenerUltimaFilaSeleccionada();

        // Cambia el texto del título según el estado del contador
        tituloVacantes.textContent = contadorClicsVacantes % 2 === 0 ? 'Vacantes Autorizados' : 'Vacantes Autorizados Por Plaza';

        // Muestra el valor de VACANTE de la fila seleccionada si existe, de lo contrario, muestra el valor inicial
        vacantesNum.innerText = contadorClicsVacantes % 2 === 0
            ? obtenerCookie('vacantes')
            : valoresFilaSeleccionada ? valoresFilaSeleccionada.VACANTES : obtenerCookie('vacantes');

        // Agrega o elimina las clases 'active' y 'active-yellow' para cambiar el color
        this.classList.toggle('active', contadorClicsVacantes % 2 !== 0);
        this.classList.toggle('active-green', contadorClicsVacantes % 2 === 0);
    });

    var widgetsVacantesDisponiblesStandBy = document.getElementById('widgetsVacantesDisponiblesStandby');
    var vacantesStandByNum = document.getElementById("cantidadVacantesStandby");
    var tituloVacantesStandby = document.getElementById('tituloVacantesStandby');
    var contadorClicsVacantesStandby = 0;

    widgetsVacantesDisponiblesStandBy.addEventListener('click', function() {
        contadorClicsVacantesStandby++;

        // Ejemplo de cómo obtener los valores desde fuera del DataTable
        var valoresFilaSeleccionada = obtenerUltimaFilaSeleccionada();

        // Cambia el texto del título según el estado del contador
        tituloVacantesStandby.textContent = contadorClicsVacantesStandby % 2 === 0 ? 'Vacantes Stand By' : 'Vacantes Stand By Por Plaza';

        // Muestra el valor de VACANTE de la fila seleccionada si existe, de lo contrario, muestra el valor inicial
        vacantesStandByNum.innerText = contadorClicsVacantesStandby % 2 === 0
            ? obtenerCookie('vacantes_standby')
            : valoresFilaSeleccionada ? valoresFilaSeleccionada.VACANTES_STANDBY : obtenerCookie('vacantes_standby');

        // Agrega o elimina las clases 'active' y 'active-yellow' para cambiar el color
        this.classList.toggle('active', contadorClicsVacantesStandby % 2 !== 0);
        this.classList.toggle('active-green', contadorClicsVacantesStandby % 2 === 0);
    });

    var widgetsVacantesActivos = document.getElementById('widgetsVacantesActivos');
    var activosNum = document.getElementById("cantidadActivos");
    var tituloPersonal = document.getElementById('tituloPersonal');
    var contadorClicsActivos = 0;

    widgetsVacantesActivos.addEventListener('click', function() {
        contadorClicsActivos++;

        // Ejemplo de cómo obtener los valores desde fuera del DataTable
        var valoresFilaSeleccionada = obtenerUltimaFilaSeleccionada();

        // Cambia el texto del título según el estado del contador
        tituloPersonal.textContent = contadorClicsActivos % 2 === 0 ? 'PERSONAL ACTIVO' : 'PERSONAL ACTIVO POR PLAZA';

        // Muestra el valor de PERSONAL_ACTIVO de la fila seleccionada si existe, de lo contrario, muestra el valor inicial
        activosNum.innerText = contadorClicsActivos % 2 === 0
            ? obtenerCookie('total')
            : valoresFilaSeleccionada ? valoresFilaSeleccionada.PERSONAL_ACTIVO : obtenerCookie('total');

        // Agrega o elimina las clases 'active' y 'active-yellow' para cambiar el color
        this.classList.toggle('active', contadorClicsActivos % 2 !== 0);
        this.classList.toggle('active-yellow', contadorClicsActivos % 2 === 0);
    });

</script>

<!-- script para filtros -->

<script>
   
    var diaSeleccionado = '<?php echo date('Y-m-d'); ?>';
    
    // Inicializar el filtro de fecha con el día de hoy
    $(document).ready(function() {
        // Permitir cambiar entre opciones (NO deshabilitar)
        $('#tipoFiltro').prop('disabled', false);
        
        // Establecer valor inicial
        $('#tipoFiltro').val('dia');
        $('#filtroSemana').hide();
        $('#filtroMes').hide();
        $('#filtroDia').show();
        
        // Establecer la fecha de hoy por defecto
        $('#fechaDia').val('<?php echo date("Y-m-d"); ?>');
        
        // Guardar el día inicial en la variable
        diaSeleccionadoFiltro = $('#fechaDia').val();
        console.log('Día inicial:', diaSeleccionadoFiltro);
        
        // Agregar evento al cambio del tipo de filtro (SOLO VISUAL)
        $('#tipoFiltro').change(function() {
            var tipo = $(this).val();
            console.log('Cambiando a filtro:', tipo);
            
            // Ocultar todos los filtros
            $('#filtroDia').hide();
            $('#filtroSemana').hide();
            $('#filtroMes').hide();
            
            // Mostrar solo el filtro correspondiente
            switch(tipo) {
                case 'dia':
                    $('#filtroDia').show();
                    break;
                case 'semana':
                    $('#filtroSemana').show();
                    break;
                case 'mes':
                    $('#filtroMes').show();
                    break;
            }
        });
    });


    $('#btnAplicarFiltro').click(function() {
        var tipoFiltro = $('#tipoFiltro').val();
        
        if (tipoFiltro === 'dia' ) {
            // FUNCIONALIDAD ORIGINAL PARA DÍA
            var fechaSeleccionada = $('#fechaDia').val();
            console.log('Filtro aplicado para día:', fechaSeleccionada);
            fechaInicioStr = null;
            fechaFinStr = null;
            // Actualizar título de las tablas para mostrar la fecha
            var nombrePlaza = obtenerCookie('v_razon_social');
            if (nombrePlaza) {
                var fechaFormateada = new Date(fechaSeleccionada).toLocaleDateString('es-ES');
                $('#asistenciaPlazasTabla').html("ASISTENCIA DE PERSONAL DE PLAZA " + nombrePlaza );
                $('#vacantesPlazaTabla').html("VACANTES DE PERSONAL DE PLAZA " + nombrePlaza );
            }
            cargarTablaGeneral(); 
            aplicarFiltroDia();
        } else if (tipoFiltro === 'semana') {

        var semanaSeleccionada = $('#fechaSemana').val(); 
        var partes = semanaSeleccionada.split('-W');

        var año = parseInt(partes[0]);
        var semana = parseInt(partes[1]);

        
        var fechaBase = new Date(año, 0, 4);

        
        var diaSemana = fechaBase.getDay() || 7; 
        var lunesSemana1 = new Date(fechaBase);
        lunesSemana1.setDate(fechaBase.getDate() - diaSemana + 1);

        
        var inicioSemana = new Date(lunesSemana1);
        inicioSemana.setDate(lunesSemana1.getDate() + (semana - 1) * 7);

        
        var finSemana = new Date(inicioSemana);
        finSemana.setDate(inicioSemana.getDate() + 5);

        var formatoFecha = { day: '2-digit', month: '2-digit', year: 'numeric' };
        fechaInicioStr = inicioSemana.toLocaleDateString('es-ES', formatoFecha);
        fechaFinStr = finSemana.toLocaleDateString('es-ES', formatoFecha);

        aplicarFiltroDia();
        } else if (tipoFiltro === 'mes') {

            var mesSeleccionado = $('#fechaMes').val();

            if (!mesSeleccionado) {
                alert('Selecciona un mes');
                return;
            }

            // Separar año y mes
            var partes = mesSeleccionado.split('-');
            var anio = parseInt(partes[0], 10);
            var mes = parseInt(partes[1], 10) - 1; // JS empieza en 0

            // Fechas reales
            var inicioMes = new Date(anio, mes, 1);
            var finMes = new Date(anio, mes + 1, 0);

            // Formato español
             fechaInicioStr = inicioMes.toLocaleDateString('es-MX');
             fechaFinStr = finMes.toLocaleDateString('es-MX');
            aplicarFiltroDia();
      
        }

    });

    // Agregar evento de click a las celdas de la tabla general
    $(document).on('click', '#miTablaGeneral tbody td', function() {
        var table = $('#miTablaGeneral').DataTable();
        var rowData = table.row($(this).closest('tr')).data(); 
        
        if (rowData) {    
            var plazaId = rowData.IID_PLAZA || 'N/A'; 
            var plazaNombre = rowData.V_RAZON_SOCIAL || 'N/A'; // Asegúrate de que este campo existe
        
            document.cookie = "iid_plaza=" + plazaId + "; path=/";
            document.cookie = "v_razon_social=" + encodeURIComponent(plazaNombre) + "; path=/";
     
            var fechaActual = $('#fechaDia').val();
            var fechaTitulo = '';
            
            if (fechaActual) {
                var partes = fechaActual.split('-');
                fechaTitulo = ' - ' + partes[2] + '/' + partes[1] + '/' + partes[0];
            }
           
            $('#asistenciaPersonal').show();
            $('#vacantesPersonal').show();
        
            cargarDatos(parseInt(plazaId));
            obtenerVacantes(parseInt(plazaId));
            
            console.log(' Datos recargados para plaza:', plazaNombre);
        }
    });

    function aplicarFiltroDia() {
        // Obtener el valor de la cookie 'iid_plaza'
        var iidPlaza = obtenerCookie('iid_plaza');
        var fechaSeleccionada = $('#fechaDia').val();
        
        if (iidPlaza) {
            console.log('Aplicando filtro - Plaza:', iidPlaza, 'Fecha:', fechaSeleccionada);
            cargarDatos(iidPlaza); // Esto ahora enviará la fecha seleccionada
        } else {
            alert('Primero selecciona una plaza de la tabla general');
        }
    }
    
    // Función auxiliar para obtener cookies
    function obtenerCookie(nombre) {
        var nombreEQ = nombre + "=";
        var cookies = document.cookie.split(';');
        for(var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i];
            while (cookie.charAt(0) == ' ') {
                cookie = cookie.substring(1, cookie.length);
            }
            if (cookie.indexOf(nombreEQ) == 0) {
                return decodeURIComponent(cookie.substring(nombreEQ.length, cookie.length));
            }
        }
        return null;
    }


   
var ultimaFilaSeleccionada;

function cargarDatos(iidPlaza) {
    

    var fechaSeleccionada = $('#fechaDia').val(); // Obtener la fecha seleccionada del input

    if (fechaInicioStr !== null && fechaFinStr !== null) {
        fechaSeleccionada = null; // Anular la fecha si se usan rangos
          if (fechaInicioStr) {
            var partes = fechaInicioStr.split('/');
            fechaInicioStr = partes[2] + '-' + partes[1] + '-' + partes[0];
        }
        
        // Convertir fechaFinStr de dd/mm/yyyy a yyyy-mm-dd
        if (fechaFinStr) {
            var partes = fechaFinStr.split('/');
            fechaFinStr = partes[2] + '-' + partes[1] + '-' + partes[0];
        }
        alert("----- esto es un debug----- fecha inicio:" + fechaInicioStr + " fecha fin: " + fechaFinStr);
        alert("----- Se ah borrado fecha de filtro diario: ----------" + fechaSeleccionada);
    }



    $.LoadingOverlaySetup({
        image: "", // Puedes usar una URL para un GIF personalizado
        fontawesome: "fa fa-spinner fa-spin", // Icono de carga (FontAwesome)
        text: "Cargando datos...", // Texto de carga
        textFontColor: "#ffffff", // Color del texto
        textFontSize: "14px", // Tamaño del texto
        textPosition: "center", // Posición del texto (center, top, bottom)
        background: "rgba(0, 0, 0, 0.8)", // Fondo del overlay
        zIndex: 1000, // Índice z del overlay
    });

    $("#asistenciaPersonal").LoadingOverlay("show");

    console.log('Enviando datos al backend:');
    console.log('  - iidPlaza:', iidPlaza);
    console.log('  - fecha:', fechaSeleccionada);
    //bandera3
    $.ajax({
        url: '../class/AsistenciaPersonal.php',
        method: 'POST',
        data: {
            accion: 'cargarDatos',
            clase: 'AsistenciaPersonal',
            metodo: 'obtenerAsistencia',
            iidPlaza: iidPlaza,
            fecha: fechaSeleccionada,
            fechaInicio: fechaInicioStr,
            fechaFin: fechaFinStr,
          
        },
        success: function(response) {
            console.log('Respuesta recibida:', response);
            construirTabla(response);
            calcularPorcentaje(response);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
            console.log('Respuesta del servidor:', jqXHR.responseText);
        },
        complete: function() {
            $("#asistenciaPersonal").LoadingOverlay("hide");
        }
    });

    //agregar otro $.ajax para reporte general
}

    function obtenerHoraMinutosSegundos(cadenaFechaHora) {
        if (cadenaFechaHora) {
            var partes = cadenaFechaHora.split(' ');
            if (partes.length >= 2) {
                var horaParte = partes[1];
                var [hora, minutos, segundos] = horaParte.split(':');
                var periodo = 'AM';

                if (parseInt(hora) >= 12) {
                    periodo = 'PM';
                    if (parseInt(hora) > 12) {
                        hora = (parseInt(hora) - 12).toString();
                    }
                }

                return `${hora}:${minutos}:${segundos} ${periodo}`;
            }
        }
        return cadenaFechaHora;
    }


    function construirTabla(data) {
        // Mostrar el GIF de carga usando Spin.js
        var collapsedGroups = {};

        // Limpiar el cuerpo de la tabla y destruir la instancia actual de DataTable si existe
        if ($.fn.DataTable.isDataTable('#miTabla')) {
            $('#miTabla').DataTable().clear().destroy();
        }
        
        var esRango = (fechaInicioStr !== null && fechaFinStr !== null);
  
        // Construir la tabla utilizando DataTables con botones de exportación e impresión
        var table = $('#miTabla').DataTable({
            data: data,
            columns: [{
                    data: 'IID'
                },
                {
                    data: 'NOMBRE'
                },
                {
                    data: 'V_DESCRIPCION'
                },
                {
                    data: 'DESCRIPCION'
                },
                {
                    data: 'PLAZA'
                },
                {
                    data: 'NOMBREALMA',
                        render: function(data, type, row) {
                        // Si el tipo de renderizado es para mostrar en la tabla
                        if (type === 'display') {
                            // Validar que data no sea null o undefined
                            if (data && typeof data === 'string') {
                                // Reemplazar 'NOMBREALMA' con una cadena vacía
                                return data.replace('NOMBREALMA', '');
                            } else {
                                return ''; // o 'N/A' o cualquier valor por defecto
                            }
                        }
                        // Si el tipo de renderizado es para ordenamiento, búsqueda, etc., devolver el dato original
                        return data || '';
                 }
                },
                {
                    data: 'LLEGO_TIEMPO',
                    render: function(data, type, row) {
                        // Si el texto es 'PRESENTE', muestra una palomita verde; de lo contrario, muestra una cruz roja
                        if (data === 'PRESENTE') {
                            return '<i class="fa fa-check-circle" style="color: green; font-size: 24px;"><span class="hidden">ASISTENCIA</span> </i>' + (row.HORA ? row.HORA : '') +' ';
                        } else {
                            var faltaColor = (row.FALTA === 'FALTA INJUSTIFICADA' || row.FALTA === null) ? 'red' : '#EFA94A';
                            var faltaCheck = (row.FALTA === 'FALTA INJUSTIFICADA' || row.FALTA === null) ? ' fa-times-circle' : 'fa-check-circle';                            

                            return '<i class="fa '+faltaCheck+'" style="color: ' + faltaColor + '; font-size: 24px;"><span class="hidden">FALTA</span></i>';                           
                        }
                    }
                }, { 
                data: 'HORA_SALIDA',
                render: function(data, type, row) {
                    // Solo mostrar HORA_SALIDA si el empleado asistió (LLEGO_TIEMPO === 'PRESENTE')
                    if (row.LLEGO_TIEMPO === 'PRESENTE' && data) {
                        return data;
                    } else {
                        return '';
                    }
                }
            },          
                //{ data: 'TIEMPO', render: function(data) { return obtenerHoraMinutosSegundos(data); } },
                {
                    data: 'FALTA',
                    render: function(data, type, row) {
                        if (row.HORA !== null) {
                            // Si row.HORA no es null, no mostrar nada
                            return '';
                        }else{
                            return data;
                        }
                                    
                }
                },
                {
                    data: 'FECHA',
                    render: function(data, type, row) {
                        if (data){
                            var partes = data.split('-');
                            if (partes.length === 3) {
                                return partes[2] + '/' + partes[1] + '/' + partes[0];
                            } else {
                                return data; // Devuelve el dato original si no tiene el formato esperado
                            }
                        }
                        return "";
                    }
                }

            ],
            dom: 'lBfrtip', // 'B' para botones
            buttons: [{
                    extend: 'copy',
                    text: 'Copiar',
                    exportOptions: {
                        columns: ':visible'
                    }
                }, {
                    text: 'Exportar a Excel',
                    action: function(e, dt, node, config) {
                        if (confirm('¿Deseas exportar solo los datos visibles en la tabla?')) {
                            $('#miTabla').tableExport({
                                fileName: 'Asistencias',
                                type: 'xls', // Puedes cambiar el tipo de archivo según tus necesidades
                                escape: 'false'
                            });
                        }
                    }

                },
                {
                    extend: 'pdf',
                    text: 'Exportar a PDF',
                    exportOptions: {
                        columns: ':visible'
                    }
                },
                {
                    extend: 'print',
                    text: 'Imprimir',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
            language: {
                "url": "../plugins/datatables/Spanish.json"
            },
            ordering: false,
            rowGroup: {
                dataSrc: ['NOMBREALMA', 'V_DESCRIPCION'],
                startRender: function(rows, group) {
                    var bgColor = group.includes('NOMBREALMA') ? 'green' : 'yellow';


                    var textColor = group.includes('NOMBREALMA') ? 'white' : 'black';

                    var collapsed = !!collapsedGroups[group];
                    rows.nodes().each(function(r) {
                        r.style.display = collapsed ? 'none' : '';
                    });

                    // Calcular el total de empleados y asistencias en el grupo
                    var totalEmpleados = rows
                        .data()
                        .pluck('TOTAL_EMPLEADO')
                        .reduce(function(acc, val) {
                            return val;
                        });

                    var tota_Faltas = rows
                        .data()
                        .pluck('TOTAL_ASISTENCIAS')
                        .reduce(function(acc, val) {
                            return val;
                        });

                        // console.log('Total Empleados en grupo ' + totalEmpleados);
                        // console.log('Total Asistencias en grupo ' + tota_Faltas);
                        // alert('Total Empleados en grupo ' + totalEmpleados);
                        // alert('Total Asistencias en grupo ' + tota_Faltas);

                    var totalChecks = Number(totalEmpleados) - Number(tota_Faltas) ;    

                    var ratio = tota_Faltas === 100 ? 0 : (totalChecks/totalEmpleados )*100;
                    
                    

                    if (bgColor == 'green' /*|| bgColor == 'purple'*/) {
                        textValor = '<b> ' /*+ almacen_hab*/ + group.replace('NOMBREALMA', '') + '</b>';
                    } else {
                        textoInicial = group.replace('NOMBREALMA', '');
                        var lastHyphenIndex = textoInicial.lastIndexOf('-');
                        var extratedPart = textoInicial.slice(0, lastHyphenIndex);
                        textValor = '<b> ' + extratedPart + '</b> ' + ' - <b>Total Empleados:</b> ' + totalEmpleados + ' <b>Asistencia:</b> ' + totalChecks + ' <b>Porcentaje Asistencia:</b> ' + ratio.toFixed(2) + '%';
                    }
                    // Renderizar la fila de grupo con los cálculos
                    return $('<tr/>')
                        .append('<td colspan="10" style="text-align:center; color:' + textColor + '; background-color: ' + bgColor + ';">' +
                            textValor +
                            '</td>')
                        .attr('data-name', group)
                        .toggleClass('collapsed', collapsed);
                }
            },
            
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All']
            ], // Opciones de cantidad de registros por página
            pageLength: -1 // Cantidad de registros por página por defecto
        });

        // Collapse específicamente los grupos de V_DESCRIPCION
        data.forEach(function(item) {
            collapsedGroups[item.V_DESCRIPCION] = true;
        });

        table.draw();

        // Collapse Groups
        $('#miTabla tbody').on('click', 'tr.dtrg-start', function() {
            var name = $(this).data('name');
            collapsedGroups[name] = !collapsedGroups[name];
            table.draw(false);
        });

    }

    


    function calcularPorcentaje(data/*, $fecha, $fechaInicio, $fechaFin*/) {

            //console.log(data)
            //calcular porcentaje de asistencia por filtro de fecha
            // if ($fecha){
            //     // Filtrar los registros donde LLEGO_TIEMPO es igual a "PRESENTE"
            //     var presentes = data.filter(function(registro) {
            //     return registro.LLEGO_TIEMPO === "PRESENTE";
            // });

            // // Calcular el porcentaje
            // var porcentaje = (presentes.length / data.length) * 100;           

            // }
            // //calcular porcentaje por rango de fechas
            // else if($fechaInicio && $fechaFin){
                
            // }else{
                // alert("no se puso calcular el porcentaje");
                
            // Filtrar los registros donde LLEGO_TIEMPO es igual a "PRESENTE"
            var presentes = data.filter(function(registro) {
                return registro.LLEGO_TIEMPO === "PRESENTE";
            });

            // Calcular el porcentaje
            var porcentaje = (presentes.length / data.length) * 100;      
            // }



 
    }

    function obtenerVacantes(iidPlaza) {

        // Personaliza el diseño del overlay
        $.LoadingOverlaySetup({
            image: "", // Puedes usar una URL para un GIF personalizado
            fontawesome: "fa fa-spinner fa-spin", // Icono de carga (FontAwesome)
            text: "Cargando datos...", // Texto de carga
            textFontColor: "#000", // Color del texto
            textFontSize: "14px", // Tamaño del texto
            textPosition: "center", // Posición del texto (center, top, bottom)
            background: "rgba(0, 0, 0, 0.8)", // Fondo del overlay
            zIndex: 1000, // Índice z del overlay
        });

        $("#vacantesPersonal").LoadingOverlay("show");

        $.ajax({
            url: '../class/AsistenciaPersonal.php',
            method: 'POST',
            data: {
                accion: 'cargarDatos',
                clase: 'AsistenciaPersonal', // Reemplaza con el nombre de tu clase
                metodo: 'obtenerVacantes', // Reemplaza con el nombre de tu función dentro de la clase
                iidPlaza: iidPlaza, // Enviar el valor de iidPlaza como parte de los datos
                fecha: $('#fechaDia').val(),
                fechaInicio: null,
                fechaFin: null,
            },
            success: function(response) {
                //console.log(response);
                //alert(response);                
                construirTabla2(response);
                // Lógica para manejar la respuesta (puede ser llamar a otra función)
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
                console.log(jqXHR.responseText); // Esto imprimirá el cuerpo de la respuesta del servidor
            },
            complete: function() {
                // Ocultar el GIF de carga después de que se haya completado la solicitud AJAX
                $("#vacantesPersonal").LoadingOverlay("hide");
            }
        });
    }

    function construirTabla2(data) {
        // Limpiar el cuerpo de la tabla y destruir la instancia actual de DataTable si existe
        if ($.fn.DataTable.isDataTable('#miTabla2')) {
            $('#miTabla2').DataTable().clear().destroy();
        }

        // Construir la tabla utilizando DataTables con botones de exportación e impresión
        $('#miTabla2').DataTable({
            data: data,
            columns: [{
                    data: 'IID_SOLICITUD'
                },
                {
                    data: 'V_DESCRIPCION'
                },
                {
                    data: 'N_STATUS',
                    render: function(data, type, row) {
                        // Si el texto es 'PRESENTE', muestra una palomita verde; de lo contrario, muestra una cruz roja
                        
                        if (row.N_STANDBYE === '1' && row.N_V_OCUPADA != 1) {
                            return '<span class="badge bg-yellow">STANDBY</span>'; 
                        }else if(row.N_V_OCUPADA == 1 && row.N_STANDBYE === '1'){
                            return ' <span class="badge bg-orange">PERSONAL EN PUESTO</span>';                    
                        }else if(data === '1'){
                            return '<span class="badge bg-blue">REGISTRADA</span>';
                        } else {
                            return ' <span class="badge bg-green">AUTORIZADA</span>';
                        }
                    }
                },{
                    data: 'V_JUSTIFICACION',                    
                },
                {
                    data: 'D_FEC_AUT_DG'
                }
            ],
            dom: 'lBfrtip', // 'B' para botones
            buttons: [{
                    extend: 'copy',
                    text: 'Copiar'
                },
                {
                    extend: 'excel',
                    text: 'Exportar a Excel'
                },
                {
                    extend: 'pdf',
                    text: 'Exportar a PDF'
                },
                {
                    extend: 'print',
                    text: 'Imprimir'
                }
            ],
            language: {
                "url": "../plugins/datatables/Spanish.json"
            }
        });
    }

    function cargarTablaGeneral() {
        //Validamos para agregar animacion a primera carga. 
        var fechaFiltro = $('#fechaDia').val();
        $("#contenidoReporteGral").LoadingOverlay("show");

//bandera
        $.ajax({
            url: '../class/AsistenciaPersonal.php',
            method: 'POST',
            data: {
                accion: 'cargarDatos',
                clase: 'AsistenciaPersonal', // Reemplaza con el nombre de tu clase
                metodo: 'obtenerReporteGeneral',
                fecha: fechaFiltro // Enviar la fecha seleccionada
            },
            success: function(response) {
                console.log(response);
                //alert(response);
                construirTablaGeneral(response);
                // Lógica para manejar la respuesta (puede ser llamar a otra función)
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
                console.log(jqXHR.responseText); // Esto imprimirá el cuerpo de la respuesta del servidor
            },
            complete: function() {
                // Ocultar el GIF de carga después de que se haya completado la solicitud AJAX                
                $("#contenidoReporteGral").LoadingOverlay("hide");

            }
        });
    }

    function construirTablaGeneral(data) {
        // Limpiar el cuerpo de la tabla y destruir la instancia actual de DataTable si existe
        if ($.fn.DataTable.isDataTable('#miTablaGeneral')) {
            $('#miTablaGeneral').DataTable().clear().destroy();
        }


        // Construir la tabla utilizando DataTables con botones de exportación e impresión
        var table = $('#miTablaGeneral').DataTable({
            scrollX: true,
            data: data,
            columns: [{
                    data: 'IID_PLAZA'
                }, {
                    data: 'V_RAZON_SOCIAL'
                },
                {
                    data: 'QUINCENAL'
                },
                {
                    data: 'SEMANAL'
                },
                {
                    data: 'PERSONAL_ACTIVO'
                },
                {
                    data: 'VACANTES'
                },
                {
                    data: 'VACANTES_STANDBY'
                },
                {
                    data: 'FALTA_INCAPACIDAD'
                },
                /*{
                    data: 'FALTA_HABILITADO'
                },*/
                {
                    data: 'FALTA_COMISION_PLAZAS'
                },
                {
                    data: 'FALTAS_SIN_JUSTIFICACION'
                },
                {
                    data: 'HOME_OFFICE'
                },
                {
                    data: 'VACACIONES'
                },
                {
                    data: null,
                    render: function(data, type, row) {
                        // Calcular la suma de QUINCENAL y SEMANAL
                        var personalActivo = parseFloat(row['PERSONAL_ACTIVO']) || 0;
                        var faltoSinJus = parseFloat(row['FALTAS_SIN_JUSTIFICACION']) || 0;

                        var checoTotal = parseFloat(personalActivo-faltoSinJus);
                        // Retornar la suma como el contenido de la celda
                        return checoTotal;
                    }
                },
                
                {
                    data: null,
                    render: function(data, type, row) {
                        // Calcular la suma de QUINCENAL y SEMANAL
                        /*
                        var quincenal = parseFloat(row['QUINCENAL']) || 0;
                        var semanal = parseFloat(row['SEMANAL']) || 0;
                        */
                        var personalActivo = parseFloat(row['PERSONAL_ACTIVO']) || 0;
                        var faltoSinJus = parseFloat(row['FALTAS_SIN_JUSTIFICACION']) || 0;
                        /*
                        var falta_Injustificada = parseFloat(row['ASISTENCIA']) || 0;
                        var habilitados = parseFloat(row['FALTA_HABILITADO'])|| 0;
                        */

                        var porcentajeAsistencia = (((personalActivo - faltoSinJus) / (personalActivo)) * 100).toFixed(2);

                        if (porcentajeAsistencia >= 70) {
                            return '<span class="badge bg-green">' + porcentajeAsistencia + '%</span>';
                        } else {
                            return ' <span class="badge bg-red">' + porcentajeAsistencia + '%</span>';
                        }
                        // Retornar la suma como el contenido de la celda
                        //return (((asistencia+faltaHome+faltaComPlazas)/(quincenal + semanal))*100).toFixed(2)+'%';
                    }
                }
            ],
            dom: 'lBfrtip', // 'B' para botones
            buttons: [{
                    extend: 'copy',
                    text: 'Copiar'
                },
                {
                    extend: 'excel',
                    text: 'Exportar a Excel'
                },
                {
                    extend: 'pdf',
                    text: 'Exportar a PDF'
                },
                {
                    extend: 'print',
                    text: 'Imprimir'
                }
            ],
            ordering: false,
            select: true,
            language: {
                "url": "../plugins/datatables/Spanish.json"
            },
            footerCallback: function(row, data, start, end, display) {
                // Calcular totales
                var quincenalTotal = 0;
                var semanalTotal = 0;
                var asistenciaTotal = 0;
                var vacantes = 0;
                var vacantesStandby = 0;
                var incapacidad = 0;
                var personalModulo = 0;
                var trabajoPlazas = 0;
                var personalInjustificado = 0;
                var homeOffice = 0;
                var vacaciones = 0;

                for (var i = 0; i < data.length; i++) {
                    quincenalTotal += parseFloat(data[i]['QUINCENAL']) || 0;
                    semanalTotal += parseFloat(data[i]['SEMANAL']) || 0;
                    asistenciaTotal += parseFloat(data[i]['ASISTENCIA']) || 0;
                    vacantes += parseFloat(data[i]['VACANTES']) || 0;
                    vacantesStandby += parseFloat(data[i]['VACANTES_STANDBY']) || 0;
                    incapacidad += parseFloat(data[i]['FALTA_INCAPACIDAD']) || 0;
                    personalModulo += parseFloat(data[i]['FALTA_HABILITADO']) || 0;
                    trabajoPlazas += parseFloat(data[i]['FALTA_COMISION_PLAZAS']) || 0;
                    personalInjustificado += parseFloat(data[i]['FALTA_INJUSTIFICADA']) || 0;
                    homeOffice += parseFloat(data[i]['HOME_OFFICE']) || 0;
                    vacaciones += parseFloat(data[i]['VACACIONES']) || 0;
                }

                var total = parseFloat(semanalTotal.toFixed(0)) + parseFloat(quincenalTotal.toFixed(0));
                //var asistenciaTotalC = parseFloat(asistenciaTotal.toFixed(0)) + parseFloat(trabajoPlazas.toFixed(0)) + parseFloat(homeOffice.toFixed(0));
                var totalchecoreloj =  parseFloat(asistenciaTotal.toFixed(0)) + parseFloat(personalModulo.toFixed(0));
                var porcentajeTotal = parseFloat(totalchecoreloj / total) * 100;

                
                //Estulos
                $(row).find('th, td')
                    .css('text-align', 'center') // Centrar el texto
                    .css('background-color', 'red') // Color de fondo rojo
                    .css('color', 'white'); // Texto blan
                // Actualizar las celdas del pie de página con los totales
                $(row).find('th:eq(2)').text(quincenalTotal.toFixed(0));
                $(row).find('th:eq(3)').text(semanalTotal.toFixed(0));
                $(row).find('th:eq(4)').text(total);
                $(row).find('th:eq(5)').text(vacantes);
                $(row).find('th:eq(6)').text(vacantesStandby);
                $(row).find('th:eq(7)').text(incapacidad);
                //$(row).find('th:eq(7)').text(personalModulo);
                $(row).find('th:eq(8)').text(trabajoPlazas);
                $(row).find('th:eq(9)').text(personalInjustificado);
                $(row).find('th:eq(10)').text(homeOffice);
                $(row).find('th:eq(11)').text(vacaciones);
                $(row).find('th:eq(12)').text(totalchecoreloj.toFixed(0));
                //$(row).find('th:eq(13)').text(asistenciaTotalC.toFixed(0));
                $(row).find('th:eq(13)').text(porcentajeTotal.toFixed(2) + '%');

                // Después de calcular totales, aplicar animación a los elementos
                $('#porcentajeNumero').text(0).stop().countTo({ from: 0, to: porcentajeTotal, speed: 6000, refreshInterval: 50, formatter: function (value, options) { return value.toFixed(2); } });
                $('#cantidadActivos').text(0).stop().countTo({ from: 0, to: total, speed: 6000, refreshInterval: 50 });
                $('#cantidadVacantes').text(0).stop().countTo({ from: 0, to: vacantes, speed: 6000, refreshInterval: 50 });
                $('#cantidadVacantesStandby').text(0).stop().countTo({ from: 0, to: vacantesStandby, speed: 6000, refreshInterval: 50 });
                
                // Almacenar los valores en variables globales
                document.cookie = 'porcentajeTotal=' + porcentajeTotal.toFixed(2) + '; expires=' + new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString(); // Caduca en 30 días
                document.cookie = 'total=' + total + '; expires=' + new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString();
                document.cookie = 'vacantes=' + vacantes + '; expires=' + new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString();
                document.cookie = 'vacantes_standby=' + vacantesStandby + '; expires=' + new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toUTCString();

            }
        });

        // Ocultar la columna con índice correspondiente a IID_PLAZA
        table.on('init', function() {
            table.column(0).visible(false);

            var iidPlazaCookie = obtenerCookie('iid_plaza');
            if (iidPlazaCookie) {
                var rowIndex = table.column(0).data().indexOf(iidPlazaCookie.toString());
                if (rowIndex !== -1) {
                    // Seleccionar la fila correspondiente al valor de la cookie
                    console.log("AQUI"+table.column(0).data().indexOf(iidPlazaCookie.toString()));
                    //alert(table.column(0).data().indexOf(iidPlazaCookie.toString()));
                    table.row(rowIndex).select();
                    
                }
            }
        });

        // Manejar el evento select
        table.on('select', function(e, dt, type, indexes) {
            if (type === 'row') {
                // Mostrar temporalmente la columna IID_PLAZA cuando se selecciona una fila
                table.column(0).visible(true);

                // Obtener los datos de la fila seleccionada
                var selectedData = table.rows(indexes).data().toArray()[0];

                // Ocultar nuevamente la columna IID_PLAZA
                table.column(0).visible(false);

                
                document.cookie = 'iid_plaza=' + selectedData.IID_PLAZA + "; path=/;";
                document.cookie = 'v_razon_social=' + selectedData.V_RAZON_SOCIAL+ "; path=/;";

                var usuarioEnJS = '<?php echo $_SESSION['usuario']; ?>';
                localStorage.setItem('usuario', usuarioEnJS);


                ultimaFilaSeleccionada = table.rows(indexes).data().toArray()[0];

                var iidPlazaCookie = obtenerCookie('iid_plaza');
                var nombrePlaza = obtenerCookie('v_razon_social');
                if (iidPlazaCookie && inicial()) {

                    $('#asistenciaPersonal').show();           
                    var asisTabla = document.getElementById("asistenciaPlazasTabla");
                    asisTabla.innerHTML = "ASISTENCIA DE PERSONAL DE PLAZA " + nombrePlaza;
                    cargarDatos(parseInt(iidPlazaCookie)); 
                    
                    $('#vacantesPersonal').show();
                    var vacTabla = document.getElementById("vacantesPlazaTabla");
                    vacTabla.innerHTML = "VACANTES DE PERSONAL DE PLAZA "+nombrePlaza;
                    obtenerVacantes(parseInt(iidPlazaCookie));
                }
                else{
                    $('#asistenciaPersonal').hide();
                    $('#vacantesPersonal').hide();                    
                }

            }

        });

    }


    $(document).ready(function() {
            inicial();
            // Función para aplicar la clase de rotación inicial
            function aplicarRotacionInicial() {
                porcentajeWidget.classList.add("initial-spin");
                vacantesWid.classList.add("initial-spin");
                textoUsuarios.classList.add("initial-spin");
            }

            // Variables globales para los elementos que contienen la clase "initial-spin"
            var porcentajeWidget = document.getElementById('textoPorcentajeWid');
            var vacantesWid = document.getElementById('textoVacantesWid');
            var textoUsuarios = document.getElementById('textoUsuarios');

            // Aplicar la rotación inicial al cargar la página
            aplicarRotacionInicial();

            // Después de 5 segundos, quita la clase de rotación
            setTimeout(function() {
                porcentajeWidget.classList.remove("initial-spin");
                vacantesWid.classList.remove("initial-spin");
                textoUsuarios.classList.remove("initial-spin");

                // Después de 10 minutos, vuelve a aplicar la clase de rotación inicial
                setTimeout(function() {
                    // Vuelve a seleccionar los elementos después de 10 minutos
                    porcentajeWidget = document.getElementById('textoPorcentajeWid');
                    vacantesWid = document.getElementById('textoVacantesWid');
                    textoUsuarios = document.getElementById('textoUsuarios');

                    aplicarRotacionInicial();
                }, 600000); // 10 minutos = 600,000 milisegundos
            }, 5000);

        if (tieneCookie('iid_plaza') && inicial()) {
            // Obtener el valor de la cookie 'iid_plaza'
            var iidPlazaCookie = obtenerCookie('iid_plaza');
            // Llamar a la función cargarDatos con el valor de la cookie
            console.log('La cookie "iid_plaza" está presente.' + iidPlazaCookie);
            //Cargar la general primero 


            cargarDatos(parseInt(iidPlazaCookie));

            cargarTablaGeneral();

            obtenerVacantes(parseInt(iidPlazaCookie));

        } else {
            // Lógica para el caso en que la cookie no exista
            console.log('La cookie "iid_plaza" no está presente.');

            cargarTablaGeneral();
            
            $('#asistenciaPersonal').hide();
            $('#vacantesPersonal').hide();
            //Oculto el demas contenido si el cookie IID_PLAZA NO EXITE
            var iidPlazaCookie = obtenerCookie('iid_plaza');
            if (!iidPlazaCookie) {
                $('#asistenciaPersonal').hide();
                $('#vacantesPersonal').hide();
            }
        }

        if (tieneCookie('v_razon_social') && inicial()) {
            // Obtener el valor de la cookie 'iid_plaza'
            var nombrePlaza = obtenerCookie('v_razon_social');
            var asisTabla = document.getElementById("asistenciaPlazasTabla");
            var vacTabla = document.getElementById("vacantesPlazaTabla");
            
            asisTabla.innerHTML = "ASISTENCIA DE PERSONAL DE PLAZA " + nombrePlaza; 
            vacTabla.innerHTML = "VACANTES DE PERSONAL DE PLAZA "+nombrePlaza;
        } else {
            var asisTabla = document.getElementById("asistenciaPlazasTabla");
            var vacTabla = document.getElementById("vacantesPlazaTabla");
            
            asisTabla.innerHTML = "ASISTENCIA DE PERSONAL DE PLAZA "; 
            vacTabla.innerHTML = "VACANTES DE PERSONAL DE PLAZA ";
        }
    });

    function obtenerUltimaFilaSeleccionada() {
        
        console.log(ultimaFilaSeleccionada);
        return ultimaFilaSeleccionada;
        
    }

    // Función para verificar si hay una cookie con un nombre específico
    function tieneCookie(nombre) {
        var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i].trim();
            if (cookie.indexOf(nombre + '=') === 0) {
                return true;
            }
        }
        return false;
    }

    // Función para obtener el valor de una cookie por su nombre
    function obtenerCookie(nombre) {
        var cookieName = nombre + '=';
        var cookieArray = document.cookie.split(';');
        for (var i = 0; i < cookieArray.length; i++) {
            var cookie = cookieArray[i].trim();
            if (cookie.indexOf(cookieName) === 0) {
                return decodeURIComponent(cookie.substring(cookieName.length));
            }
        }
        return null;
    }

    function inicial(){
        var usuarioEnLocalStorage = localStorage.getItem('usuario');
        var usuarioEnPHP = '<?php echo $_SESSION['usuario']; ?>';

        if (usuarioEnLocalStorage === usuarioEnPHP) {
            return true;
        } else {
            
            return false;
        }
    }

    function eliminarCookie(nombre) {
        console.log("Entro a eliminar");
        document.cookie = nombre + '=; expires=' + new Date(0).toUTCString() + '; path=/; domain=' + window.location.hostname;
    }


    // Puedes proporcionar el valor deseado de iidPlaza
</script>


<!-- jQuery 2.2.3 -->
<script src="../plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="../bootstrap/js/bootstrap.min.js"></script>
<!-- FastClick -->
<script src="../plugins/fastclick/fastclick.js"></script>
<!-- AdminLTE App -->
<script src="../dist/js/app.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../dist/js/demo.js"></script>
<!-- Select2 -->
<script src="../plugins/select2/select2.full.min.js"></script>
<!-- Grafica Highcharts -->
<script src="../plugins/highcharts/highcharts.js"></script>
<script src="../plugins/highcharts/modules/data.js"></script>
<script src="../plugins/highcharts/modules/exporting.js"></script>
<!-- DataTables -->
<script src="../plugins/datatables/jquery.dataTables.min.js"></script>
<script src="../plugins/datatables/dataTables.bootstrap.min.js"></script>
<!-- DataTables buttons -->
<script src="../plugins/datatables/extensions/buttons_datatable/dataTables.buttons.min.js"></script>
<script src="../plugins/datatables/extensions/buttons_datatable/buttons.html5.min.js"></script>
<!-- DataTables export exel -->
<script src="../plugins/datatables/extensions/buttons_datatable/jszip.min.js"></script>
<!-- DataTables muestra/oculta columna -->
<script src="../plugins/datatables/extensions/buttons_datatable/buttons.colVis.min.js"></script>
<!-- DataTables button print -->
<script src="../plugins/datatables/extensions/buttons_datatable/buttons.print.min.js"></script>
<!-- SELECT DATATBLE -->
<script src="../plugins/datatables/extensions/Select/dataTables.select.min.js"></script>
<!-- RESPONSIVE DATATBLE -->
<script src="../plugins/datatables/extensions/Responsive/js/dataTables.responsive.min.js"></script>

<script src="https://cdn.jsdelivr.net/jquery.loadingoverlay/latest/loadingoverlay.min.js"></script>

<script src="https://unpkg.com/tableexport.jquery.plugin/tableExport.min.js"></script>

<!--mis campos -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/rowgroup/1.4.1/js/dataTables.rowGroup.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-countto/1.2.0/jquery.countTo.min.js"></script>


<script>
    function cargarPantalla(iidconsecutivo, tipo) {
        $("#modal").load("empleados_det.php?iid_emple=" + iidconsecutivo + "&tipo=" + tipo + "");
    }
</script>
<!-- date-range-picker -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js"></script>
<script src="../plugins/daterangepicker/daterangepicker.js"></script>

<!-- Inicia FancyBox JS -->
<!-- Add mousewheel plugin (this is optional) -->
<script type="text/javascript" src="../plugins/fancybox/lib/jquery.mousewheel.pack.js?v=3.1.3"></script>
<!-- Add fancyBox main JS and CSS files -->
<script type="text/javascript" src="../plugins/fancybox/source/jquery.fancybox.pack.js?v=2.1.5"></script>
<!-- Add Button helper (this is optional) -->
<script type="text/javascript" src="../plugins/fancybox/source/helpers/jquery.fancybox-buttons.js?v=1.0.5"></script>
<!-- Add Thumbnail helper (this is optional) -->
<script type="text/javascript" src="../plugins/fancybox/source/helpers/jquery.fancybox-thumbs.js?v=1.0.7"></script>
<script type="text/javascript">
    $(document).ready(function() {
        /*
         *  Simple image gallery. Uses default settings
         */

        $('.fancybox').fancybox();

        /*
         *  Different effects
         */

        // Change title type, overlay closing speed
        $(".fancybox-effects-a").fancybox({
            helpers: {
                title: {
                    type: 'outside'
                },
                overlay: {
                    speedOut: 0
                }
            }
        });

        // Disable opening and closing animations, change title type
        $(".fancybox-effects-b").fancybox({
            openEffect: 'none',
            closeEffect: 'none',

            helpers: {
                title: {
                    type: 'over'
                }
            }
        });


    });
</script>
<!-- Termina FancyBox JS -->

<!-- PACE -->
<script src="../plugins/pace/pace.min.js"></script>
<!-- page script -->
<script type="text/javascript">
    // To make Pace works on Ajax calls
    $(document).ajaxStart(function() {
        Pace.restart();
    });
    $('.ajax').click(function() {
        $.ajax({
            url: '#',
            success: function(result) {
                $('.ajax-content').html('<hr>Ajax Request Completed !');
            }
        });
    });
</script>

</html>
<?php conexion::cerrar($conn); ?>