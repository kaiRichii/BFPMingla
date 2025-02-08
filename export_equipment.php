<?php
require './vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use setasign\Fpdi\Fpdi;
require 'db_connection.php'; // Database connection

$action = $_GET['action'] ?? '';

$equipmentIds = explode(',', $_GET['equipmentIds']);
$placeholders = implode(',', array_fill(0, count($equipmentIds), '?'));

if ($action === 'pdf') {
    exportToPDF($conn, $equipmentIds, $placeholders);
} elseif ($action === 'excel') {
    exportToExcel($conn, $equipmentIds, $placeholders);
} else {
    echo "Invalid action!";
    exit;
}

function exportToPDF($conn, $equipmentIds, $placeholders) {
    // Query to fetch equipment data
    $query = "SELECT equipment_name, quantity, last_maintenance_date, next_maintenance_date, status, notes FROM equipment WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $types = str_repeat('i', count($equipmentIds)); 
    $stmt->bind_param($types, ...$equipmentIds); 
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize PDF
    $pdf = new FPDF('L', 'mm', 'A4'); // Landscape orientation
    $pdf->AddPage();
    
    // Add header content (logos and title)
    $pdf->Image('./img/bfp3.png', 68, 8, 25); // Logo at top-left, width 15
    $pdf->Image('./img/Bureau_of_Fire_Protection.png', 200, 8, 25); // Logo at top-right, width 15

    // Set header font and color
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C'); // Font 11
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetTextColor(22, 53, 92); // Color #16355C
    $pdf->Cell(0, 5, 'BUREAU OF FIRE PROTECTION', 0, 1, 'C'); // Font 14, color #16355C
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Reset text color to black
    $pdf->Cell(0, 5, 'Province of Cebu', 0, 1, 'C'); // Font 12
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 5, 'Minglanilla Fire Station', 0, 1, 'C'); // Font 12, bold
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 5, 'Ward 2 Poblacion, Minglanilla, Cebu', 0, 1, 'C'); // Font 12
    $pdf->Cell(0, 5, 'Tel no.: (032) 401-2943 / 273-2830', 0, 1, 'C'); // Font 12

    // Add some space after the header
    $pdf->Ln(10);

    // Equipment Maintenance Report Title Section
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(52, 152, 219); // Blue for section header
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->Cell(0, 10, 'Equipment Maintenance', 0, 1, 'C', true); // Title centered
    $pdf->Ln(5); // Space between title and table

    // Fetch and calculate maximum column widths
    $data = [];
    $maxWidths = [40, 40, 40, 40, 40, 60]; // Column widths (adjusted for notes)
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
        $maxWidths[0] = max($maxWidths[0], $pdf->GetStringWidth($row['equipment_name']));
        $maxWidths[1] = max($maxWidths[1], $pdf->GetStringWidth($row['quantity']));
        $maxWidths[2] = max($maxWidths[2], $pdf->GetStringWidth($row['last_maintenance_date']));
        $maxWidths[3] = max($maxWidths[3], $pdf->GetStringWidth($row['next_maintenance_date']));
        $maxWidths[4] = max($maxWidths[4], $pdf->GetStringWidth($row['status']));
        $maxWidths[5] = max($maxWidths[5], $pdf->GetStringWidth($row['notes']));
    }

    // Add some padding to the widths for readability
    foreach ($maxWidths as &$width) {
        $width += 10;
    }

    // Ensure the table fits within the page
    $pageWidth = 280; // A4 landscape printable width
    $totalWidth = array_sum($maxWidths);
    if ($totalWidth > $pageWidth) {
        $scaleFactor = $pageWidth / $totalWidth;
        foreach ($maxWidths as &$width) {
            $width *= $scaleFactor;
        }
    }

    // Table Header Section
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetFillColor(52, 152, 219); // Blue header background
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->Cell($maxWidths[0], 10, 'Equipment Name', 1, 0, 'C', true);
    $pdf->Cell($maxWidths[1], 10, 'Quantity', 1, 0, 'C', true);
    $pdf->Cell($maxWidths[2], 10, 'Last Maintenance', 1, 0, 'C', true);
    $pdf->Cell($maxWidths[3], 10, 'Next Maintenance', 1, 0, 'C', true);
    $pdf->Cell($maxWidths[4], 10, 'Status', 1, 0, 'C', true);
    $pdf->Cell($maxWidths[5], 10, 'Notes', 1, 1, 'C', true);

    // Table Data Section
    $pdf->SetFont('Arial', '', 10);
    $pdf->SetTextColor(0, 0, 0); // Reset text color to black
    foreach ($data as $row) {
        $pdf->Cell($maxWidths[0], 10, $row['equipment_name'], 1);
        $pdf->Cell($maxWidths[1], 10, $row['quantity'], 1, 0, 'C');
        $pdf->Cell($maxWidths[2], 10, $row['last_maintenance_date'], 1, 0, 'C');
        $pdf->Cell($maxWidths[3], 10, $row['next_maintenance_date'], 1, 0, 'C');
        $pdf->Cell($maxWidths[4], 10, $row['status'], 1, 0, 'C');
        $pdf->Cell($maxWidths[5], 10, $row['notes'], 1);
        $pdf->Ln();
    }

    // Add Footer (page number, contact info)
    $pdf->SetY(-15); // Position at the bottom of the page
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, 'For inquiries, contact (032) 401-2943 / 273-2830', 0, 1, 'C');
    $pdf->Cell(0, 5, 'Page ' . $pdf->PageNo(), 0, 0, 'C');
    $pdf->Ln(5); // Space after page number

    // Output the PDF
    $pdf->Output('D', 'Equipment_Report.pdf'); // Force download
}

// function exportToPDF($conn, $equipmentIds, $placeholders) {
//     $query = "SELECT equipment_name, quantity, last_maintenance_date, next_maintenance_date, status, notes FROM equipment WHERE id IN ($placeholders)";
//     $stmt = $conn->prepare($query);
//     $types = str_repeat('i', count($equipmentIds)); 
//     $stmt->bind_param($types, ...$equipmentIds); 
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $pdf = new FPDF('L', 'mm', 'A4'); // Landscape orientation
//     $pdf->AddPage();
    
//     // Add the logo
//     $pdf->Image('./img/bfp3.png', 10, 6, 15); // Logo at top-left, width 20
//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->Cell(0, 10, 'BFP Minglanilla Fire Station', 0, 1, 'C'); // Title centered

//     // Add a sub-title
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->Cell(0, 10, 'Equipment Report', 0, 1, 'C');

//     // Fetch data for width calculation
//     $data = [];
//     $maxWidths = [40, 40, 40, 40, 40, 40]; // Initialize max widths for each column
//     while ($row = $result->fetch_assoc()) {
//         $data[] = $row;
//         $maxWidths[0] = max($maxWidths[0], $pdf->GetStringWidth($row['equipment_name']));
//         $maxWidths[1] = max($maxWidths[1], $pdf->GetStringWidth($row['quantity']));
//         $maxWidths[2] = max($maxWidths[2], $pdf->GetStringWidth($row['last_maintenance_date']));
//         $maxWidths[3] = max($maxWidths[3], $pdf->GetStringWidth($row['next_maintenance_date']));
//         $maxWidths[4] = max($maxWidths[4], $pdf->GetStringWidth($row['status']));
//         $maxWidths[5] = max($maxWidths[5], $pdf->GetStringWidth($row['notes']));
//     }

//     // Add some padding to the widths
//     foreach ($maxWidths as &$width) {
//         $width += 10; // Add padding for readability
//     }

//     // Ensure total width fits the page
//     $pageWidth = 280; // A4 landscape printable width
//     $totalWidth = array_sum($maxWidths);
//     if ($totalWidth > $pageWidth) {
//         $scaleFactor = $pageWidth / $totalWidth;
//         foreach ($maxWidths as &$width) {
//             $width *= $scaleFactor;
//         }
//     }

//     // Table header
//     $pdf->SetFont('Arial', 'B', 10);
//     $pdf->SetFillColor(200, 200, 200); // Light gray background for header
//     $pdf->Cell($maxWidths[0], 10, 'Equipment Name', 1, 0, 'C', true);
//     $pdf->Cell($maxWidths[1], 10, 'Quantity', 1, 0, 'C', true);
//     $pdf->Cell($maxWidths[2], 10, 'Last Maintenance', 1, 0, 'C', true);
//     $pdf->Cell($maxWidths[3], 10, 'Next Maintenance', 1, 0, 'C', true);
//     $pdf->Cell($maxWidths[4], 10, 'Status', 1, 0, 'C', true);
//     $pdf->Cell($maxWidths[5], 10, 'Notes', 1, 1, 'C', true);

//     // Table data
//     $pdf->SetFont('Arial', '', 10);
//     foreach ($data as $row) {
//         $pdf->Cell($maxWidths[0], 10, $row['equipment_name'], 1);
//         $pdf->Cell($maxWidths[1], 10, $row['quantity'], 1, 0, 'C');
//         $pdf->Cell($maxWidths[2], 10, $row['last_maintenance_date'], 1, 0, 'C');
//         $pdf->Cell($maxWidths[3], 10, $row['next_maintenance_date'], 1, 0, 'C');
//         $pdf->Cell($maxWidths[4], 10, $row['status'], 1, 0, 'C');
//         $pdf->Cell($maxWidths[5], 10, $row['notes'], 1);
//         $pdf->Ln();
//     }

//     // Output the PDF
//     $pdf->Output('D', 'Equipment_Report.pdf'); // Force download
// }



// function exportToExcel($conn, $equipmentIds, $placeholders) {
//     $query = "SELECT equipment_name, quantity, last_maintenance_date, next_maintenance_date, status, notes FROM equipment WHERE id IN ($placeholders)";
//     $stmt = $conn->prepare($query);
//     $types = str_repeat('i', count($equipmentIds)); 
//     $stmt->bind_param($types, ...$equipmentIds); 
//     $stmt->execute();
//     $result = $stmt->get_result();

//     $spreadsheet = new Spreadsheet();
//     $sheet = $spreadsheet->getActiveSheet();

//     // Header
//     $sheet->setCellValue('A1', 'Equipment Name');
//     $sheet->setCellValue('B1', 'Quantity');
//     $sheet->setCellValue('C1', 'Last Maintenance');
//     $sheet->setCellValue('D1', 'Next Maintenance');
//     $sheet->setCellValue('E1', 'Status');
//     $sheet->setCellValue('F1', 'Notes');

//     // Data
//     $rowIndex = 2; // Start from the second row
//     while ($row = $result->fetch_assoc()) {
//         $sheet->setCellValue('A' . $rowIndex, $row['equipment_name']);
//         $sheet->setCellValue('B' . $rowIndex, $row['quantity']);
//         $sheet->setCellValue('C' . $rowIndex, $row['last_maintenance_date']);
//         $sheet->setCellValue('D' . $rowIndex, $row['next_maintenance_date']);
//         $sheet->setCellValue('E' . $rowIndex, $row['status']);
//         $sheet->setCellValue('F' . $rowIndex, $row['notes']);
//         $rowIndex++;
//     }

//     $writer = new Xlsx($spreadsheet);
//     $fileName = 'Equipment_Report.xlsx';
//     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//     header('Content-Disposition: attachment; filename="' . $fileName . '"');
//     $writer->save('php://output'); // Force download
// }
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

function exportToExcel($conn, $equipmentIds, $placeholders) {
    $query = "SELECT equipment_name, quantity, last_maintenance_date, next_maintenance_date, status, notes FROM equipment WHERE id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $types = str_repeat('i', count($equipmentIds)); 
    $stmt->bind_param($types, ...$equipmentIds); 
    $stmt->execute();
    $result = $stmt->get_result();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set title and font style for the sheet with increased font size
    $sheet->setCellValue('A1', 'Republic of the Philippines');
    $sheet->mergeCells('A1:F1');  // Merge across columns for centering
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A2', 'BUREAU OF FIRE PROTECTION');
    $sheet->mergeCells('A2:F2');  // Merge across columns for centering
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(18);  // Larger font size
    $sheet->getStyle('A2')->getFont()->getColor()->setRGB('16355C');  // Color #16355C
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A3', 'Province of Cebu');
    $sheet->mergeCells('A3:F3');  // Merge across columns for centering
    $sheet->getStyle('A3')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A4', 'Minglanilla Fire Station');
    $sheet->mergeCells('A4:F4');  // Merge across columns for centering
    $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);  // Larger font size
    $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A5', 'Ward 2 Poblacion, Minglanilla, Cebu');
    $sheet->mergeCells('A5:F5');  // Merge across columns for centering
    $sheet->getStyle('A5')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A6', 'Tel no.: (032) 401-2943 / 273-2830');
    $sheet->mergeCells('A6:F6');  // Merge across columns for centering
    $sheet->getStyle('A6')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Add space after header (increase row height)
    $sheet->getRowDimension(7)->setRowHeight(20);  // Increased row height

    // Header Section for Equipment Information (same as previous example)
    $sheet->setCellValue('A8', 'Firefighting Equipment');
    $sheet->mergeCells('A8:F8');  // Merge across columns for centering
    $sheet->getStyle('A8')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A8')->getFont()->getColor()->setRGB('FFFFFF'); // White text
    $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Set Column Titles for Equipment Report
    $sheet->setCellValue('A9', 'Equipment Name');
    $sheet->setCellValue('B9', 'Quantity');
    $sheet->setCellValue('C9', 'Last Maintenance');
    $sheet->setCellValue('D9', 'Next Maintenance');
    $sheet->setCellValue('E9', 'Status');
    $sheet->setCellValue('F9', 'Notes');

    // Apply Style to Header Row
    $sheet->getStyle('A9:F9')->getFont()->setBold(true)->setSize(14);  // Larger font size and bold
    $sheet->getStyle('A9:F9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);  // Center align text
    $sheet->getStyle('A9:F9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A9:F9')->getFont()->getColor()->setRGB('FFFFFF'); // White font color
    $sheet->getRowDimension(9)->setRowHeight(25);  // Increased row height for header

    // Add Data for Equipment
    $rowIndex = 10;  // Start from row 10
    while ($row = $result->fetch_assoc()) {
        // Set Data
        $sheet->setCellValue('A' . $rowIndex, $row['equipment_name']);
        $sheet->setCellValue('B' . $rowIndex, $row['quantity']);
        $sheet->setCellValue('C' . $rowIndex, $row['last_maintenance_date']);
        $sheet->setCellValue('D' . $rowIndex, $row['next_maintenance_date']);
        $sheet->setCellValue('E' . $rowIndex, $row['status']);
        $sheet->setCellValue('F' . $rowIndex, $row['notes']);
        
        // Apply Style to Data Rows
        $sheet->getStyle('A' . $rowIndex . ':F' . $rowIndex)->getFont()->setSize(14);  // Larger font size for data rows
        $sheet->getStyle('A' . $rowIndex . ':F' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);  // Left alignment for text
        $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height for data

        $rowIndex++;  // Move to the next row
    }

    // Set Column Width for Better Display
    $sheet->getColumnDimension('A')->setWidth(30);  // Adjusted width for Equipment Name
    $sheet->getColumnDimension('B')->setWidth(20);  // Adjusted width for Quantity
    $sheet->getColumnDimension('C')->setWidth(25);  // Adjusted width for Last Maintenance Date
    $sheet->getColumnDimension('D')->setWidth(25);  // Adjusted width for Next Maintenance Date
    $sheet->getColumnDimension('E')->setWidth(20);  // Adjusted width for Status
    $sheet->getColumnDimension('F')->setWidth(40);  // Adjusted width for Notes

    // Output the Excel file
    $writer = new Xlsx($spreadsheet);
    $fileName = 'Equipment_Report_Styled.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    $writer->save('php://output');  // Force download
}

?>
