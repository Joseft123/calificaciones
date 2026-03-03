<?php
session_start();

// Validar que el usuario tenga acceso (Cualquier rol logueado)
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['id_docente']) && !isset($_SESSION['id_alumno'])) {
    die('Acceso denegado');
}

// Suprimir advertencias deprecated si existen en producción para que FPDF pueda emitir headers
error_reporting(E_ALL & ~E_DEPRECATED);

include '../includes/conexion.php';
require '../includes/fpdf/fpdf.php';

// Obtener ID del alumno
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('Falta el ID del alumno');
}
$id_alumno = intval($_GET['id']);

// Consulta: Datos del Alumno
$sql_alumno = "SELECT matricula, nombre, apellidos, nivel, grado, grupo FROM alumnos WHERE id_alumno = $id_alumno";
$res_alumno = $conexion->query($sql_alumno);

if ($res_alumno->num_rows == 0) {
    die('Alumno no encontrado');
}
$alumno = $res_alumno->fetch_assoc();

// Consulta: Calificaciones
$sql_calificaciones = "SELECT m.clave_materia, m.nombre_materia, c.calificacion, c.fecha_registro
                       FROM calificaciones c
                       INNER JOIN materias m ON c.id_materia = m.id_materia
                       WHERE c.id_alumno = $id_alumno
                       ORDER BY m.nombre_materia ASC";
$res_calificaciones = $conexion->query($sql_calificaciones);

// Función universal modernizada para texto a PDF (Reemplazo de utf8_decode)
function txtPDF($texto)
{
    if (function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($texto, 'ISO-8859-1', 'UTF-8');
    }
    return $texto;
}

// --- CLASE EXTENDIDA PARA PDF (HEADER/FOOTER) ---
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(13, 110, 253);
        $this->Cell(0, 15, txtPDF('SISTEMA ESCOLAR - REPORTE OFICIAL'), 0, 1, 'C');

        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(50, 50, 50);
        $this->Cell(0, 8, txtPDF('BOLETA DE CALIFICACIONES'), 0, 1, 'C');
        $this->Ln(5);

        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-30);

        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.3);
        $this->Line(40, $this->GetY(), 90, $this->GetY());
        $this->Line(120, $this->GetY(), 170, $this->GetY());

        $this->Ln(2);
        $this->SetFont('Arial', '', 9);
        $this->Cell(105, 5, txtPDF('Firma del Director'), 0, 0, 'C');
        $this->Cell(30, 5, txtPDF('Firma del Tutor / Académico'), 0, 1, 'C');

        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, txtPDF('Documento Generado el: ' . date('d/m/Y H:i')) . ' - Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// --- CREACIÓN DEL DOCUMENTO ---
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Info del Alumno 
$pdf->SetFillColor(245, 245, 245);
$pdf->SetDrawColor(220, 220, 220);
$pdf->Rect(10, 45, 190, 25, 'DF');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetXY(15, 48);
$pdf->Cell(25, 6, txtPDF('Alumno: '));
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 6, txtPDF($alumno['apellidos'] . ' ' . $alumno['nombre']));

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 6, txtPDF('Matrícula: '));
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 6, txtPDF($alumno['matricula']));

$pdf->SetXY(15, 56);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 6, txtPDF('Nivel: '));
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(95, 6, txtPDF($alumno['nivel']));

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(25, 6, txtPDF('Grado/Grupo: '));
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 6, txtPDF(strval($alumno['grado']) . 'º ' . $alumno['grupo']));

$pdf->Ln(20);

// --- TABLA DE CALIFICACIONES ---
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(13, 110, 253);

$pdf->SetTextColor(255, 255, 255);
$pdf->SetDrawColor(10, 90, 210);

$pdf->Cell(30, 8, txtPDF('Clave'), 1, 0, 'C', true);
$pdf->Cell(90, 8, txtPDF('Asignatura'), 1, 0, 'L', true);
$pdf->Cell(35, 8, txtPDF('Calificación'), 1, 0, 'C', true);
$pdf->Cell(35, 8, txtPDF('Registro'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(200, 200, 200);

$fill = false;

$pdf->SetFillColor(245, 250, 255);

$suma_calificaciones = 0;
$total_materias = 0;

if ($res_calificaciones->num_rows > 0) {
    while ($fila = $res_calificaciones->fetch_assoc()) {
        $pdf->Cell(30, 7, txtPDF($fila['clave_materia']), 'LRB', 0, 'C', $fill);
        $pdf->Cell(90, 7, txtPDF($fila['nombre_materia']), 'LRB', 0, 'L', $fill);

        $calif = floatval($fila['calificacion']);
        if ($calif < 6.0) {
            $pdf->SetTextColor(220, 53, 69);
            $pdf->SetFont('Arial', 'B', 10);
        }
        else {
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', '', 10);
        }

        $pdf->Cell(35, 7, number_format($calif, 1), 'LRB', 0, 'C', $fill);

        $pdf->SetTextColor(80, 80, 80);
        $pdf->SetFont('Arial', '', 9);
        $pdf->Cell(35, 7, date('d/m/Y', strtotime($fila['fecha_registro'])), 'LRB', 1, 'C', $fill);

        $suma_calificaciones += $calif;
        $total_materias++;
        $fill = !$fill;
    }

    $pdf->SetTextColor(0, 0, 0);
    $promedio = $total_materias > 0 ? ($suma_calificaciones / $total_materias) : 0;

    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(120, 10, txtPDF('PROMEDIO FINAL DEL ALUMNO:'), 0, 0, 'R');

    $pdf->SetFillColor(230, 240, 255);
    $pdf->Cell(35, 10, number_format($promedio, 1), 1, 1, 'C', true);

}
else {
    $pdf->Cell(190, 15, txtPDF('El alumno no cuenta con calificaciones registradas en este periodo.'), 1, 1, 'C');
}

// Limpiar buffer (evitar echo fantasma)
ob_clean();

// Salida al navegador
$pdf->Output('I', 'Boleta_' . $alumno['matricula'] . '.pdf');

$conexion->close();
?>
