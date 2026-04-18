<?php
require('fpdf.php'); // Make sure fpdf.php is in your folder
include 'db.php';
session_start();

if(!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$uid = $_SESSION['user_id'];

// Fetch User Data
$user_q = mysqli_query($conn, "SELECT username FROM users WHERE id='$uid'");
$user = mysqli_fetch_assoc($user_q);
$name = strtoupper($user['username']);

// Create PDF Instance (L = Landscape)
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Draw a Border
$pdf->SetLineWidth(2);
$pdf->Rect(10, 10, 277, 190); 

// --- Content ---
$pdf->SetFont('Arial', 'B', 30);
$pdf->Cell(0, 40, 'CERTIFICATE OF COMPLETION', 0, 1, 'C');

$pdf->SetFont('Arial', '', 18);
$pdf->Cell(0, 10, 'This is to certify that', 0, 1, 'C');

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 35);
$pdf->Cell(0, 20, $name, 0, 1, 'C'); // Dynamic Student Name

$pdf->Ln(10);
$pdf->SetFont('Arial', '', 18);
$pdf->Cell(0, 10, 'has successfully completed the', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 22);
$pdf->Cell(0, 15, 'CODEQUEST LEARNING PATHWAY', 0, 1, 'C');

$pdf->Ln(20);
$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 10, 'Issued on: ' . date('M d, Y'), 0, 1, 'C');

// Output PDF to Browser
$pdf->Output('I', 'Certificate_'.$name.'.pdf');
?>
