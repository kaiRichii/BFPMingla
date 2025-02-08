<?php
require './vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use setasign\Fpdi\Fpdi;
require './db_connection.php'; 

$action = $_GET['action'] ?? '';

if ($action === 'pdf' || $action === 'view') {
    exportToPDF($conn, $action);
} elseif ($action === 'excel') {
    exportToExcel($conn);
} else {
    echo "Invalid action!";
    exit;
}

// having problems:
// function fetchBuildingData($conn) {
//     $data = [];

//     // Building applications received this month
//     // $data['applications_received'] = $conn->query("
//     //     SELECT COUNT(*) AS total 
//     //     FROM applications 
//     //     WHERE type = 0 AND MONTH(created_at) = MONTH(CURRENT_DATE())
//     // ")->fetch_assoc()['total'];
//     $result = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE type = 0");
//     $data['applications_received'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

//     // Total inspections conducted this month
//     $data['inspected'] = $conn->query("
//         SELECT COUNT(*) AS total 
//         FROM inspections 
//         WHERE application_id IN (SELECT id FROM applications WHERE type = 0)
//           AND MONTH(created_at) = MONTH(CURRENT_DATE())
//     ")->fetch_assoc()['total'];

//     // FSEC issuance data
//     $data['fsec_current'] = $conn->query("
//         SELECT COUNT(*) AS total 
//         FROM issuance 
//         WHERE application_id IN (SELECT id FROM applications WHERE type = 0)
//           AND MONTH(created_at) = MONTH(CURRENT_DATE())
//     ")->fetch_assoc()['total'];

//     $data['fsec_previous'] = $conn->query("
//         SELECT COUNT(*) AS total 
//         FROM issuance 
//         WHERE application_id IN (SELECT id FROM applications WHERE type = 0)
//           AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
//     ")->fetch_assoc()['total'];

//     $data['fsec_total'] = $conn->query("
//         SELECT COUNT(*) AS total 
//         FROM issuance 
//         WHERE application_id IN (SELECT id FROM applications WHERE type = 0)
//     ")->fetch_assoc()['total'];

//     return $data;
// }

// function fetchOccupancyData($conn) {
//     $data = [];

//     // Occupancy applications received this month
//     // $data['applications_received'] = $conn->query("
//     //     SELECT COUNT(*) AS total 
//     //     FROM applications 
//     //     WHERE type = 1 AND MONTH(created_at) = MONTH(CURRENT_DATE())
//     // ")->fetch_assoc()['total'];

//      // Occupancy applications received
//      $result = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE type = 1");
//      $data['applications_received'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

//     // FSIC issuance data
//     $data['fsic_current'] = $conn->query("
//         SELECT COUNT(*) AS total 
//         FROM issuance 
//         WHERE application_id IN (SELECT id FROM applications WHERE type = 1)
//           AND MONTH(created_at) = MONTH(CURRENT_DATE())
//     ")->fetch_assoc()['total'];

//     $data['fsic_previous'] = $conn->query("
//         SELECT COUNT(*) AS total 
//         FROM issuance 
//         WHERE application_id IN (SELECT id FROM applications WHERE type = 1)
//           AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)
//     ")->fetch_assoc()['total'];

//     $data['fsic_total'] = $conn->query("
//         SELECT COUNT(*) AS total 
//         FROM issuance 
//         WHERE application_id IN (SELECT id FROM applications WHERE type = 1)
//     ")->fetch_assoc()['total'];

//     return $data;
// }

// i switched to this:
    function fetchBuildingData($conn) {
    $data = [];

    // Building applications received
    $result = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE type = 0");
    $data['applications_received'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

    // Total inspections conducted
    $result = $conn->query("SELECT COUNT(*) AS total FROM inspections WHERE application_id IN (SELECT id FROM applications WHERE type = 0)");
    $data['inspected'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

    // FSEC issuance (current month)
    $result = $conn->query("SELECT COUNT(*) AS total FROM issuance WHERE application_id IN (SELECT id FROM applications WHERE type = 0)");
    $data['fsec_current'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

    // FSEC issuance (previous month)
    $result = $conn->query("SELECT COUNT(*) AS total FROM issuance WHERE application_id IN (SELECT id FROM applications WHERE type = 0) AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)");
    $data['fsec_previous'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

    // Total FSEC issuance
    $result = $conn->query("SELECT COUNT(*) AS total FROM issuance WHERE application_id IN (SELECT id FROM applications WHERE type = 0)");
    $data['fsec_total'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

    return $data;
}

function fetchOccupancyData($conn) {
    $data = [];

    // Occupancy applications received
    $result = $conn->query("SELECT COUNT(*) AS total FROM applications WHERE type = 1");
    $data['applications_received'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

    // FSIC issuance (current month)
    $result = $conn->query("SELECT COUNT(*) AS total FROM issuance WHERE application_id IN (SELECT id FROM applications WHERE type = 1)");
    $data['fsic_current'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

    // FSIC issuance (previous month)
    $result = $conn->query("SELECT COUNT(*) AS total FROM issuance WHERE application_id IN (SELECT id FROM applications WHERE type = 1) AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)");
    $data['fsic_previous'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

    // Total FSIC issuance
    $result = $conn->query("SELECT COUNT(*) AS total FROM issuance WHERE application_id IN (SELECT id FROM applications WHERE type = 1)");
    $data['fsic_total'] = ($result && $row = $result->fetch_assoc()) ? $row['total'] : 0;

    return $data;
}

function exportToPDF($conn, $action) {
    $buildingData = fetchBuildingData($conn);
    $occupancyData = fetchOccupancyData($conn);

    // Initialize PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

    // Set font for header sections with specific sizes and colors
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

    // Logos - resizing to 8mm as per your request
    $pdf->Image('./img/bfp3.png', 30, 8, 25); // Logo 1 resized to 8mm
    $pdf->Image('./img/Bureau_of_Fire_Protection.png', 155, 8, 25); // Logo 2 resized to 8mm

    // Add space after logos
    $pdf->Ln(8); // Adjust space after logos

    // Building Information Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(52, 152, 219); // Blue for section header
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->Cell(0, 10, 'Building Information', 0, 1, 'C', true);
    $pdf->Ln(5);

    // Table Data - Building Section
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Reset text color to black
    $pdf->Cell(95, 10, 'Total Applications Received This Month', 1, 0, 'L', false);
    $pdf->Cell(95, 10, $buildingData['applications_received'], 1, 1, 'C');

    $pdf->Cell(95, 10, 'Total Inspections Conducted', 1, 0, 'L', false);
    $pdf->Cell(95, 10, $buildingData['inspected'], 1, 1, 'C');

    $pdf->Cell(95, 10, 'Number of Issued FSEC - Current Month', 1, 0, 'L', false);
    $pdf->Cell(95, 10, $buildingData['fsec_current'], 1, 1, 'C');

    $pdf->Cell(95, 10, 'Number of Issued FSEC - Previous Months', 1, 0, 'L', false);
    $pdf->Cell(95, 10, $buildingData['fsec_previous'], 1, 1, 'C');

    $pdf->Cell(95, 10, 'Total Number of Issued FSEC', 1, 0, 'L', false);
    $pdf->Cell(95, 10, $buildingData['fsec_total'], 1, 1, 'C');

    // Add space between sections
    $pdf->Ln(10);

    // Occupancy Information Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->SetFillColor(52, 152, 219); // Blue for section header
    $pdf->SetTextColor(255, 255, 255); // White text
    $pdf->Cell(0, 10, 'Occupancy Information', 0, 1, 'C', true);
    $pdf->Ln(5);

    // Table Data - Occupancy Section
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Reset text color to black
    $pdf->Cell(95, 10, 'Total Applications Received This Month', 1, 0, 'L', false);
    $pdf->Cell(95, 10, $occupancyData['applications_received'], 1, 1, 'C');

    $pdf->Cell(95, 10, 'Number of Issued FSIC - Current Month', 1, 0, 'L', false);
    $pdf->Cell(95, 10, $occupancyData['fsic_current'], 1, 1, 'C');

    $pdf->Cell(95, 10, 'Number of Issued FSIC - Previous Months', 1, 0, 'L', false);
    $pdf->Cell(95, 10, $occupancyData['fsic_previous'], 1, 1, 'C');

    $pdf->Cell(95, 10, 'Total Number of Issued FSIC', 1, 0, 'L', false);
    $pdf->Cell(95, 10, $occupancyData['fsic_total'], 1, 1, 'C');

    // Add space after the table
    $pdf->Ln(10);

    // Add a decorative line (to give it a certificate feel)
    $pdf->SetDrawColor(52, 152, 219); // Set color for the line
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Draw line across the page
    $pdf->Ln(5); // Space after line

    // Footer with page number and contact info
    $pdf->SetY(-15); // Position at the bottom of the page (15mm)
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Ln(5); // New line for contact info

    // Output the PDF
    if ($action == 'pdf') {
        $pdf->Output('D', 'Building_and_Occupancy_Report.pdf');
    } else {
        $pdf->Output('I', 'Building_and_Occupancy_Report.pdf');
    }
}


// function exportToPDF($conn, $action) {
//     $buildingData = fetchBuildingData($conn);
//     $occupancyData = fetchOccupancyData($conn);

//     $pdf = new FPDF('P', 'mm', 'A4');
//     $pdf->AddPage();

//     // Header Section
//     $pdf->Image('./img/bfp3.png', 10, 6, 20); // Add logo
//     $pdf->SetFont('Arial', 'B', 16);
//     $pdf->Cell(0, 10, 'BFP Minglanilla Fire Station', 0, 1, 'C');
//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->Cell(0, 10, 'Building and Occupancy Report', 0, 1, 'C');
//     $pdf->Ln(10);

//     // Building Section
//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->SetFillColor(200, 200, 200); // Light gray background for section header
//     $pdf->Cell(190, 10, 'Building', 1, 1, 'C', true); // Merged header

//     // Table Data
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(95, 10, 'Total Applications Received This Month', 1, 0, 'L');
//     $pdf->Cell(95, 10, $buildingData['applications_received'], 1, 1, 'C');
//     $pdf->Cell(95, 10, 'Total Inspections Conducted', 1, 0, 'L');
//     $pdf->Cell(95, 10, $buildingData['inspected'], 1, 1, 'C');
//     $pdf->Cell(95, 10, 'Number of Issued FSEC - For Current Month', 1, 0, 'L');
//     $pdf->Cell(95, 10, $buildingData['fsec_current'], 1, 1, 'C');
//     $pdf->Cell(95, 10, 'Number of Issued FSEC - For Previous Months', 1, 0, 'L');
//     $pdf->Cell(95, 10, $buildingData['fsec_previous'], 1, 1, 'C');
//     $pdf->Cell(95, 10, 'Total Number of Issued FSEC', 1, 0, 'L');
//     $pdf->Cell(95, 10, $buildingData['fsec_total'], 1, 1, 'C');

//     $pdf->Ln(10); // Add some space

//     // Occupancy Section
//     $pdf->SetFont('Arial', 'B', 12);
//     $pdf->SetFillColor(200, 200, 200); // Light gray background for section header
//     $pdf->Cell(190, 10, 'Occupancy', 1, 1, 'C', true); // Merged header

//     // Table Data
//     $pdf->SetFont('Arial', '', 10);
//     $pdf->Cell(95, 10, 'Total Applications Received This Month', 1, 0, 'L');
//     $pdf->Cell(95, 10, $occupancyData['applications_received'], 1, 1, 'C');
//     $pdf->Cell(95, 10, 'Number of Issued FSIC - For Current Month', 1, 0, 'L');
//     $pdf->Cell(95, 10, $occupancyData['fsic_current'], 1, 1, 'C');
//     $pdf->Cell(95, 10, 'Number of Issued FSIC - From Previous Months', 1, 0, 'L');
//     $pdf->Cell(95, 10, $occupancyData['fsic_previous'], 1, 1, 'C');
//     $pdf->Cell(95, 10, 'Total Number of Issued FSIC', 1, 0, 'L');
//     $pdf->Cell(95, 10, $occupancyData['fsic_total'], 1, 1, 'C');

//     // Output the PDF
//     if($action == 'pdf'){
//         $pdf->Output('D', 'Building_and_Occupancy_Report.pdf');
//     }else{
//         $pdf->Output('I', 'Building_and_Occupancy_Report.pdf');
//     }
// }

// function exportToExcel($conn) {
//     $buildingData = fetchBuildingData($conn);
//     $occupancyData = fetchOccupancyData($conn);

//     $spreadsheet = new Spreadsheet();
//     $sheet = $spreadsheet->getActiveSheet();

//     // Building Section
//     $sheet->setCellValue('A1', 'Building');
//     $sheet->setCellValue('A2', 'Total Applications Received This Month');
//     $sheet->setCellValue('B2', $buildingData['applications_received']);
//     $sheet->setCellValue('A3', 'Total Inspections Conducted');
//     $sheet->setCellValue('B3', $buildingData['inspected']);
//     $sheet->setCellValue('A4', 'Number of Issued FSEC');
//     $sheet->setCellValue('A5', '- For Current Month');
//     $sheet->setCellValue('B5', $buildingData['fsec_current']);
//     $sheet->setCellValue('A6', '- For Previous Months');
//     $sheet->setCellValue('B6', $buildingData['fsec_previous']);
//     $sheet->setCellValue('A7', '- Total Issued FSEC');
//     $sheet->setCellValue('B7', $buildingData['fsec_total']);

//     // Occupancy Section
//     $sheet->setCellValue('A9', 'Occupancy');
//     $sheet->setCellValue('A10', 'Total Applications Received This Month');
//     $sheet->setCellValue('B10', $occupancyData['applications_received']);
//     $sheet->setCellValue('A11', 'Number of Issued FSIC for Occupancy');
//     $sheet->setCellValue('A12', '- For Current Month');
//     $sheet->setCellValue('B12', $occupancyData['fsic_current']);
//     $sheet->setCellValue('A13', '- From Previous Months');
//     $sheet->setCellValue('B13', $occupancyData['fsic_previous']);
//     $sheet->setCellValue('A14', '- Total Issued FSIC');
//     $sheet->setCellValue('B14', $occupancyData['fsic_total']);

//     $writer = new Xlsx($spreadsheet);
//     $fileName = 'Building_and_Occupancy_Report.xlsx';
//     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//     header('Content-Disposition: attachment; filename="' . $fileName . '"');
//     $writer->save('php://output');
// }
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

function exportToExcel($conn) {
    $buildingData = fetchBuildingData($conn);
    $occupancyData = fetchOccupancyData($conn);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Set title and font style for the sheet with increased font size
    $sheet->setCellValue('A1', 'Republic of the Philippines');
    $sheet->mergeCells('A1:B1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A2', 'BUREAU OF FIRE PROTECTION');
    $sheet->mergeCells('A2:B2');
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(18);  // Larger font size
    $sheet->getStyle('A2')->getFont()->getColor()->setRGB('16355C');  // Color #16355C
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A3', 'Province of Cebu');
    $sheet->mergeCells('A3:B3');
    $sheet->getStyle('A3')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A4', 'Minglanilla Fire Station');
    $sheet->mergeCells('A4:B4');
    $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14);  // Larger font size
    $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A5', 'Ward 2 Poblacion, Minglanilla, Cebu');
    $sheet->mergeCells('A5:B5');
    $sheet->getStyle('A5')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A6', 'Tel no.: (032) 401-2943 / 273-2830');
    $sheet->mergeCells('A6:B6');
    $sheet->getStyle('A6')->getFont()->setSize(14);  // Increased font size
    $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Add space after header (in Excel, just increase the row number)
    $sheet->getRowDimension(7)->setRowHeight(20);  // Increased row height

    // Set up section headers (Building Information) with larger font size
    $sheet->setCellValue('A8', 'Building Information');
    $sheet->mergeCells('A8:B8');
    $sheet->getStyle('A8')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A8')->getFont()->getColor()->setRGB('FFFFFF'); // White text
    $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Add Building data rows with larger font size and row height
    $sheet->setCellValue('A9', 'Total Applications Received This Month');
    $sheet->setCellValue('B9', $buildingData['applications_received']);
    $sheet->getStyle('A9:B9')->getFont()->setSize(14);  // Larger font size
    $sheet->getStyle('A9:B9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension(9)->setRowHeight(25);  // Increased row height

    $sheet->setCellValue('A10', 'Total Inspections Conducted');
    $sheet->setCellValue('B10', $buildingData['inspected']);
    $sheet->getStyle('A10:B10')->getFont()->setSize(14);  // Larger font size
    $sheet->getStyle('A10:B10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension(10)->setRowHeight(25);  // Increased row height

    $sheet->setCellValue('A11', 'Number of Issued FSEC');
    $sheet->setCellValue('A12', '- For Current Month');
    $sheet->setCellValue('B12', $buildingData['fsec_current']);
    $sheet->setCellValue('A13', '- For Previous Months');
    $sheet->setCellValue('B13', $buildingData['fsec_previous']);
    $sheet->setCellValue('A14', '- Total Issued FSEC');
    $sheet->setCellValue('B14', $buildingData['fsec_total']);
    
    // Add space after the Building section
    $sheet->getRowDimension(15)->setRowHeight(25);  // Increased row height

    // Set up Occupancy section header with larger font size
    $sheet->setCellValue('A16', 'Occupancy Information');
    $sheet->mergeCells('A16:B16');
    $sheet->getStyle('A16')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A16')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A16')->getFont()->getColor()->setRGB('FFFFFF'); // White text
    $sheet->getStyle('A16')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Add Occupancy data rows with larger font size and row height
    $sheet->setCellValue('A17', 'Total Applications Received This Month');
    $sheet->setCellValue('B17', $occupancyData['applications_received']);
    $sheet->getStyle('A17:B17')->getFont()->setSize(14);  // Larger font size
    $sheet->getStyle('A17:B17')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getRowDimension(17)->setRowHeight(25);  // Increased row height

    $sheet->setCellValue('A18', 'Number of Issued FSIC for Occupancy');
    $sheet->setCellValue('A19', '- For Current Month');
    $sheet->setCellValue('B19', $occupancyData['fsic_current']);
    $sheet->setCellValue('A20', '- From Previous Months');
    $sheet->setCellValue('B20', $occupancyData['fsic_previous']);
    $sheet->setCellValue('A21', '- Total Issued FSIC');
    $sheet->setCellValue('B21', $occupancyData['fsic_total']);
    
    // Add space after the Occupancy section
    $sheet->getRowDimension(22)->setRowHeight(25);  // Increased row height


    // Set column widths for better display
    $sheet->getColumnDimension('A')->setWidth(100);  // Increased width
    $sheet->getColumnDimension('B')->setWidth(100);  // Increased width

    // Output the Excel file
    $writer = new Xlsx($spreadsheet);
    $fileName = 'Building_and_Occupancy_Report_Larger.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    $writer->save('php://output');
}

?>
