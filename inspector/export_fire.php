<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use setasign\Fpdi\Fpdi;
require '../db_connection.php';

$action = $_GET['action'] ?? '';
$fromDate = $_GET['from'] ?? '';
$toDate = $_GET['to'] ?? '';

$dateFilter = '';
if ($fromDate && $toDate) {
    $dateFilter = "WHERE incident_date BETWEEN '$fromDate' AND '$toDate'";
} elseif ($fromDate) {
    $dateFilter = "WHERE incident_date >= '$fromDate'";
} elseif ($toDate) {
    $dateFilter = "WHERE incident_date <= '$toDate'";
}

if ($action === 'pdf' || $action === 'view') {
    exportToPDF($conn, $action, $dateFilter);
} elseif ($action === 'excel') {
    exportToExcel($conn, $dateFilter);
} else {
    echo "Invalid action!";
    exit;
}

// function exportToPDF($conn, $action, $dateFilter) {
//     $pdf = new FPDF('P', 'mm', 'A4');
//     $pdf->AddPage();
//     $pdf->Image('../img/bfp3.png', 10, 6, 15);
//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->Cell(0, 10, 'BFP Minglanilla Fire Station', 0, 1, 'C');
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(0, 10, 'Incident Reports Summary', 0, 1, 'C');

//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->SetFillColor(200, 200, 200);
//     $pdf->Cell(190, 10, 'Incident Report Summary by Occupancy Type', 1, 1, 'C', true);
//     $pdf->SetFont('Arial', '', 10);

//     $pdf->Cell(95, 10, 'Occupancy Type', 1, 0, 'C');
//     $pdf->Cell(47.5, 10, 'Total Incidents', 1, 0, 'C');
//     $pdf->Cell(47.5, 10, 'Estimated Damages (PHP)', 1, 1, 'C');

//     $query = "SELECT occupancy_type, COUNT(*) AS total_incidents, 
//                      SUM(estimated_damages) AS total_damages 
//               FROM incident_reports 
//               $dateFilter 
//               GROUP BY occupancy_type";
//     $result = $conn->query($query);
//     $totalIncidents = 0;
//     $totalDamages = 0.00;

//     while ($row = $result->fetch_assoc()) {
//         $pdf->Cell(95, 10, $row['occupancy_type'], 1, 0, 'L');
//         $pdf->Cell(47.5, 10, $row['total_incidents'], 1, 0, 'C');
//         $pdf->Cell(47.5, 10, number_format($row['total_damages'], 2), 1, 1, 'R');
//         $totalIncidents += $row['total_incidents'];
//         $totalDamages += $row['total_damages'];
//     }

//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(95, 10, 'TOTAL', 1, 0, 'L', true);
//     $pdf->Cell(47.5, 10, $totalIncidents, 1, 0, 'C', true);
//     $pdf->Cell(47.5, 10, number_format($totalDamages, 2), 1, 1, 'R', true);

//     if ($action === 'pdf') {
//         $pdf->Output('D', 'Incident_Report_Summary.pdf');
//     } else {
//         $pdf->Output('I', 'Incident_Report_Summary.pdf');
//     }
// }

function exportToPDF($conn, $action, $dateFilter) {
    // Initialize PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // Add header content (logos and title)
    $pdf->Image('../img/bfp3.png', 32, 6, 25); // Logo at top-left
    $pdf->Image('../img/Bureau_of_Fire_Protection.png', 150, 6, 25); // Logo at top-right

    // Set header font and color
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(22, 53, 92); // Color #16355C
    $pdf->Cell(0, 5, 'BUREAU OF FIRE PROTECTION', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Reset to black
    $pdf->Cell(0, 5, 'Province of Cebu', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 5, 'Minglanilla Fire Station', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 5, 'Ward 2 Poblacion, Minglanilla, Cebu', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Tel no.: (032) 401-2943 / 273-2830', 0, 1, 'C');

    // Add some space after the header
    $pdf->Ln(10);

    // Incident Report Title Section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(52, 152, 219); // Blue for section header
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->Cell(0, 10, 'Incident Report Summary by Occupancy Type', 0, 1, 'C', true); // Title centered
    $pdf->Ln(5); // Space between title and table

    // Set column widths and headers for the report
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(52, 152, 219); // Blue header background
    $pdf->SetTextColor(255, 255, 255); // White text

    $pdf->Cell(95, 10, 'Occupancy Type', 1, 0, 'C', true);
    $pdf->Cell(47.5, 10, 'Total Incidents', 1, 0, 'C', true);
    $pdf->Cell(47.5, 10, 'Estimated Damages (PHP)', 1, 1, 'C', true);

    // Query to fetch incident report data
    $query = "SELECT occupancy_type, COUNT(*) AS total_incidents, 
                     SUM(estimated_damages) AS total_damages 
              FROM incident_reports 
              $dateFilter 
              GROUP BY occupancy_type";
    $result = $conn->query($query);

    // Initialize total variables
    $totalIncidents = 0;
    $totalDamages = 0.00;

    // Table Data Section
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Reset text color to black
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(95, 10, $row['occupancy_type'], 1, 0, 'L');
        $pdf->Cell(47.5, 10, $row['total_incidents'], 1, 0, 'C');
        $pdf->Cell(47.5, 10, number_format($row['total_damages'], 2), 1, 1, 'R');
        $totalIncidents += $row['total_incidents'];
        $totalDamages += $row['total_damages'];
    }

    // Summary Row (Total)
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(200, 200, 200); // Light gray background for totals
    $pdf->Cell(95, 10, 'TOTAL', 1, 0, 'L', true);
    $pdf->Cell(47.5, 10, $totalIncidents, 1, 0, 'C', true);
    $pdf->Cell(47.5, 10, number_format($totalDamages, 2), 1, 1, 'R', true);
   

    // Output the PDF
    if ($action === 'pdf') {
        $pdf->Output('D', 'Incident_Report_Summary.pdf'); // Force download
    } else {
        $pdf->Output('I', 'Incident_Report_Summary.pdf'); // Inline view
    }
}


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

function exportToExcel($conn, $dateFilter) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set title and font style for the sheet with increased font size
    $sheet->setCellValue('A1', 'Republic of the Philippines');
    $sheet->mergeCells('A1:C1');  // Merge across columns for centering
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A2', 'BUREAU OF FIRE PROTECTION');
    $sheet->mergeCells('A2:C2');  // Merge across columns for centering
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(18);  // Larger font size
    $sheet->getStyle('A2')->getFont()->getColor()->setRGB('16355C');  // Color #16355C
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A3', 'Province of Cebu');
    $sheet->mergeCells('A3:C3');  // Merge across columns for centering
    $sheet->getStyle('A3')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A4', 'Minglanilla Fire Station');
    $sheet->mergeCells('A4:C4');  // Merge across columns for centering
    $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);  // Larger font size
    $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A5', 'Ward 2 Poblacion, Minglanilla, Cebu');
    $sheet->mergeCells('A5:C5');  // Merge across columns for centering
    $sheet->getStyle('A5')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A6', 'Tel no.: (032) 401-2943 / 273-2830');
    $sheet->mergeCells('A6:C6');  // Merge across columns for centering
    $sheet->getStyle('A6')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Add space after header (increase row height)
    $sheet->getRowDimension(7)->setRowHeight(20);  // Increased row height

    // Incident Report Title Section
    $sheet->setCellValue('A7', 'Incident Report Summary by Occupancy Type');
    $sheet->mergeCells('A7:C7');  // Merge across columns for centering
    $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A7')->getFont()->getColor()->setRGB('FFFFFF'); // White text
    $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Set Column Titles for Summary Report
    $sheet->setCellValue('A8', 'Occupancy Type');
    $sheet->setCellValue('B8', 'Total Incidents');
    $sheet->setCellValue('C8', 'Estimated Damages (PHP)');

    // Apply Style to Header Row
    $sheet->getStyle('A8:C8')->getFont()->setBold(true)->setSize(14);  // Larger font size and bold
    $sheet->getStyle('A8:C8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);  // Center align text
    $sheet->getStyle('A8:C8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A8:C8')->getFont()->getColor()->setRGB('FFFFFF'); // White font color
    $sheet->getRowDimension(8)->setRowHeight(25);  // Increased row height for header

    // Query for Incident Reports Summary by Occupancy Type
    $query = "SELECT occupancy_type, COUNT(*) AS total_incidents, 
                     SUM(estimated_damages) AS total_damages 
              FROM incident_reports 
              $dateFilter 
              GROUP BY occupancy_type";
    $result = $conn->query($query);

    $rowIndex = 9;
    $totalIncidents = 0;
    $totalDamages = 0.00;

    while ($row = $result->fetch_assoc()) {
        // Set Data for each row
        $sheet->setCellValue('A' . $rowIndex, $row['occupancy_type']);
        $sheet->setCellValue('B' . $rowIndex, $row['total_incidents']);
        $sheet->setCellValue('C' . $rowIndex, number_format($row['total_damages'], 2));

        // Apply Style to Data Rows
        $sheet->getStyle('A' . $rowIndex . ':C' . $rowIndex)->getFont()->setSize(14);  // Larger font size for data rows
        $sheet->getStyle('A' . $rowIndex . ':C' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);  // Left alignment for text
        $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height for data

        // Update total values
        $totalIncidents += $row['total_incidents'];
        $totalDamages += $row['total_damages'];
        $rowIndex++;  // Move to the next row
    }

    // Set Total Row
    $sheet->setCellValue('A' . $rowIndex, 'TOTAL');
    $sheet->setCellValue('B' . $rowIndex, $totalIncidents);
    $sheet->setCellValue('C' . $rowIndex, number_format($totalDamages, 2));
    $sheet->getStyle('A' . $rowIndex . ':C' . $rowIndex)->getFont()->setBold(true);

    // Set Column Width for Better Display
    $sheet->getColumnDimension('A')->setWidth(50);  // Adjusted width for Occupancy Type
    $sheet->getColumnDimension('B')->setWidth(50);  // Adjusted width for Total Incidents
    $sheet->getColumnDimension('C')->setWidth(50);  // Adjusted width for Estimated Damages

    // Output the Excel file
    $writer = new Xlsx($spreadsheet);
    $fileName = 'Incident_Report_Summary_Styled.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    $writer->save('php://output');  // Force download
    exit;
}

// function exportToExcel($conn, $dateFilter) {
//     $spreadsheet = new Spreadsheet();
//     $sheet = $spreadsheet->getActiveSheet();

//     $sheet->setCellValue('A1', 'BFP Minglanilla Fire Station');
//     $sheet->setCellValue('A2', 'Incident Reports Summary');
//     $sheet->mergeCells('A1:C1');

//     $sheet->setCellValue('A4', 'Occupancy Type');
//     $sheet->setCellValue('B4', 'Total Incidents');
//     $sheet->setCellValue('C4', 'Estimated Damages (PHP)');
//     $sheet->getStyle('A4:C4')->getFont()->setBold(true);

//     $query = "SELECT occupancy_type, COUNT(*) AS total_incidents, 
//                      SUM(estimated_damages) AS total_damages 
//               FROM incident_reports 
//               $dateFilter 
//               GROUP BY occupancy_type";
//     $result = $conn->query($query);

//     $rowIndex = 5;
//     $totalIncidents = 0;
//     $totalDamages = 0.00;

//     while ($row = $result->fetch_assoc()) {
//         $sheet->setCellValue('A' . $rowIndex, $row['occupancy_type']);
//         $sheet->setCellValue('B' . $rowIndex, $row['total_incidents']);
//         $sheet->setCellValue('C' . $rowIndex, number_format($row['total_damages'], 2));
//         $totalIncidents += $row['total_incidents'];
//         $totalDamages += $row['total_damages'];
//         $rowIndex++;
//     }

//     $sheet->setCellValue('A' . $rowIndex, 'TOTAL');
//     $sheet->setCellValue('B' . $rowIndex, $totalIncidents);
//     $sheet->setCellValue('C' . $rowIndex, number_format($totalDamages, 2));
//     $sheet->getStyle('A' . $rowIndex . ':C' . $rowIndex)->getFont()->setBold(true);

//     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//     header('Content-Disposition: attachment;filename="Incident_Report_Summary.xlsx"');
//     header('Cache-Control: max-age=0');

//     $writer = new Xlsx($spreadsheet);
//     $writer->save('php://output');
//     exit;
// }
?>
