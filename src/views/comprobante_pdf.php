<?php
require_once __DIR__ . '/../../database/conexcion_BD.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../libs/fpdf/fpdf.php';

$comprobante = $_SESSION['flow_comprobante'] ?? null;

if (empty($comprobante)) {
    header('Location: index.php');
    exit;
}

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 12, "Tuya's Barber", 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, 'Comprobante de reserva', 0, 1, 'C');
$pdf->Ln(6);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Codigo:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, $comprobante['codigo'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Cliente:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, $comprobante['nombre'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Corte:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, $comprobante['corte'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Fecha:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, $comprobante['fecha_legible'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Hora:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, $comprobante['hora'] . ' hs', 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Metodo de pago:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, ucfirst($comprobante['metodo_pago']), 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 8, 'Monto:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 8, '$' . number_format($comprobante['monto'], 0, ',', '.'), 0, 1);

if (!empty($comprobante['notas'])) {
    $pdf->Ln(4);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Notas:', 0, 1);
    $pdf->SetFont('Arial', '', 11);
    $pdf->MultiCell(0, 6, $comprobante['notas']);
}

$pdf->Output('D', 'comprobante_' . $comprobante['codigo'] . '.pdf');