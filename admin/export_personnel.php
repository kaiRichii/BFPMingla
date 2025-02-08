<?php
require '../vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use setasign\Fpdi\Fpdi;
require '../db_connection.php'; 

$action = $_GET['action'] ?? '';

$personnelIds = explode(',', $_GET['personnelIds']);
$placeholders = implode(',', array_fill(0, count($personnelIds), '?'));

if ($action === 'pdf') {
    exportToPDF($conn, $personnelIds, $placeholders);
} elseif ($action === 'excel') {
    exportToExcel($conn, $personnelIds, $placeholders);
} else {
    echo "Invalid action!";
    exit;
}

// function exportToPDF($conn, $personnelIds, $placeholders) {
//     $query = "SELECT rank, CONCAT(first_name, ' ', last_name) AS fullname, contact_number, designation FROM personnel WHERE id IN ($placeholders)";
//     $stmt = $conn->prepare($query);
//     $types = str_repeat('i', count($personnelIds)); 
//     $stmt->bind_param($types, ...$personnelIds); 
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $pdf = new FPDF('L', 'mm', 'A4'); 
//     $pdf->AddPage();

//     // Add the logo
//     $pdf->Image('../img/bfp3.png', 10, 6, 15); 
//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->Cell(0, 10, 'BFP Minglanilla Fire Station', 0, 1, 'C'); 

//     // Add a sub-title
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(0, 10, 'Personnel Report', 0, 1, 'C');

//     // Table header
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->SetFillColor(200, 200, 200); 
//     $headers = ['Rank', 'Fullname', 'Contact Number', 'Designation'];
//     $widths = [50, 50, 40, 140]; 
//     foreach ($headers as $index => $header) {
//         $pdf->Cell($widths[$index], 10, $header, 1, 0, 'C', true);
//     }
//     $pdf->Ln();

//     // Table data
//     $pdf->SetFont('Arial', '', 10);
//     while ($row = $result->fetch_assoc()) {
//         $pdf->Cell($widths[0], 10, $row['rank'], 1);
//         $pdf->Cell($widths[1], 10, $row['fullname'], 1);
//         $pdf->Cell($widths[2], 10, $row['contact_number'], 1);
//         $pdf->Cell($widths[3], 10, $row['designation'], 1);
//         $pdf->Ln();
//     }

//     // Output the PDF
//     $pdf->Output('D', 'Personnel_Report.pdf');
// }

function exportToPDF($conn, $personnelIds, $placeholders) {
    // Query to fetch personnel data
    $query = "SELECT rank, CONCAT(first_name, ' ', last_name) AS fullname, contact_number, designation FROM personnel WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $types = str_repeat('i', count($personnelIds)); 
    $stmt->bind_param($types, ...$personnelIds); 
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize PDF
    $pdf = new FPDF('L', 'mm', 'A4'); 
    $pdf->AddPage();

    // Add header content (logos and title)
    $pdf->Image('../img/bfp3.png', 75, 8, 25);  // Logo at top-left
    $pdf->Image('../img/Bureau_of_Fire_Protection.png', 195, 8, 25);  // Logo at top-right

    // Set header font and color
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(22, 53, 92);  // Color #16355C
    $pdf->Cell(0, 5, 'BUREAU OF FIRE PROTECTION', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0);  // Reset to black
    $pdf->Cell(0, 5, 'Province of Cebu', 0, 1, 'C');
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 5, 'Minglanilla Fire Station', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 5, 'Ward 2 Poblacion, Minglanilla, Cebu', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Tel no.: (032) 401-2943 / 273-2830', 0, 1, 'C');

    // Add some space after the header
    $pdf->Ln(10);

    // Personnel Report Title Section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(52, 152, 219);  // Blue for section header
    $pdf->SetTextColor(255, 255, 255);  // White text
    $pdf->Cell(0, 10, 'Personnel Report', 0, 1, 'C', true);  // Title centered
    $pdf->Ln(5);  // Space between title and table

    // Set column widths and headers for the report
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(52, 152, 219);  // Blue header background
    $pdf->SetTextColor(255, 255, 255);  // White text

    // Define column headers and widths
    $pdf->Cell(50, 10, 'Rank', 1, 0, 'C', true);
    $pdf->Cell(50, 10, 'Fullname', 1, 0, 'C', true);
    $pdf->Cell(40, 10, 'Contact Number', 1, 0, 'C', true);
    $pdf->Cell(140, 10, 'Designation', 1, 1, 'C', true);

    // Table Data Section
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0);  // Reset text color to black
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(50, 10, $row['rank'], 1);
        $pdf->Cell(50, 10, $row['fullname'], 1);
        $pdf->Cell(40, 10, $row['contact_number'], 1);
        $pdf->Cell(140, 10, $row['designation'], 1);
        $pdf->Ln();
    }

    // Output the PDF
    $pdf->Output('D', 'Personnel_Report.pdf');  // Force download
}


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

function exportToExcel($conn, $personnelIds, $placeholders) {
    // Query to fetch personnel data
    $query = "SELECT rank, CONCAT(first_name, ' ', last_name) AS fullname, contact_number, designation FROM personnel WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $types = str_repeat('i', count($personnelIds)); 
    $stmt->bind_param($types, ...$personnelIds); 
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Header Section
    $sheet->setCellValue('A1', 'Republic of the Philippines');
    $sheet->mergeCells('A1:D1');  // Merge across columns for centering
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A2', 'BUREAU OF FIRE PROTECTION');
    $sheet->mergeCells('A2:D2');  // Merge across columns for centering
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(18);  // Larger font size
    $sheet->getStyle('A2')->getFont()->getColor()->setRGB('16355C');  // Color #16355C
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A3', 'Province of Cebu');
    $sheet->mergeCells('A3:D3');  // Merge across columns for centering
    $sheet->getStyle('A3')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A4', 'Minglanilla Fire Station');
    $sheet->mergeCells('A4:D4');  // Merge across columns for centering
    $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);  // Larger font size
    $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A5', 'Ward 2 Poblacion, Minglanilla, Cebu');
    $sheet->mergeCells('A5:D5');  // Merge across columns for centering
    $sheet->getStyle('A5')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A6', 'Tel no.: (032) 401-2943 / 273-2830');
    $sheet->mergeCells('A6:D6');  // Merge across columns for centering
    $sheet->getStyle('A6')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Add space after header (increase row height)
    $sheet->getRowDimension(7)->setRowHeight(20);  // Increased row height

    // Personnel Report Title Section
    $sheet->setCellValue('A8', 'BFP Minglanilla Personnel');
    $sheet->mergeCells('A8:D8');  // Merge across columns for centering
    $sheet->getStyle('A8')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A8')->getFont()->getColor()->setRGB('FFFFFF'); // White text
    $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Set Column Titles for Personnel Report
    $sheet->setCellValue('A9', 'Rank');
    $sheet->setCellValue('B9', 'Fullname');
    $sheet->setCellValue('C9', 'Contact Number');
    $sheet->setCellValue('D9', 'Designation');

    // Apply Style to Header Row
    $sheet->getStyle('A9:D9')->getFont()->setBold(true)->setSize(14);  // Larger font size and bold
    $sheet->getStyle('A9:D9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);  // Center align text
    $sheet->getStyle('A9:D9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A9:D9')->getFont()->getColor()->setRGB('FFFFFF'); // White font color
    $sheet->getRowDimension(9)->setRowHeight(25);  // Increased row height for header

    // Add Data for Personnel
    $rowIndex = 10;  // Start from row 10
    while ($row = $result->fetch_assoc()) {
        // Set Data
        $sheet->setCellValue('A' . $rowIndex, $row['rank']);
        $sheet->setCellValue('B' . $rowIndex, $row['fullname']);
        $sheet->setCellValue('C' . $rowIndex, $row['contact_number']);
        $sheet->setCellValue('D' . $rowIndex, $row['designation']);
        
        // Apply Style to Data Rows
        $sheet->getStyle('A' . $rowIndex . ':D' . $rowIndex)->getFont()->setSize(14);  // Larger font size for data rows
        $sheet->getStyle('A' . $rowIndex . ':D' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);  // Left alignment for text
        $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height for data

        $rowIndex++;  // Move to the next row
    }

    // Set Column Width for Better Display
    $sheet->getColumnDimension('A')->setWidth(50);  // Adjusted width for Rank
    $sheet->getColumnDimension('B')->setWidth(50);  // Adjusted width for Fullname
    $sheet->getColumnDimension('C')->setWidth(50);  // Adjusted width for Contact Number
    $sheet->getColumnDimension('D')->setWidth(50);  // Adjusted width for Designation

    // Output the Excel file
    $writer = new Xlsx($spreadsheet);
    $fileName = 'Personnel_Report_Styled.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    $writer->save('php://output');  // Force download
}

// function exportToExcel($conn, $personnelIds, $placeholders) {
//     $query = "SELECT rank, CONCAT(first_name, ' ', last_name) AS fullname, contact_number, designation FROM personnel WHERE id IN ($placeholders)";
//     $stmt = $conn->prepare($query);
//     $types = str_repeat('i', count($personnelIds)); 
//     $stmt->bind_param($types, ...$personnelIds); 
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $spreadsheet = new Spreadsheet();
//     $sheet = $spreadsheet->getActiveSheet();

//     // Header
//     $sheet->setCellValue('A1', 'Rank');
//     $sheet->setCellValue('B1', 'Fullname');
//     $sheet->setCellValue('C1', 'Contact Number');
//     $sheet->setCellValue('D1', 'Designation');

//     // Data
//     $rowIndex = 2; // Start from the second row
//     while ($row = $result->fetch_assoc()) {
//         $sheet->setCellValue('A' . $rowIndex, $row['rank']);
//         $sheet->setCellValue('B' . $rowIndex, $row['fullname']);
//         $sheet->setCellValue('C' . $rowIndex, $row['contact_number']);
//         $sheet->setCellValue('D' . $rowIndex, $row['designation']);
//         $rowIndex++;
//     }

//     $writer = new Xlsx($spreadsheet);
//     $fileName = 'Personnel_Report.xlsx';
//     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//     header('Content-Disposition: attachment; filename="' . $fileName . '"');
//     $writer->save('php://output'); // Force download
// }
?>
