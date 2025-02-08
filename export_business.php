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



function exportToPDF($conn, $action) {
    // Initialize PDF
    $pdf = new FPDF('P', 'mm', 'A4');
    $pdf->AddPage();

      // Set font and logo for the header
      $pdf->Image('./img/bfp3.png', 30, 8, 25); // Logo resized to 8mm
      $pdf->Image('./img/Bureau_of_Fire_Protection.png', 155, 8, 25); // Second logo resized to 8mm
      $pdf->Ln(1); // Adjust space after logos
  
      // Set title and address information
      $pdf->SetFont('Arial', '', 11);
      $pdf->Cell(0, 5, 'Republic of the Philippines', 0, 1, 'C');
      $pdf->SetFont('Arial', 'B', 14);
      $pdf->SetTextColor(22, 53, 92); // #16355C
      $pdf->Cell(0, 5, 'BUREAU OF FIRE PROTECTION', 0, 1, 'C');
      $pdf->SetFont('Arial', '', 12);
      $pdf->SetTextColor(0, 0, 0); // Reset text color to black
      $pdf->Cell(0, 5, 'Province of Cebu', 0, 1, 'C');
      $pdf->SetFont('Arial', 'B', 12);
      $pdf->Cell(0, 5, 'Minglanilla Fire Station', 0, 1, 'C');
      $pdf->SetFont('Arial', '', 12);
      $pdf->Cell(0, 5, 'Ward 2 Poblacion, Minglanilla, Cebu', 0, 1, 'C');
      $pdf->Cell(0, 5, 'Tel no.: (032) 401-2943 / 273-2830', 0, 1, 'C');
      $pdf->Ln(10); // Add some space after the header
  
      // Add a decorative line (to give it a certificate feel)
    $pdf->SetDrawColor(52, 152, 219); // Set color for the line
    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY()); // Draw line across the page
    $pdf->Ln(5); // Space after line

    // Section 1: Number of Applications Received
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(52, 152, 219); // Gray background for section header
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(190, 10, 'Number of Applications Received (within a month)', 1, 1, 'C', true);
    $pdf->SetFont('Arial', '', 10);
    
    // Table Header for Applications
    $pdf->SetTextColor(0, 0, 0, 1);
    $pdf->Cell(95, 10, 'Application Type', 1, 0, 'C');
    $pdf->Cell(95, 10, 'Count', 1, 1, 'C');

    // Query to count New and Renewal Applications
    $query = "
    SELECT t.application_type, COALESCE(a.count, 0) AS count
    FROM (
        SELECT 'new_business_permit' AS application_type
        UNION ALL
        SELECT 'renewal_business_permit' AS application_type
    ) t
    LEFT JOIN (
        SELECT application_type, COUNT(*) AS count
        FROM applications
        WHERE created_at >= CURDATE() - INTERVAL 1 MONTH
        GROUP BY application_type
    ) a ON t.application_type = a.application_type";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $applicationType = ($row['application_type'] == 'new_business_permit') ? 'New Business Permit' : 'Renewal Business Permit';
        $pdf->Cell(95, 10, $applicationType, 1, 0, 'L');
        $pdf->Cell(95, 10, $row['count'], 1, 1, 'C');
    }

    // Total Applications
    $query = "SELECT COUNT(*) AS total FROM applications WHERE created_at >= CURDATE() - INTERVAL 1 MONTH AND (application_type = 'new_business_permit' OR application_type = 'renewal_business_permit')";
    $result = $conn->query($query);
    $totalApplications = $result->fetch_assoc()['total'];
    $pdf->Cell(95, 10, 'Total Applications', 1, 0, 'L');
    $pdf->Cell(95, 10, $totalApplications, 1, 1, 'C');
    $pdf->Ln(5); // Space between sections

    // Section 2: Structural Inspections
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(190, 10, 'Structural Inspections', 1, 1, 'C', true);
    $pdf->SetFont('Arial', '', 10);

    // Table Header for Inspections
    $pdf->SetTextColor(0, 0, 0, 1);
    $pdf->Cell(95, 10, 'Building Type', 1, 0, 'C');
    $pdf->Cell(95, 10, 'Count', 1, 1, 'C');

    // Define inspection types
    $types_count = [
        'assembly' => 0, 'educational' => 0, 'daycare' => 0, 'healthcare' => 0, 'residential' => 0,
        'detention' => 0, 'mercantile' => 0, 'business' => 0, 'industrial' => 0, 'storage' => 0,
        'special' => 0, 'hotel' => 0, 'dormitories' => 0, 'apartment' => 0, 'lodging' => 0, 'single' => 0
    ];
    $totalInspections = 0;

    // Query to fetch inspection data
    $query = "SELECT additional FROM issuance WHERE created_at >= CURDATE() - INTERVAL 1 MONTH";
    $result = $conn->query($query);

    // Process each row to count inspection types
    while ($row = $result->fetch_assoc()) {
        $additional = json_decode($row['additional'], true); // Decode JSON to get occupancy type
        if ($additional && isset($additional['typeOccupancy'])) {
            $typeOccupancy = strtolower($additional['typeOccupancy']);
            if (isset($types_count[$typeOccupancy])) {
                $types_count[$typeOccupancy]++;
                $totalInspections++;
            }
        }
    }

    // Output inspection counts for each type
    foreach ($types_count as $type => $count) {
        $pdf->Cell(95, 10, ucfirst($type), 1, 0, 'L');
        $pdf->Cell(95, 10, $count, 1, 1, 'C');
    }

    // Total Inspections
    $pdf->Cell(95, 10, 'Total Inspections', 1, 0, 'L');
    $pdf->Cell(95, 10, $totalInspections, 1, 1, 'C');
    $pdf->Ln(5); // Space between sections

    // Section 3: Issued Permits
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(190, 10, 'Number of Issued Permits (within a month)', 1, 1, 'C', true);
    $pdf->SetFont('Arial', '', 10);

    // Add counts for new business permits
    $query = "SELECT COUNT(*) AS total 
    FROM applications 
    INNER JOIN issuance ON applications.id = issuance.application_id 
    WHERE applications.created_at >= CURDATE() - INTERVAL 1 MONTH 
    AND applications.application_type = 'new_business_permit' 
    AND issuance.status = 1";
    $result = $conn->query($query);
    $issuedNewPermits = $result->fetch_assoc()['total'];

    $pdf->SetTextColor(0, 0, 0, 1);
    $pdf->Cell(95, 10, 'Issued New Business Permits', 1, 0, 'L');
    $pdf->Cell(95, 10, $issuedNewPermits, 1, 1, 'C');

    // Add counts for renewal business permits
    $query = "SELECT COUNT(*) AS total 
    FROM applications 
    INNER JOIN issuance ON applications.id = issuance.application_id 
    WHERE applications.created_at >= CURDATE() - INTERVAL 1 MONTH 
    AND applications.application_type = 'renewal_business_permit' 
    AND issuance.status = 1";
    $result = $conn->query($query);
    $issuedRenewalPermits = $result->fetch_assoc()['total'];

    $pdf->Cell(95, 10, 'Issued Renewal Business Permits', 1, 0, 'L');
    $pdf->Cell(95, 10, $issuedRenewalPermits, 1, 1, 'C');

    // Total Issued Permits
    $totalIssuedPermits = $issuedNewPermits + $issuedRenewalPermits;
    $pdf->Cell(95, 10, 'Total Issued Permits', 1, 0, 'L');
    $pdf->Cell(95, 10, $totalIssuedPermits, 1, 1, 'C');
    $pdf->Ln(5); // Space between sections

    // Section 4: Notices
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(190, 10, 'Number of Notices (within a month)', 1, 1, 'C', true);
    $pdf->SetFont('Arial', '', 10);

    // Define the types of notices and status codes
    $notice_types = ['Notice to Comply' => 4, 'Notice to Correct' => 5, 'Abatement' => 6];
    $notice_count = ['Notice to Comply' => 0, 'Notice to Correct' => 0, 'Abatement' => 0];
    $totalNotices = 0;

    // Get counts for each notice type
    foreach ($notice_types as $notice_name => $status_code) {
        $query = "SELECT COUNT(*) AS total 
        FROM inspections 
        INNER JOIN applications ON applications.id = inspections.application_id
        WHERE inspections.created_at >= CURDATE() - INTERVAL 1 MONTH 
        AND inspections.status = $status_code";
        $result = $conn->query($query);
        $count = $result->fetch_assoc()['total'];

        $notice_count[$notice_name] = $count;
        $totalNotices += $count;
    }

    // Output counts for each notice type
    $pdf->SetTextColor(0, 0, 0, 1);
    foreach ($notice_count as $notice_name => $count) {
        $pdf->Cell(95, 10, $notice_name, 1, 0, 'L');
        $pdf->Cell(95, 10, $count, 1, 1, 'C');
    }

    // Total Notices
    $pdf->Cell(95, 10, 'Total Notices', 1, 0, 'L');
    $pdf->Cell(95, 10, $totalNotices, 1, 1, 'C');
    $pdf->Ln(5); // Space between sections

    // Output the PDF
    if ($action == 'pdf') {
        $pdf->Output('D', 'Business_Report.pdf'); // Download PDF
    } else {
        $pdf->Output('I', 'Business_Report.pdf'); // Display PDF in browser
    }
}

// function exportToExcel($conn) {
//     $spreadsheet = new Spreadsheet();
//     $sheet = $spreadsheet->getActiveSheet();

//     // Set column headers for the application section
//     $sheet->setCellValue('A1', 'Business Establishments Report');
//     $sheet->mergeCells('A1:B1'); // Merge cells for the title
//     $sheet->setCellValue('A3', 'Application Type');
//     $sheet->setCellValue('B3', 'Count');

//     // Query to get application types count
//     $query = "SELECT application_type, COUNT(*) AS count 
//               FROM applications 
//               WHERE created_at >= CURDATE() - INTERVAL 1 MONTH 
//               AND (application_type = 'new_business_permit' OR application_type = 'renewal_business_permit') 
//               GROUP BY application_type";
//     $result = $conn->query($query);

//     // Add data to sheet for application types
//     $rowIndex = 4; // Start from row 4
//     while ($row = $result->fetch_assoc()) {
//         $sheet->setCellValue('A' . $rowIndex, ucfirst(str_replace('_', ' ', $row['application_type'])));
//         $sheet->setCellValue('B' . $rowIndex, $row['count']);
//         $rowIndex++;
//     }

//     // Total applications row
//     $query = "SELECT COUNT(*) AS total 
//               FROM applications 
//               WHERE created_at >= CURDATE() - INTERVAL 1 MONTH 
//               AND (application_type = 'new_business_permit' OR application_type = 'renewal_business_permit')";
//     $result = $conn->query($query);
//     $totalApplications = $result->fetch_assoc()['total'];
//     $sheet->setCellValue('A' . $rowIndex, 'Total Applications');
//     $sheet->setCellValue('B' . $rowIndex, $totalApplications);
//     $rowIndex += 2; // Leave a space between sections

//     // Section: Structural Inspections
//     $sheet->setCellValue('A' . $rowIndex, 'Structural Inspections:');
//     $rowIndex++;
//     $sheet->setCellValue('A' . $rowIndex, 'Building Type');
//     $sheet->setCellValue('B' . $rowIndex, 'Count');
//     $rowIndex++;

//     // Define the types to count for inspections
//     $types_count = [
//         'assembly' => 0, 'educational' => 0, 'daycare' => 0, 'healthcare' => 0, 'residential' => 0,
//         'detention' => 0, 'mercantile' => 0, 'business' => 0, 'industrial' => 0, 'storage' => 0,
//         'special' => 0, 'hotel' => 0, 'dormitories' => 0, 'apartment' => 0, 'lodging' => 0, 'single' => 0
//     ];

//     // Get all issuance data for inspections
//     $query = "SELECT additional FROM issuance WHERE created_at >= CURDATE() - INTERVAL 1 MONTH";
//     $result = $conn->query($query);

//     // Process each row to count the types
//     while ($row = $result->fetch_assoc()) {
//         $additional = json_decode($row['additional'], true); // Decode the JSON
//         if ($additional && isset($additional['typeOccupancy'])) {
//             $typeOccupancy = strtolower($additional['typeOccupancy']);
//             if (isset($types_count[$typeOccupancy])) {
//                 $types_count[$typeOccupancy]++;
//             }
//         }
//     }

//     // Output the counts for each type
//     foreach ($types_count as $type => $count) {
//         $sheet->setCellValue('A' . $rowIndex, ucfirst($type));
//         $sheet->setCellValue('B' . $rowIndex, $count);
//         $rowIndex++;
//     }

//     // Section: Notices
//     $sheet->setCellValue('A' . $rowIndex, 'Number of Notices (within a month):');
//     $rowIndex++;

//     // Define the types of notices and their corresponding status codes
//     $notice_types = [
//         'Notice to Comply' => 4,
//         'Notice to Correct' => 5,
//         'Abatement' => 6
//     ];

//     // Initialize counters for notices
//     $notice_count = [
//         'Notice to Comply' => 0,
//         'Notice to Correct' => 0,
//         'Abatement' => 0
//     ];

//     // Initialize total notices counter
//     $totalNotices = 0;

//     // Get the counts for each type of notice
//     foreach ($notice_types as $notice_name => $status_code) {
//         $query = "SELECT COUNT(*) AS total 
//                   FROM inspections 
//                   INNER JOIN applications ON applications.id = inspections.application_id
//                   WHERE inspections.created_at >= CURDATE() - INTERVAL 1 MONTH 
//                   AND inspections.status = $status_code";
//         $result = $conn->query($query);
//         $count = $result->fetch_assoc()['total'];

//         // Update the count for the specific notice type
//         $notice_count[$notice_name] = $count;
//         $totalNotices += $count; // Increment the total notices count
//     }

//     // Output the counts for each type of notice
//     foreach ($notice_count as $notice_name => $count) {
//         $sheet->setCellValue('A' . $rowIndex, $notice_name);
//         $sheet->setCellValue('B' . $rowIndex, $count);
//         $rowIndex++;
//     }

//     // Output the total notices
//     $sheet->setCellValue('A' . $rowIndex, 'Total Notices');
//     $sheet->setCellValue('B' . $rowIndex, $totalNotices);
//     $rowIndex++; // Add space between sections

//     // Set headers for download
//     header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
//     header('Content-Disposition: attachment;filename="Business_Report.xlsx"');
//     header('Cache-Control: max-age=0');

//     // Write to output
//     $writer = new Xlsx($spreadsheet);
//     $writer->save('php://output');
//     exit; // Ensure no further output
// }

use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function exportToExcel($conn) {
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
    
    // Add space after header (increase the row height)
    $sheet->getRowDimension(7)->setRowHeight(20);  
    // Set column widths
    $sheet->getColumnDimension('A')->setWidth(100);
    $sheet->getColumnDimension('B')->setWidth(100);

    // Add a section header (Business Establishments Report) with larger font size
    $sheet->setCellValue('A8', 'Business Establishments Report');
    $sheet->mergeCells('A8:B8');
    $sheet->getStyle('A8')->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A8')->getFont()->getColor()->setRGB('FFFFFF'); // White text
    $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Set column headers for application section
    $sheet->setCellValue('A9', 'Application Type');
    $sheet->setCellValue('B9', 'Count');
    $sheet->getStyle('A9:B9')->getFont()->setBold(true)->setSize(14);  // Larger font size
    $sheet->getStyle('A9:B9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension(9)->setRowHeight(25);  // Increased row height

    // Query to get application types count
    $query = "SELECT application_type, COUNT(*) AS count 
              FROM applications 
              WHERE created_at >= CURDATE() - INTERVAL 1 MONTH 
              AND (application_type = 'new_business_permit' OR application_type = 'renewal_business_permit') 
              GROUP BY application_type";
    $result = $conn->query($query);

    // Add data to sheet for application types
    $rowIndex = 10; // Start from row 10
    while ($row = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $rowIndex, ucfirst(str_replace('_', ' ', $row['application_type'])));
        $sheet->setCellValue('B' . $rowIndex, $row['count']);
        $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getFont()->setSize(14);  // Larger font size
        $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height
        $rowIndex++;
    }

    // Add a total row for applications
    $query = "SELECT COUNT(*) AS total 
              FROM applications 
              WHERE created_at >= CURDATE() - INTERVAL 1 MONTH 
              AND (application_type = 'new_business_permit' OR application_type = 'renewal_business_permit')";
    $result = $conn->query($query);
    $totalApplications = $result->fetch_assoc()['total'];
    $sheet->setCellValue('A' . $rowIndex, 'Total Applications');
    $sheet->setCellValue('B' . $rowIndex, $totalApplications);
    $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getFont()->setBold(true);
    $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height
    $rowIndex += 2; // Add space between sections

    // Add section for structural inspections
    // Add section for Structural Inspections with styling
    $sheet->setCellValue('A' . $rowIndex, 'Structural Inspections:');
    $sheet->mergeCells('A' . $rowIndex . ':B' . $rowIndex);
    $sheet->getStyle('A' . $rowIndex)->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A' . $rowIndex)->getFont()->getColor()->setRGB('FFFFFF'); // White text
    $sheet->getStyle('A' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height
    $rowIndex++;
    $sheet->setCellValue('A' . $rowIndex, 'Building Type');
    $sheet->setCellValue('B' . $rowIndex, 'Count');
    $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getFont()->setBold(true)->setSize(14);  // Larger font size
    $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height
    $rowIndex++;

    // Define the building types to count for inspections
    $types_count = [
        'assembly' => 0, 'educational' => 0, 'daycare' => 0, 'healthcare' => 0, 'residential' => 0,
        'detention' => 0, 'mercantile' => 0, 'business' => 0, 'industrial' => 0, 'storage' => 0,
        'special' => 0, 'hotel' => 0, 'dormitories' => 0, 'apartment' => 0, 'lodging' => 0, 'single' => 0
    ];

    // Query for inspections
    $query = "SELECT additional FROM issuance WHERE created_at >= CURDATE() - INTERVAL 1 MONTH";
    $result = $conn->query($query);

    // Count each building type based on 'additional' column
    while ($row = $result->fetch_assoc()) {
        $additional = json_decode($row['additional'], true); // Decode JSON
        if ($additional && isset($additional['typeOccupancy'])) {
            $typeOccupancy = strtolower($additional['typeOccupancy']);
            if (isset($types_count[$typeOccupancy])) {
                $types_count[$typeOccupancy]++;
            }
        }
    }

    // Add inspection counts to the sheet
    foreach ($types_count as $type => $count) {
        $sheet->setCellValue('A' . $rowIndex, ucfirst($type));
        $sheet->setCellValue('B' . $rowIndex, $count);
        $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getFont()->setSize(14);  // Larger font size
        $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height
        $rowIndex++;
    }

    // Add a section for Notices
    $sheet->setCellValue('A' . $rowIndex, 'Number of Notices (within a month):');
    $sheet->mergeCells('A' . $rowIndex . ':B' . $rowIndex);
    $sheet->getStyle('A' . $rowIndex)->getFont()->setBold(true)->setSize(16);  // Larger font size
    $sheet->getStyle('A' . $rowIndex)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3498DB'); // Blue background
    $sheet->getStyle('A' . $rowIndex)->getFont()->getColor()->setRGB('FFFFFF'); // White text
    $sheet->getStyle('A' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height
    $rowIndex++;

    // Define the types of notices and their corresponding status codes
    $notice_types = [
        'Notice to Comply' => 4,
        'Notice to Correct' => 5,
        'Abatement' => 6
    ];

    // Initialize counters for notices
    $notice_count = [
        'Notice to Comply' => 0,
        'Notice to Correct' => 0,
        'Abatement' => 0
    ];

    // Initialize total notices counter
    $totalNotices = 0;

    // Get the counts for each type of notice
    foreach ($notice_types as $notice_name => $status_code) {
        $query = "SELECT COUNT(*) AS total 
                  FROM inspections 
                  INNER JOIN applications ON applications.id = inspections.application_id
                  WHERE inspections.created_at >= CURDATE() - INTERVAL 1 MONTH 
                  AND inspections.status = $status_code";
        $result = $conn->query($query);
        $count = $result->fetch_assoc()['total'];

        // Update the count for the specific notice type
        $notice_count[$notice_name] = $count;
        $totalNotices += $count; // Increment the total notices count
    }

    // Output the counts for each type of notice
    foreach ($notice_count as $notice_name => $count) {
        $sheet->setCellValue('A' . $rowIndex, $notice_name);
        $sheet->setCellValue('B' . $rowIndex, $count);
        $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getFont()->setSize(14);  // Larger font size
        $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height
        $rowIndex++;
    }

    // Output the total notices
    $sheet->setCellValue('A' . $rowIndex, 'Total Notices');
    $sheet->setCellValue('B' . $rowIndex, $totalNotices);
    $sheet->getStyle('A' . $rowIndex . ':B' . $rowIndex)->getFont()->setBold(true);
    $sheet->getRowDimension($rowIndex)->setRowHeight(25);  // Increased row height
    $rowIndex++; // Add space between sections

    // Set headers for download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Business_Report_Larger.xlsx"');
    header('Cache-Control: max-age=0');

    // Write to output
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit; // Ensure no further output
}

?>
