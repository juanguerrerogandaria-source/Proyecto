<?php
require_once __DIR__ . '/../../database/conexcion_BD.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

$comprobante = $_SESSION['flow_comprobante'] ?? null;

if (empty($comprobante)) {
    header('Location: index.php');
    exit;
}

// Paleta de la marca (violeta)
$violetaR = 124; $violetaG = 58; $violetaB = 237;
$grisR = 90; $grisG = 90; $grisB = 90;

$pdf = new FPDF();
$pdf->AddPage();

// Franja superior de color
$pdf->SetFillColor($violetaR, $violetaG, $violetaB);
$pdf->Rect(0, 0, 210, 35, 'F');

// Logo (si existe en ese formato; FPDF soporta JPG, PNG y GIF)
$logoPath = __DIR__ . '/../../public/img/tuyasbarber.jpeg';
if (file_exists($logoPath)) {
    $pdf->Image($logoPath, 15, 8, 20, 20);
}

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetXY(40, 12);
$pdf->Cell(0, 10, "Tuya's Barber", 0, 1);

$pdf->SetFont('Arial', '', 11);
$pdf->SetXY(40, 22);
$pdf->Cell(0, 8, 'Comprobante de reserva', 0, 1);

// Vuelvo a texto normal para el resto del documento
$pdf->SetTextColor(30, 30, 30);
$pdf->SetY(45);

function filaComprobante($pdf, $label, $valor, $grisR, $grisG, $grisB)
{
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($grisR, $grisG, $grisB);
    $pdf->Cell(55, 9, $label, 0, 0);

    $pdf->SetFont('Arial', '', 11);
    $pdf->SetTextColor(20, 20, 20);
    $pdf->Cell(0, 9, $valor, 0, 1);
}

filaComprobante($pdf, 'Codigo de reserva:', $comprobante['codigo'], $grisR, $grisG, $grisB);
filaComprobante($pdf, 'Cliente:', $comprobante['nombre'], $grisR, $grisG, $grisB);
filaComprobante($pdf, 'Corte:', $comprobante['corte'], $grisR, $grisG, $grisB);
filaComprobante($pdf, 'Fecha:', $comprobante['fecha_legible'], $grisR, $grisG, $grisB);
filaComprobante($pdf, 'Hora:', $comprobante['hora'] . ' hs', $grisR, $grisG, $grisB);
filaComprobante($pdf, 'Metodo de pago:', ucfirst($comprobante['metodo_pago']), $grisR, $grisG, $grisB);

$pdf->Ln(2);

// Línea divisoria antes del monto
$pdf->SetDrawColor($violetaR, $violetaG, $violetaB);
$pdf->SetLineWidth(0.5);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->Ln(4);

$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor($violetaR, $violetaG, $violetaB);
$pdf->Cell(55, 10, 'Monto total:', 0, 0);
$pdf->Cell(0, 10, '$' . number_format($comprobante['monto'], 0, ',', '.'), 0, 1);

if (!empty($comprobante['notas'])) {
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetTextColor($grisR, $grisG, $grisB);
    $pdf->Cell(0, 8, 'Notas:', 0, 1);
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(20, 20, 20);
    $pdf->MultiCell(0, 6, $comprobante['notas']);
}

// Pie de página
$pdf->SetY(-25);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(140, 140, 140);
$pdf->Cell(0, 10, "Gracias por elegir Tuya's Barber - Presenta este comprobante el dia de tu turno", 0, 0, 'C');

$pdf->Output('D', 'comprobante_' . $comprobante['codigo'] . '.pdf');